# -*- coding: utf-8 -*-
'''
通讯录数据、贷款交集
'''
import re
import pandas as pd
import math

from .model import YiUser
from .model import YiLoan

class Addrloan(object):

    def __init__(self, pd_address):
        if pd_address is None:
            raise Exception(1001, "pd_address can't analysis")

        self.pd_address = pd_address

    def getMobiles(self,pd_address):
        addr_phones = pd_address.phone.str.replace('-','')
        addr_phones = addr_phones.drop_duplicates()
        addr_phones = addr_phones[addr_phones.str.len()==11]
        is_tel = '^0\d{2,3}\d{7,8}$|^\d{7,8}$|^400'
        p = re.compile(is_tel, re.DOTALL)
        mobiles=addr_phones[addr_phones.str.contains(p)==False]
        #最多分析1000个, 以减少数据库压力.(应该极小几率出现)
        mlen = mobiles.count()
        if mlen == 0:
            return []
        if mlen > 1000:
            mlen=1000
        return list(mobiles[0:mlen])

    def chunk(self, biglist):
        #大列表分割成小列表
        limit = 200
        mlen = len(biglist)
        mc = math.ceil(mlen/limit)
        lst = []
        for i in range(0,int(mc)):
            lst.append(biglist[i*limit:(i+1)*limit])

        return lst

    def getUsers(self,lst):
        user_ids = []
        user_ids = YiUser().getUidsByMobiles(lst)
        return user_ids

    def run(self):
        # 获取手机号
        mobiles = self.getMobiles(self.pd_address)
        if len(mobiles) == 0:
            return None
        # 分隔成200每段
        #mobiles_lst = self.chunk(mobiles)
        # 获取用户user_id
        user_ids = self.getUsers(mobiles)
        if  len(user_ids) == 0:
            return None
        user_total = YiUser().getUidCounts(mobiles)
        loan_all = YiLoan().getAllLoanByUids(user_ids)
        loan_total = YiLoan().getLoanedByUids(user_ids)
        overdue_norepay = YiLoan().overdueAndNorepayByUids(user_ids)
        overdue_repay = YiLoan().overdueAndRepayByUids(user_ids)
        overdue7_norepay = YiLoan().overdue7AndNorepay(user_ids)
        overdue7_repay = YiLoan().overdue7AndRepay(user_ids)
        last_loan_day = YiLoan().lateApplyDay(user_ids)
        normal_repay = YiLoan().advanceRepay(user_ids)
        history_bad_status_total = YiLoan().getHistroyBadStatus(user_ids)
        # 分隔成200每段
        # user_ids_lst = self.chunk(user_ids) 
        realadl_tot_reject_num = history_bad_status_total.get('realadl_tot_reject_num') if history_bad_status_total else 0
        realadl_tot_freject_num = history_bad_status_total.get('realadl_tot_freject_num') if history_bad_status_total else 0
        realadl_tot_sreject_num = history_bad_status_total.get('realadl_tot_sreject_num') if history_bad_status_total else 0
        realadl_tot_dlq14_num = history_bad_status_total.get('realadl_tot_dlq14_num') if history_bad_status_total else 0
        realadl_dlq14_ratio = history_bad_status_total.get('realadl_dlq14_ratio') if history_bad_status_total else 0
        history_bad_status = history_bad_status_total.get('history_bad_status') if history_bad_status_total else 0
        realadl_dlq14_ratio_denominator = history_bad_status_total.get('realadl_dlq14_ratio_denominator') if history_bad_status_total else 0
        realadl_wst_dlq_sts = history_bad_status
        info =  {
            # 通讯录与user表数量
            'user_total': int(user_total),

            # 通讯录与loan表总数(含申请)
            'loan_all': int(loan_all),

            #通讯录有过放款
            'loan_total': int(loan_total),

            #逾期未还款
            'overdue_norepay': int(overdue_norepay),   

            #逾期已还款
            'overdue_repay': int(overdue_repay), 

            #逾期7天未还款
            'overdue7_norepay':int(overdue7_norepay),   

            #逾期7天已还款
            'overdue7_repay':int(overdue7_repay), 

            #通讯录最近一次申请借款天数
            'last_loan_day':str(last_loan_day),  

            #通讯录借款提前/正常还款
            'normal_repay': int(normal_repay),

            'realadl_tot_reject_num':int(realadl_tot_reject_num),

            'realadl_tot_freject_num':int(realadl_tot_freject_num),

            'realadl_tot_sreject_num':int(realadl_tot_sreject_num),

            'realadl_tot_dlq14_num':int(realadl_tot_dlq14_num),

            'realadl_dlq14_ratio':realadl_dlq14_ratio,

            'history_bad_status':int(history_bad_status),
            
            'realadl_dlq14_ratio_denominator':int(realadl_dlq14_ratio_denominator),

            'realadl_wst_dlq_sts':int(realadl_wst_dlq_sts)
        }
        return info
  