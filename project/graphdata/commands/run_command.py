# -*- coding: utf-8 -*-
import json
import os
import time
import csv
import sys
from datetime import datetime, timedelta
from model.relation_match import RelationMatch
from lib.logger import logger
from .base_command import BaseCommand
from service import YyyAnalysis
from service import MultimatchData
from service import JcardmatchData
from service import RelationData
from service import Spider
from module.yiyiyuan import YiUser
from module.yiyiyuan import YiLoan
from model.open import OpenJxlStat
from model.strategy import StrategyRequest
from model.antifraud import AfBase
from model.antifraud import AfDbAgent, AfCompluteRule, AfResult


class RunCommand(BaseCommand):
    def __init__(self):
        super(RunCommand, self).__init__()

    #数据中间表
    def runbase(self):
        strategy_data = self._getStrategy()
        if strategy_data is None:
            print("there is nothing data to deal with")
            return None

        for db_strategy in strategy_data:
            try:
                oYiUser = YiUser().getByUserId(db_strategy.user_id)
                record = None
                if oYiUser is not None:
                    record = OpenJxlStat().getByPhone(oYiUser.mobile)

                jxlstat_id = 0 if record is None else record.get('jxlstat_id')
                baseInfo  = {
                    'request_id':db_strategy.req_id,
                    'aid':db_strategy.aid,
                    'user_id':db_strategy.user_id,
                    'loan_id':db_strategy.loan_id,
                    'jxlstat_id':jxlstat_id,
                    'match_status':0,
                    'create_time':datetime.now(),
                    'modify_time':datetime.now()
                }
                afbase = AfBase().addData(baseInfo)
                print(afbase)
            except Exception as e:
                print(e)
                logger.error("af_base save fail %s" %e)

        return True
    
    def _getStrategy(self):
        now = datetime.now()
        t = now.strftime('%Y-%m-%d %H:%M:%S')
        data = StrategyRequest().getData(t)
        data_len = len(data)
        if data_len == 0:
            logger.info("no strategy data")
            return None

        # lock status = 1
        res = StrategyRequest().lock(data, 1)
        if not res:
            logger.info("no strategy save fail")
            return None

        #  number of data to deal with
        logger.info("there's %s data to deal with" % data_len)
        return data

    # 匹配二级关系
    def multimatch(self):
        '''
        获取所有需要执行二级关系数据
        '''
        match_data = self._getMultiMatch()
        if match_data is None:
            print("there is nothing data to deal with")
            return None

        for db_match in match_data:
            try:
                # 1. 分析后的数据
                obj = MultimatchData(db_match)
                dict_res = obj.runMatch()
                print(dict_res)

                # 2. 保存到文件中
                filepath = self.__saveMultiMatch(dict_res)

                # 3. 文件路径保存到库中
                dict_res['multi_match']['relation_ship'] = filepath
                oAfDbAgent = AfDbAgent()
                db_res = oAfDbAgent.import_match_db(dict_res)
                print(db_res)

            except Exception as e:
                logger.error("multimatch.id:%s is fail: %s" % (db_match.id, e))
            finally:
                # 4. 更新为结束
                res = db_match.finishMatched(4)
                print(res)
        return None

    def _getMultiMatch(self):
        # get data from db
        # 读取7天内的数据
        today = datetime.now()
        time = today + timedelta(days=-7)
        time = time.strftime('%Y-%m-%d %H:%M:%S')

        oAfBase = AfBase()
        data = oAfBase.getMulMatchData(time)
        data_len = len(data)
        if data_len == 0:
            logger.info("no multi-match data")
            return None

        # lock status = 3
        res = oAfBase.lockMatchStatus(data, 3)  # 锁定
        if not res:
            logger.info("no multi-match save fail")
            return None

        #  number of data to deal with
        logger.info("there's %s data to deal with" % data_len)

        return data

    def __saveMultiMatch(self, dict_res):
        path = os.getcwd()
        now = datetime.now()
        t = now.strftime('%Y%m/%d')

        multi_match = dict_res.get('multi_match') if dict_res else {}
        relation_ship = multi_match.get('relation_ship') if multi_match else "[]"
        user_id = multi_match.get('user_id') if multi_match else 0
        loan_id = multi_match.get('loan_id') if multi_match else 0
        aid = multi_match.get('aid') if multi_match else 0

        if len(json.loads(relation_ship)) == 0:
            logger.info("user_id:%s has not multi-match relationship" %
                  (user_id))
            return None

        # 创建级联目录
        dir_path = '/relation/multimatch/' + t

        dir_exists = os.path.exists(path + '/../' + dir_path)
        if not dir_exists:
            os.makedirs(path + '/../' + dir_path)

        # 将内容写入文件
        filepath = dir_path + '/' + \
            str(aid) + '_' + str(user_id) + '_' + str(loan_id) + '_match.json'

        dict_json = json.dumps(relation_ship)

        fp = open(path + '/../' + filepath, "a+")
        data = fp.write(dict_json + '\n')
        fp.close()
        return filepath

    # 匹配间接联系人(杰卡德)
    def jcardmatch(self):
        '''
        获取所有需要执行jaccard数据
        '''
        jcard_data = self._getJcardMatch()
        if jcard_data is None:
            print("there is nothing data to deal with")
            return None

        for db_jcard in jcard_data:
            try:
                # 1. 分析后的数据
                obj = JcardmatchData(db_jcard)
                dict_res = obj.runJcard()
                print(dict_res)

                # 2. 保存到文件中
                filepath = self.__saveJcardMatch(dict_res)

                # 3. 文件路径保存到库中
                dict_res['jcard_match']['jcard_result'] = filepath
                oAfDbAgent = AfDbAgent()
                db_res = oAfDbAgent.import_jcard_db(dict_res)
                print(db_res)

                # 4. 更新为结束
                res = db_jcard.finishMatched(6)
                print(res)

            except Exception as e:
                logger.error("jcardmatch.id:%s is fail: %s" % (db_jcard.id, e))

        return None
    def testjcard(self,step):
        start_time = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        print('start_time: %s ' % (start_time))
        jcard_data = self._gettestjcard(step)
        if jcard_data is None:
            print("there is nothing data to deal with")
            return None
        gettestjcard = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        print('gettestjcard: %s ' % (gettestjcard))

        for user in jcard_data:
            # time.sleep(0.5)
            # print('step: %s ||| user_Id:%s '%(step,user_id))
            try:
                relationstart = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
                print('relationstart: %s ' % (relationstart))
                start1 = time.clock()
                # 1. 分析后的数据
                obj = JcardmatchData()
                dict_res = obj.runtestJcard(user)
                end1 = time.clock()
                print('jacard_all_time: %s Seconds' % (end1 - start1))
                # print(dict_res)
                relationend = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
                print('relationend: %s ' % (relationend))

                # 2. 保存到文件中
                filepath = self.__saveJcardMatch(dict_res)
                savefile = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
                print('savefile: %s ' % (savefile))

                if filepath:
                    # 3. 分析关系系数
                    obj = RelationData()
                    start2 = time.clock()
                    dict_res = obj.runtestRelation(user,filepath)
                    end2 = time.clock()
                    print('relation_all_time: %s Seconds' % (end2 - start2))
                    # print(dict_res)
                    caljcard = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
                    print('caljcard: %s ' % (caljcard))

                    # 4.入库
                    oAfDbAgent = AfDbAgent()
                    db_res = oAfDbAgent.import_relation_db(dict_res)
                    save_res = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
                    print('save_res: %s ' % (save_res))

                    # print(db_res)
                end_time = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
                print('end_time: %s ' % (end_time))
            except Exception as e:
                logger.error("user_id.id:%s is fail: %s" % (user, e))
        return None

    def __saveJcardMatch(self, dict_res):
        path = os.getcwd()
        now = datetime.now()
        t = now.strftime('%Y%m/%d')

        jcard_match = dict_res.get('jcard_match') if dict_res else {}
        jcard_result = jcard_match.get('jcard_relation') if jcard_match else "[]"
        relation_result = jcard_match.get('relation_list') if jcard_match else "[]"
        # print(jcard_result)
        user_id = jcard_match.get('user_id') if jcard_match else 0
        loan_id = jcard_match.get('loan_id') if jcard_match else 0
        aid = jcard_match.get('aid') if jcard_match else 0

        if len(json.loads(jcard_result)) == 0 and len(json.loads(relation_result)) == 0:
            logger.info("user_id:%s has not jcard-match relationship" %
                  (user_id))
            return None
        end_time = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        jac_relation = {'jaccard':jcard_result,'relation':relation_result,'time':end_time}
        # 创建级联目录
        dir_path = '/relation/jaccard/' + t

        dir_exists = os.path.exists(path + '/../' + dir_path)
        if not dir_exists:
            os.makedirs(path + '/../' + dir_path)

        # 将内容写入文件
        filepath = dir_path + '/' + \
            str(aid) + '_' + str(user_id) + '_' + str(loan_id) + '_jaccard.json'

        dict_json = json.dumps(jac_relation)

        fp = open(path + '/../' + filepath, "a+")
        data = fp.write(dict_json + '\n')
        fp.close()
        return filepath
    def _gettestjcard(self,step):
        path = os.getcwd()
        # 测试ID
        # windows
        # file_name = ['a.csv','b.csv','c.csv','d.csv','e.csv']
        # data = []
        # with open(path+'/commands/data/'+file_name[int(step)], "r", encoding='utf-8') as csvfile:
        #     reader2 = csv.reader(csvfile)  # 读取csv文件，返回的是迭代类型
        #     for row in reader2:
        #         try:
        #             #get loan_info
        #             # loan_info = YiLoan().get(str(row[0]))
        #             # create_time = loan_info.create_time.strftime('%Y-%m-%d %H:%M:%S')
        #             create_time = self.getSplitDateTime(str(row[2]))
        #             user = [str(row[0]),create_time,str(row[1])]
        #             data.append(user)
        #         except Exception as e:
        #             logger.error("loan_id:%s is fail: %s" % (str(row[0]), e))
        # csvfile.close()
        # print(len(data))
        # data = [['3811476', '2017-12-20 16:28:53', '11111'], ['2354915', '2017-12-25 15:00:35', '2222']]
        # data = [['2354915', '2017-12-25 15:00:35', '2222']]
        data = [['6262219', '2017-08-08 12:37:49', '17792432']]
        # data = [['2354915', '2017-12-25 15:00:35', '2222']]
        # oAfBase = AfBase()
        # data = oAfBase.getJaccardDataByids(test_ids)
        # data_len = len(data)
        # if data_len == 0:
        #     logger.info("no jcard-match data")
        #     return None
        # lock status = 5
        # res = oAfBase.lockMatchStatus(data, 5)  # 锁定
        # if not res:
        #     logger.info("no jcard-match save fail")
        #     return None
        #  number of data to deal with
        # logger.info("there's %s data to deal with" % data_len)
        return data

    def _getJcardMatch(self):
        # get data from db
        # 读取7天内的数据
        today = datetime.now()
        time = today + timedelta(days=-7)
        time = time.strftime('%Y-%m-%d %H:%M:%S')

        oAfBase = AfBase()
        data = oAfBase.getJaccardData(time)
        data_len = len(data)
        if data_len == 0:
            logger.info("no jcard-match data")
            return None

        # lock status = 5
        res = oAfBase.lockMatchStatus(data, 5)  # 锁定
        if not res:
            logger.info("no jcard-match save fail")
            return None

        #  number of data to deal with
        logger.info("there's %s data to deal with" % data_len)

        return data


    #模型分析
    def index(self):
        base_data = self._getBase()
        if base_data is None:
            print("there is nothing data to deal with")
            return None

        for db_base in base_data:
            try:
                # 1. 分析后的数据
                obj = YyyAnalysis(db_base)
                dict_data = obj.run()
                print(dict_data)

                # 2. 保存到数据库中
                oAfDbAgent = AfDbAgent()
                res = oAfDbAgent.import_db(dict_data)
                print(res)

                # 3. 进行决策计算分值
                oAfCompluteRule = AfCompluteRule(dict_data)
                dict_res = oAfCompluteRule.compute()
                print(dict_res)

                # 4. 保存到结果集
                oAfResult = AfResult()
                db_result = oAfResult.addResult(dict_res)
                print(db_result)

                # 5. 更新af_base表
                res_match = db_base.finishMatched(2)
                print(res_match)

                # 6. 更新 st_strategy_request 状态
                db_strategy = StrategyRequest().getByReqId(db_base.request_id)
                if db_strategy is not None:
                    res = db_strategy.finished(2)
                    print(res)
            except Exception as e:
                logger.error("af_base.id:%s is fail: %s" % (db_base.id, e))

        return None
    
    def _getBase(self):
        today = datetime.now()
        time = today + timedelta(days=-7)
        time = time.strftime('%Y-%m-%d %H:%M:%S')

        # aid = 1 # @todo
        oAfBase = AfBase()
        data = oAfBase.getAfBaseData(time)
        data_len = len(data)
        if data_len == 0:
            logger.info("no anti-fraud data")
            return None

        # lock status = 1
        res = oAfBase.lockMatchStatus(data, 1)  # 锁定
        if not res:
            logger.info("no anti-fraud save fail")
            return None

        #  number of data to deal with
        logger.info("there's %s data to deal with" % data_len)

        return data

    def getbyid(self, id):
        oBase = AfBase()
        db_base = oBase.getById(int(id))
        print("af_base.id: ", id)
        obj = YyyAnalysis(db_base)
        dict_data = obj.run()
        print(dict_data)

    def getSplitDateTime(self, datestr):

        splitstr = datestr.split(':')
        #     27OCT2016:04:34:48
        strtemp = splitstr[0]
        year = strtemp[5:]
        mon = ''
        if (strtemp[2:5] == 'JAN'):
            mon = '01'
        elif (strtemp[2:5] == 'FEB'):
            mon = '02'
        elif (strtemp[2:5] == 'MAR'):
            mon = '03'
        elif (strtemp[2:5] == 'APR'):
            mon = '04'
        elif (strtemp[2:5] == 'MAY'):
            mon = '05'
        elif (strtemp[2:5] == 'JUN'):
            mon = '06'
        elif (strtemp[2:5] == 'JUL'):
            mon = '07'
        elif (strtemp[2:5] == 'AUG'):
            mon = '08'
        elif (strtemp[2:5] == 'SEP'):
            mon = '09'
        elif (strtemp[2:5] == 'OCT'):
            mon = '10'
        elif (strtemp[2:5] == 'NOV'):
            mon = '11'
        elif (strtemp[2:5] == 'DEC'):
            mon = '12'
        day = strtemp[0:2]
        hour = splitstr[1]
        miu = splitstr[2]
        sec = splitstr[3]
            # result = [year,mon,day,hour,miu,sec]
        result = '{0}-{1}-{2} {3}:{4}:{5}'.format(year, mon, day, hour, miu, sec)
        return result

    # 基于关系获得相关关系系数
    def relationmatch(self):
        '''
        获取所有已存在relation关系的数据
        '''
        relation_data = self._getRelationMatch()
        if relation_data is None:
            print("there is nothing data to deal with")
            return None

        for db_relation in relation_data:
            try:
                # 1. 分析后的数据
                obj = RelationData(db_relation)
                dict_res = obj.runRelation()
                print(dict_res)

                # 2. 保存到库中
                oAfDbAgent = AfDbAgent()
                db_res = oAfDbAgent.import_relation_db(dict_res)
                print(db_res)

                # 3. 更新为结束
                res = db_relation.finishMatched(8)
                print(res)

            except Exception as e:
                logger.error("relation.id:%s is fail: %s" % (db_relation.id, e))

        return None

    def _getRelationMatch(self):
        # get data from db
        # 读取7天内的数据
        today = datetime.now()
        time = today + timedelta(days=-7)
        time = time.strftime('%Y-%m-%d %H:%M:%S')

        oAfBase = AfBase()
        data = oAfBase.getRelationData(time)
        data_len = len(data)
        if data_len == 0:
            logger.info("no relation-match data")
            return None

        # lock status = 7
        # res = oAfBase.lockMatchStatus(data, 7)  # 锁定
        # if not res:
        #     logger.info("no jcard-match save fail")
        #     return None

        # number of data to deal with
        logger.info("there's %s data to deal with" % data_len)

        return data

    def run_spider(self):
        spider = Spider()
        spider.run()





