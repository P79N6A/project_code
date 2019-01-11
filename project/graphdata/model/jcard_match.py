# -*- coding: utf-8 -*-
'''
间接匹配(jaccard)
'''
import re
import traceback
import time
from datetime import datetime,timedelta
import pandas as pd
import numpy as np
import copy
import json 
import time 
import os
from lib.logger import logger
from module.yiyiyuan.model import YiUser
from model.analysis import AddressList
from model.analysis import DetailList
from model.analysis import ReverseAddressList
from model.analysis import ReverseDetailList
from model.sparrow import Mobile
from module.address import Address

class JcardMatch(object):

    def __init__(self,phone):
        self.phone             = phone
        self.relation_list     = []
        self.return_list       = []
        self.all_relation_list = {}
        self.addr_all          = []
        self.addr_phones       = None
        self.user_mobiles      = None
        self.user_tels         = None

    def run(self):
        if self.phone is None:
            return {'jcard_relation': json.dumps(self.return_list), 'relation_list': json.dumps(self.all_relation_list)}

        '''通讯录'''
        # 查出该用户所有的通讯录号码
        self.addr_phones = self.__get_address_phone(self.phone)
        if self.addr_phones is None:
            return {'jcard_relation': json.dumps(self.return_list), 'relation_list': json.dumps(self.all_relation_list)}

        # 间接关系杰卡德系数
        start1 = time.clock()
        self.__get_addr_indirect_jaccard_()
        end1 = time.clock()
        print('indirect: %s Seconds' % (end1 - start1))


        end_time = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        print('end_time: %s ' % (end_time))
        os._exit(0)

        return {'jcard_relation': json.dumps(self.return_list),'relation_list':json.dumps(self.all_relation_list)}

    # 获取间接关系用户杰卡德系数(address)
    def __get_addr_indirect_jaccard_(self):
        # 反查出用户的号码
        indirect_start = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        print('indirect_start: %s ' % (indirect_start))
        
        addr_user_phones = ReverseAddressList().getAddrByPhones(self.addr_phones.tolist())

        get_address = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        print('get_address: %s ' % (get_address))

        # 号码去重
        if len(addr_user_phones) != 0:
            pd_addr_user_phones = pd.DataFrame(addr_user_phones, columns=['phone'])
            pd_user_phones = pd_addr_user_phones[(pd_addr_user_phones['phone']) != self.phone]['phone'].drop_duplicates()

        # 查出所有人的通讯录号码
        indirect_relations = []
        addr_other_phones = self.__get_address_phones(pd_user_phones.tolist())

        get_reverse_address = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        print('get_reverse_address: %s ' % (get_reverse_address))

        pd_other_phones = pd.DataFrame(addr_other_phones)
        pd_other_phones = pd_other_phones[(pd_other_phones['user_phone']) != self.phone ].groupby(['user_phone'])['phone']
       
        # 正则匹配
        is_tel = '^0\d{2,3}\d{7,8}$|^\d{7,8}$|^400'
        p = re.compile(is_tel, re.DOTALL)

        user_reg = self.addr_phones.str.contains(p)
        self.user_mobiles = self.addr_phones[user_reg==False] # 手机号
        self.user_tels = self.addr_phones[user_reg==True] # 固话


        time2 = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        print('jcard_start: %s ' % (time2))
        print(pd_other_phones.count())

        i=0
        for user_phone,phone in pd_other_phones:
            i = i+1
            print('jcard_start: %d ' % (i))

            phone_dup = phone[phone != user_phone].drop_duplicates() # 去重
            other_reg = phone_dup.str.contains(p)
            other_mobiles = phone_dup[other_reg==False] # 手机号
            other_tels = phone_dup[other_reg==True] # 固话

            indirect_relations.append(user_phone)

            time2 = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
            print('__calc_jaccard start: %s ' % (time2))

            # 计算jaccard系数
            self.__calc_jaccard(user_phone, phone_dup, other_mobiles, other_tels)

            time2 = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
            print('__calc_jaccard end: %s ' % (time2))

        calc_jaccard = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        print('calc_jaccard: %s ' % (calc_jaccard))

        if len(indirect_relations) > 0:
            self.all_relation_list.setdefault('indirect',indirect_relations)
        
        indirect_end = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        print('indirect_end: %s ' % (indirect_end))

    # 计算jaccard系数
    def __calc_jaccard(self, user_phone, phone_dup, other_mobiles, other_tels):
        jaccard = jaccard_phones = jaccard_others = 0

        # 全部号码jaccard系数
        intersection_addrlen = np.intersect1d(self.addr_phones, phone_dup).size
        union_addrlen = np.union1d(self.addr_phones, phone_dup).size
        # intersection_addrlen = len(set(phones.tolist()).intersection(addr_other_all))
        # union_addrlen = len(set(phones.tolist()).union(addr_other_all))
        # 手机号jaccard系数
        intersection_phones = np.intersect1d(self.user_mobiles, other_mobiles).size
        union_phones = np.union1d(self.user_mobiles, other_mobiles).size
        # intersection_phones = len(set(mobiles).intersection(other_phones[0]))
        # union_phones = len(set(mobiles).union(other_phones[0]))
        # 固话jaccard系数
        intersection_others = np.intersect1d(self.user_tels, other_tels).size
        union_others = np.union1d(self.user_tels, other_tels).size
        # intersection_others = len(set(tels).intersection(other_phones[1]))
        # union_others = len(set(tels).union(other_phones[1]))
        if union_addrlen != 0:
            jaccard = round((intersection_addrlen/union_addrlen),4)

        if union_phones != 0:
            jaccard_phones = round((intersection_phones / union_phones),4)

        if union_others != 0:
            jaccard_others = round((intersection_others / union_others),4)

        if not (jaccard == 0 and jaccard_phones == 0 and  jaccard_others == 0):
            self.return_list.append([
                    self.phone,
                    user_phone,
                    jaccard,
                    jaccard_phones,
                    jaccard_others,
                    intersection_addrlen,
                    intersection_phones,
                    intersection_others
                ])


    def __get_address_phone(self, phone):
        '''
        通讯录相关
        '''
        address_phone = None
        if phone:
            dbAddressList = AddressList().getByUserPhone(phone)
            if len(dbAddressList) > 0:
                try:
                    address_phone = self.__getDistinctPhone(dbAddressList)
                except Exception as e:
                    logger.error("phone:%s address is fail: %s" % (phone, e))
        return address_phone

    def __get_address_phones(self, phones):
        '''
        通讯录相关
        '''
        address_phone = []
        if phones:
            dbAddressList = AddressList().getByUserPhones(phones)
            if len(dbAddressList) > 0:
                try:
                    return dbAddressList
                except Exception as e:
                    logger.error("phone:%s address is fail: %s" % (phone, e))
        return address_phone

    def __get_detail_phone(self, phone):
        '''
        详单相关
        '''
        detail_phone = []
        if phone:
            dbDetailList = DetailList().getByUserPhone(phone)
            if len(dbDetailList) > 0:
                try:
                    detail_phone = self.__getDistinctPhone(dbDetailList)
                except Exception as e:
                    logger.error("phone:%s address is fail: %s" % (phone, e))
        return detail_phone

    def __getDistinctPhone(self, phones):
        if len(phones) == 0:
            raise Exception(1000, "phone list can't analysis")

        pd_other_phones = pd.DataFrame(phones)
        pd_other_phones = pd_other_phones[(pd_other_phones['phone']) != self.phone]['phone'].drop_duplicates()
        return pd_other_phones
