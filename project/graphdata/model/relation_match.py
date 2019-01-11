# -*- coding: utf-8 -*-
'''
间接匹配(jaccard)
'''
import pandas as pd

import re
import json
import os
from lib.logger import logger
from model.antifraud import AfDetail
from model.antifraud import AfReport
from module.yiyiyuan.model import YiUser
from module.yiyiyuan.model import YiLoan
from module.yiyiyuan.model import YiUserPassword


class RelationMatch(object):
    def __init__(self, relation):
        # self.url = relation.jcard_result
        self.first = []  #一级
        self.second = []  #二级
        self.reverse = []  #逆一级
        self.indirect = []  #间接关系
        self.list_data = []  #一 二 逆 关系集合
        self.reged_users = [] #一 二 逆中已注册用户
        self.in_reged_users = [] # 间接关系中已注册用户
        self.dict_relation = {}
        self.list_jaccard = []
        self.return_dict = {}
        # self.request_id = relation.request_id

        # test
        self.url = relation.get('jcard_result')
        self.request_id =relation.get('request_id')

    def set_data(self):
        self.first = list(set(self.dict_relation.get('first', [])))
        self.second = list(set(self.dict_relation.get('second', [])))
        self.reverse = list(set(self.dict_relation.get('reverse', [])))
        self.indirect = list(set(self.dict_relation.get('indirect', [])))
        self.list_data = list(set(self.first + self.second + self.reverse))
        # 查询已注册用户
        self.reged_users = YiUser().getUidsByMobiles(self.list_data)
        # 查询间接关系中已注册用户
        x_relation = list(set(self.indirect).difference(set(self.list_data)))  # indirect中有而list_data中没有的
        self.in_reged_users = YiUser().getUidsByMobiles(x_relation)
        # 一 二 逆一级 间接关系注册用户
        self.all_reged_users = list(set(self.reged_users).union(set(self.in_reged_users)))

    def run(self):
        if self.url is None:
            return self.return_dict
        '''获取关系用户数据'''
        self._get_relation_by_url(self.url)

        '''计算杰卡德相关系数'''
        self._get_jaccard()

        '''计算关系相关值'''
        if self.dict_relation is None:
            return self.return_dict

        '''关系用户分组'''
        self.set_data()

        '''获取关系用户中的安卓相关系数'''
        self._get_android_data()

        '''获取关系用户中的vs_connect_match_1_no4'''
        self._get_connect_match_avg()

        '''获取关系用户中的vs_connect_match_1_no4'''
        self._get_report_shutdown_avg()

        '''获取关系用户7天逾期率'''
        self._get_yiqi_7_p()
        return self.return_dict

    def _get_relation_by_url(self,url):
        # 获取通话详单处理对象
        if len(url) == 0:
            logger.error("request_id:%s jaccard_url is None: %s" % (self.request_id))
            return None
        try:
            path = os.getcwd()
            file_path = path + '/..' + url
            regex = re.compile(r'\\(?![/u"])')
            records = [json.loads(regex.sub(r"\\\\", line).replace("'", '"')) for line in open(file_path)]
            relation_data = records[0].get('relation')
            jaccard_data = records[0].get('jaccard')
            if len(relation_data) == 0:
                logger.error("request_id:%s No relation_data: %s" % (self.request_id))


            #relation dict
            self.dict_relation = json.loads(relation_data)

            #jaccard dict
            self.list_jaccard = list(json.loads(jaccard_data))

        except Exception as e:
            logger.error("request_id:%s jaccard is fail: %s" % (self.request_id, e))
            return None

    def _get_yiqi_7_p(self):
        #
        # 关联用户中成功借款总笔数
        suc_all_loan = YiLoan().getSucLoanByUids(self.all_reged_users)

        # 关联用户中逾期大于等于7天的总笔数
        overdue_7_day = YiLoan().overdue7day(self.all_reged_users)
        if suc_all_loan == 0 or overdue_7_day == 0:
            self.return_dict['yiqi_7_p'] = 0
            return None
        # 逾期7天及以上占比
        self.return_dict['yiqi_7_p'] = float('%.4f' % round(overdue_7_day/suc_all_loan,4))

    def _get_android_data(self):

        # 查询已注册中安卓用户数
        android_count = YiUserPassword().getAndroidcount(self.reged_users)
        self.return_dict['num_android_no4'] = android_count

        # 获取注册用户差集
        in_android_count = YiUserPassword().getAndroidcount(self.in_reged_users)
        # 安卓用户占比
        if self.all_reged_users == 0:
            self.return_dict['p_num_android'] = 0
            return None
        self.return_dict['p_num_android'] = round((android_count+in_android_count)/len(self.all_reged_users),4)

    def _get_connect_match_avg(self):
        # 获取已注册用户的vs_connect_match
        match_data = AfDetail().getVsConnectMatchByUserIds(self.reged_users)
        self.return_dict['vs_connect_match_1_no4'] = 0
        if len(match_data) > 0:
            self.return_dict['vs_connect_match_1_no4'] = round(sum(match_data)/len(match_data),4)

    def _get_report_shutdown_avg(self):
        # 获取已注册用户的vs_connect_match
        match_data = AfReport().getReportShutdownByUserIds(self.reged_users)
        self.return_dict['report_shutdown_1_no4'] = 0
        if len(match_data) > 0:
            self.return_dict['report_shutdown_1_no4'] = round(sum(match_data) / len(match_data), 4)

    def _get_jaccard(self):
        '''获取杰卡德均值'''
        if self.list_jaccard is None:
            return self.return_dict
        pd_jaccard = pd.DataFrame(self.list_jaccard)
        pd_jaccard.columns = ['user_phone', 'phone', 'jac_all', 'jac_phone', 'jac_other', 'i_all', 'i_p', 'i_o',
                              'source', 'type']
        #过滤间接关系
        pd_jaccard = pd_jaccard[(pd_jaccard['type']) != 'indirect']
        #求jac_all最大值
        self.return_dict['jaccard_all_max_no4'] = float(pd_jaccard[['jac_all']].sort_values(by='jac_all', ascending=False).head(1).jac_all)

        #过滤jac_phone为0 并按关联手机号去重求jac_phone均值
        jac_phone_all = pd_jaccard[(pd_jaccard['jac_phone']) != 0].drop_duplicates(['phone']).jac_phone
        sum_all = jac_phone_all.sum()
        count_all = jac_phone_all.count()
        if count_all == 0 or sum_all == 0:
            self.return_dict['avg_jaccard_phone_no4'] = 0
            return None
        self.return_dict['avg_jaccard_phone_no4'] = float('%.4f' % round(sum_all/count_all,4))





