# -*- coding: utf-8 -*-
'''
二级匹配
'''
import re
import traceback
from datetime import datetime,timedelta
import pandas as pd
import numpy as np
import copy
import json 

from lib.logger import logger
from module.yiyiyuan.model import YiUser
from model.sparrow import Mobile
from model.open import OpenJxlStat
from model.analysis import AddressList
from module.address import Address
from module.detail import Detail


class MultiMatch(object):

    def __init__(self,phone,detail_phone,address_phone):
        self.phone            = phone
        self.detail_phone     = detail_phone
        self.address_phone    = address_phone
        self.return_list      = []
            

    def run(self):
        #一级关系
        self.__hit_db(self.detail_phone,self.address_phone,self.phone,self.phone,1)
        stair_relations = copy.deepcopy(self.return_list)
        if stair_relations :
            for stair_relation in stair_relations:
                address_phone = self.__get_address_phone(stair_relation[1])
                detail_phone = self.__get_detail_phone(stair_relation[1])
                self.__hit_db(detail_phone,address_phone,stair_relation[1],self.phone,2)
        return {'relation_ship': json.dumps(self.return_list)}

    def __hit_db(self,detail_phone,address_phone,phone,from_phone,level):
        # 二级匹配去掉本人手机号
        if phone in address_phone :
            address_phone.remove(phone)
        if phone in detail_phone :
            detail_phone.remove(phone)
        if from_phone in address_phone :
            address_phone.remove(from_phone)
        if from_phone in detail_phone :
            detail_phone.remove(from_phone)

        only_detail_phone  = np.setdiff1d(detail_phone,address_phone)
        only_address_phone = np.setdiff1d(address_phone,detail_phone)
        intersection_phone = np.intersect1d(detail_phone,address_phone)
        if len(only_detail_phone) >0 :
            only_detail_mobiles = Mobile().getUidsByMobiles(only_detail_phone)
            if only_detail_mobiles :
                [self.return_list.append([phone,mobile,level,2,from_phone]) for mobile in only_detail_mobiles]
        if len(only_address_phone)>0 :
            only_address_mobiles = Mobile().getUidsByMobiles(only_address_phone)
            if only_address_mobiles :
                [self.return_list.append([phone,mobile,level,1,from_phone]) for mobile in only_address_mobiles]
        if len(intersection_phone)>0 :
            intersection_mobiles = Mobile().getUidsByMobiles(intersection_phone)
            if intersection_mobiles :
                [self.return_list.append([phone,mobile,level,3,from_phone]) for mobile in intersection_mobiles]       

    def __get_address_phone(self, phone):
        '''
        通讯录相关
        '''
        address_phone = []
        userInfo = YiUser().getByMobile(phone)

        if userInfo:
            dbYiAddressList = AddressList().getByUserPhoneDict(userInfo.mobile)
            if len(dbYiAddressList) > 0:
                try:
                    oAddress = Address(dbYiAddressList)
                    address_phone = oAddress.getDistinctPhone()
                except Exception as e:
                    logger.error("phone:%s address is fail: %s" % (phone, e))
        return address_phone 

    def __get_detail_phone(self, phone):
        '''
        详单相关
        '''
        json_data = None
        detail_phone = []
        if phone:
            record = OpenJxlStat().getByPhone(phone)
            if record:
                json_data = OpenJxlStat().getDetail(record.get('detail_url'))
        if json_data:    
            try:
                oDetail = Detail(json_data)
                detail_phone = oDetail.getDistinctPhone()
            except Exception as e:
                logger.error("phone:%s jxl detail is fail: %s" % (phone, e))
        return detail_phone 