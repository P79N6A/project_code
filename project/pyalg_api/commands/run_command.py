# -*- coding: utf-8 -*-
import json
from datetime import datetime, timedelta
from lib.logger import logger
from lib.config import get_config
from util.dictObj import dict2object
from .base_command import BaseCommand
from service import YyyAnalysis
from service import OperatorLogic
from module.yiyiyuan import YiUser
from model.open import OpenJxlStat
from model.strategy import CreditRequest
from model.strategy import StrategyRequest
from model.antifraud import AfBase,AfJacBase,AfTagBase
from model.antifraud import AfDbAgent, AfCompluteRule, AfResult
from model.antifraud import AfRelationBase

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
                # get user_info
                oYiUser = YiUser().getByUserId(db_strategy.user_id)
                record = None
                if oYiUser is not None:
                    record = OpenJxlStat().getByPhone(oYiUser.mobile)
                # get jxl_stat
                jxlstat_id = 0 if record is None else record.get('jxlstat_id')
                if db_strategy.version < 20:
                    if jxlstat_id == 0:
                        res = db_strategy.finished(0)
                        continue
                # save af_base
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
                afbase_id = AfBase().addBaseData(baseInfo)
                print(afbase_id)
                try:
                    #增加中间表
                    relation_id = AfRelationBase().saveData(afbase_id, db_strategy.id)
                    print(relation_id)
                except Exception as e:
                    print(e)
                #get last ID

                # save af_jac_base
                jacbaseinfo = {
                    'aid': db_strategy.aid,
                    'jac_match_id':0,
                    'request_id': db_strategy.req_id,
                    'base_id' : afbase_id,
                    'user_id' : db_strategy.user_id,
                    'loan_id' : db_strategy.loan_id,
                    'mobile' : 0 if oYiUser is None else oYiUser.mobile,
                    'jac_status' : 0,
                    'base_status' : 0,
                    'create_time' : datetime.now(),
                    'modify_time' : datetime.now()
                }
                afjacbase = AfJacBase().addData(jacbaseinfo)
                print(afjacbase)

                # save af_tag_base
                tagbaseinfo = {
                    'aid': db_strategy.aid,
                    'base_id': afbase_id,
                    'user_id': db_strategy.user_id,
                    'phone': 0 if oYiUser is None else oYiUser.mobile,
                    'tag_status': 0,
                    'create_time': datetime.now(),
                    'modify_time': datetime.now()
                }
                aftagbase = AfTagBase().addData(tagbaseinfo)
                print(aftagbase)
            except Exception as e:
                print(e)
                logger.error("af_base save fail %s" %e)

        return True
    
    def _getStrategy(self):
        now = datetime.now()
        t = now.strftime('%Y-%m-%d %H:%M:00')
        data = StrategyRequest().getData(t)
        data_len = len(data)
        if data_len == 0:
            logger.info("no strategy data")
            return None

        # lock status = 1
        res = StrategyRequest().lock(data, 101)
        if not res:
            logger.info("no strategy save fail")
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
            except Exception as e:
                logger.error("af_base.id:%s is fail: %s" % (db_base.id, e))
            finally:
                # 5. 更新af_base表
                res_match = db_base.finishMatched(2)
                print(res_match)
                # 6. 更新af_jac_base表
                # res_match = AfJacBase().finishBase(db_base.id)
                # print(res_match)
                # 7. 更新 st_strategy_request 状态
                #db_strategy = StrategyRequest().getByReqId(db_base)

                db_strategy = None
                relation_base_data = AfRelationBase().getInfo(db_base.id)
                if relation_base_data is None:
                    logger.error("af_base.id:%s 中间表中不存在" % (db_base.id))
                else:
                    db_strategy = StrategyRequest().getByIdData(relation_base_data.strategy_request_id)

                if db_strategy is not None:
                    res = db_strategy.finished(102)
                    print(res)
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

    #单条模型分析
    def getbyid(self, id):
        oBase = AfBase()
        db_base = oBase.getById(int(id))
        print("af_base.id: ", id)
        obj = YyyAnalysis(db_base)
        dict_data = obj.run()
        print(dict_data)

    # 运营商报告解析定时
    def run_strategy(self):
        strategyData = self.__getInitStrateryData()
        if strategyData is None:
            return None

        for oStrategy in strategyData:
            # 获取请求业务端数据
            try:
                oCredit = CreditRequest().getByStReqId(oStrategy.id)
                creditData = oCredit.credit_data
                creditData = json.loads(creditData)
            except Exception as e:
                logger.error("请求参数获取失败, StrategyId:" + str(oStrategy.id) + ", 异常信息: %s" % (e))
                continue

            aid = creditData.get('aid', None)
            phone = creditData.get('mobile', None)
            contain = self.__getContainByAid(aid)
            # 判断聚信立报告是否取得
            if oStrategy.version < 20:
                record = OpenJxlStat().getByPhone(phone)
                if record is None:
                    res = oStrategy.finished(0)
                    logger.error("未取得聚信立报告, 稍后执行")
                    continue
            # 拼接请求参数
            requestArgs = {
                'aid': aid,
                'credit_id': oStrategy.id,
                'identity': creditData.get('identity', None),
                'phone': phone,
                'realname': creditData.get('realname', None),
                'contain': contain,
                'contact': creditData.get('relation', None),
            }
            oRequest = dict2object(requestArgs)
            # 调用数据分析方法
            try:
                obj = OperatorLogic(oRequest)
                dict_data = obj.run()
                if dict_data is None:
                    logger.error("数据解析失败, StrategyId:" + str(oStrategy.id))
                else:
                    res = oStrategy.finished(2)
                    logger.error("数据解析成功, StrategyId:" + str(oStrategy.id))
            except Exception as e:
                logger.error("代码异常, StrategyId:" + str(oStrategy.id) + ", 异常信息: %s" % (e))

        return True

    def __getInitStrateryData(self):
        # 获取评测请求表锁单数据
        strategyData = StrategyRequest().getInitData()
        dataLen = len(strategyData)
        if dataLen == 0:
            logger.info("no strategy data to deal with")
            return None
        # 设置清洗锁单状态
        if not get_config().DEBUG:
            res = StrategyRequest().lock(strategyData, 1)
            if not res:
                logger.info("strategy lock fail")
                return None
        logger.info("there's %s data to deal with" % dataLen)
        return strategyData

    def __getContainByAid(self, aid):
        aid = int(aid)
        if aid == 1:
            return 1
        elif aid == 14:
            return 1
        elif aid == 16:
            return 8
        elif aid == 17:
            return 2
        else:
            return 1

    # 定时测试
    def run_test(self):
        pass
