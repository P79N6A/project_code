# -*- coding: utf-8 -*-
'''
间接匹配(jaccard)
'''
from datetime import datetime

from lib.logger import logger
from model.jcard_match import JcardMatch
from model.base_model import DictMerge
import os
import time
from module.yiyiyuan import YiUser

class JcardmatchData(object):

    def __init__(self, jcardmatch):
        self.aid = jcardmatch.aid
        self.user_id = jcardmatch.user_id
        self.loan_id = jcardmatch.loan_id
        self.request_id = jcardmatch.id
        self.__init_obj()

    def __init_obj(self):
        # db类
        self.dbUser = None

    def runJcard(self):
        loan_time = datetime.now()
        # 1. 用户
        self.dbUser = YiUser().get(self.user_id)
        if self.dbUser is None:
            logger.error("user_id:%s db中没有纪录" % self.user_id)
            raise Exception(1002, "没有纪录")

        # 2. 杰卡德系数(jaccard)
        oJcardMatch = JcardMatch(self.dbUser.mobile,loan_time)
        jcard_match_res = oJcardMatch.run()
        dict_data = {}
        dict_data['jcard_match'] = jcard_match_res
        dict_data = self.__mergeData(self.aid, self.user_id, self.request_id, self.loan_id, dict_data)
        return dict_data
    
    def runtestJcard(self,user):
        user_id = user[0]
        loan_time = user[1]
        loan_id = user[2]
        # 1. 用户
        self.dbUser = YiUser().get(user_id)
        if self.dbUser is None:
            logger.error("user_id:%s db中没有纪录" % user_id)
            raise Exception(1002, "没有纪录")
        get_user = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        print('get_user: %s ' % (get_user))

        # 2. 杰卡德系数(jaccard)
        oJcardMatch = JcardMatch(self.dbUser.mobile)
        jcard_match_res = oJcardMatch.run()
        dict_data = {}
        dict_data['jcard_match'] = jcard_match_res
        dict_data = self.__mergeData(aid, user_id, 'test', loan_id, dict_data)
        return dict_data
    def __mergeData(self, aid, user_id, request_id, loan_id, dict_data):

        def mergeUser(dict_data):
            ''' 合并 user_id '''
            if dict_data is None:
                return {}
            if len(dict_data) == 0:
                return {}
            dict_data['aid'] = aid
            dict_data['user_id'] = user_id
            dict_data['request_id'] = request_id
            dict_data['loan_id'] = loan_id
            dict_data['create_time'] = datetime.now()
            return dict_data

        for k in dict_data:
            dict_data[k] = mergeUser(dict_data[k])

        return dict_data

