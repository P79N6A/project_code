# -*- coding: utf-8 -*-
'''
多级匹配
'''
from datetime import datetime

from lib.logger import logger
from model.multi_match import MultiMatch
from model.open import OpenJxlStat
from model.base_model import DictMerge

from module.address import Address
from module.detail import Detail
from module.yiyiyuan import YiUser
from module.yiyiyuan import YiAddressList

class MultimatchData(object):

    def __init__(self, multimatch):
        self.multimatch = multimatch
        self.aid = multimatch.aid
        self.user_id = multimatch.user_id
        self.loan_id = multimatch.loan_id
        self.request_id = multimatch.id
        self.detail_phone = []
        self.address_phone = []
        self.__init_obj()

    def __init_obj(self):
        # db类
        self.dbUser = None
        self.dbYiAddressList = None
        # analysis类
        self.oAddress = None
        self.oDetail = None

    def __address(self):
        # 获取通信录处理对象
        self.oAddress = None
        self.dbYiAddressList = YiAddressList().getByUserid(self.user_id)
        if len(self.dbYiAddressList) > 0:
            try:
                self.oAddress = Address(self.dbYiAddressList)
            except Exception as e:
                logger.error("user_id:%s address %s" % (self.user_id, e))

    def __detail(self, url):
        # 获取通话详单处理对象
        try:
            json_data = OpenJxlStat().getDetail(url)
            if json_data:    
                self.oDetail = Detail(json_data)
        except Exception as e:
            logger.error("user_id:%s jxl detail is fail: %s" % (self.user_id, e))
            self.oDetail = None

    def runMatch(self):
        # 1. 用户
        self.dbUser = YiUser().get(self.user_id)
        if self.dbUser is None:
            logger.error("user_id:%s db中没有纪录" % self.user_id)
            raise Exception(1002, "没有纪录")

        # 2. 获取通讯录对象
        self.__address()
        self.address_phone = self.oAddress.getDistinctPhone() if self.oAddress else []
        # 3. 获取通话详单对象
        record = OpenJxlStat().getByPhone(self.dbUser.mobile)
        if record :
            self.__detail(record.get('detail_url'))
        self.detail_phone = self.oDetail.getDistinctPhone() if self.oDetail else [] 
        # 4. 二级关系
        oMultiMatch = MultiMatch(self.dbUser.mobile,self.detail_phone,self.address_phone)
        multi_match_res = oMultiMatch.run()
        dict_data = {}
        dict_data['multi_match'] = multi_match_res
        dict_data = self.__mergeData(self.aid, self.user_id, self.request_id, self.loan_id, dict_data)
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

