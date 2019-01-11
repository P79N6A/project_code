# -*- coding: utf-8 -*-

from lib.logger import logger
from module.yiyiyuan import YyyLoan
from module.yiyiyuan import Addrloan
# from module.yigeyi import YgyLoan
# from module.yigeyi import YgyAddrLoan
from module.huakashop import HkShopLoan
from module.huakashop import HkShopAddrLoan
from module.huakaborrow import HkBorrowLoan
from module.huakaborrow import HkBorrowAddrLoan
from module.yigeyinew import YgyLoan
from module.yigeyinew import YgyAddrLoan

import pandas as pd
from collections import ChainMap

class LoanLogic(object):

    def __init__(self, pd_address, phone, contact, contain=1):
        self.contain = int(contain)
        self.pd_address = pd_address
        self.contact = contact #常用联系人
        self.phone = phone
        self.__dictData()

    def __dictData(self):
        self.addr_loan = {
            'user_total': 0,
            'loan_all': 0,
            'loan_total': 0,
            'overdue_norepay': 0,   
            'overdue_repay': 0, 
            'overdue7_norepay':0,   
            'overdue7_repay':0, 
            'last_loan_day':'',  
            'normal_repay': 0,
            'realadl_tot_reject_num':0,
            'realadl_tot_freject_num':0,
            'realadl_tot_sreject_num':0,
            'realadl_tot_dlq14_num':0,
            'realadl_dlq14_ratio':999999,
            'history_bad_status':0,
            'realadl_dlq14_ratio_denominator':0,
            'realadl_wst_dlq_sts':0
        }
        self.yyy_loan = {}
        self.yyy_addr_loan = {}
        self.ygy_loan = {}
        self.ygy_addr_loan = {}
        self.hk_borrow_loan = {}
        self.hk_borrow_addr_loan = {}
        self.hk_shop_loan = {}
        self.hk_shop_addr_loan = {}
        self.return_data = {}
        self.merge_data = {}
        self.YyyLoanObj = None
        self.YgyLoanObj = None
        self.HkBorrowLoanObj = None
        self.HkShopLoanObj = None

    def run(self):
        #都需要查询的数据
        self.queryAll()
        #一亿元
        if (self.contain & 1):
            if self.pd_address is not None:
                YyyAddrLoanObj = Addrloan(self.pd_address)
                self.yyy_addr_loan = YyyAddrLoanObj.run()
            if not self.yyy_addr_loan:
                self.yyy_addr_loan = self.addr_loan    
            self.yyy_loan = self.YyyLoanObj.run(self.contact)
            contain = {'contain_id':1}
            self.return_data['yyy_loan'] = dict(ChainMap(contain,self.yyy_loan,self.yyy_addr_loan))
            
        #一个亿
        if (self.contain & 2):
            if self.pd_address is not None:
                YgyAddrLoanObj = YgyAddrLoan(self.pd_address)
                self.ygy_addr_loan = YgyAddrLoanObj.run()
            if not self.ygy_addr_loan:
                self.ygy_addr_loan = self.addr_loan    
            self.ygy_loan = self.YgyLoanObj.run(self.contact)
            contain = {'contain_id':2}
            self.return_data['ygy_loan'] = dict(ChainMap(contain,self.ygy_loan,self.ygy_addr_loan))
        #花卡借
        if (self.contain & 4):
            if self.pd_address is not None:
                HkBorrowAddrLoanObj = HkBorrowAddrLoan(self.pd_address)
                self.hk_borrow_addr_loan = HkBorrowAddrLoanObj.run()
            if not self.hk_borrow_addr_loan:
                self.hk_borrow_addr_loan = self.addr_loan    
            self.hk_borrow_loan = self.HkBorrowLoanObj.run(self.contact)
            contain = {'contain_id':4}
            self.return_data['sdh_loan'] = dict(ChainMap(contain,self.hk_borrow_loan,self.hk_borrow_addr_loan))

        #花卡商城
        if (self.contain & 8):
            if self.pd_address is not None:
                HkShopAddrLoanObj = HkShopAddrLoan(self.pd_address)
                self.hk_shop_addr_loan = HkShopAddrLoanObj.run()
            if not self.hk_shop_addr_loan:
                self.hk_shop_addr_loan = self.addr_loan    
            self.hk_shop_loan = self.HkShopLoanObj.run(self.contact)
            contain = {'contain_id':8}
            self.return_data['sdhs_loan'] = dict(ChainMap(contain,self.hk_shop_loan,self.hk_shop_addr_loan))
            
        #合并数据
        self.mergeLoanData()
        return self.return_data,self.merge_data

    def queryAll(self):
        is_res = {}
        self.YyyLoanObj = YyyLoan(self.phone)
        is_res['yyy_res'] = self.YyyLoanObj.getIsOverdueAndIsLoading()
        self.YgyLoanObj = YgyLoan(self.phone)
        is_res['ygy_res'] = self.YgyLoanObj.getIsOverdueAndIsLoading()
        # self.HkBorrowLoanObj = HkBorrowLoan(self.phone)
        # is_res['hkb_res'] = self.HkBorrowLoanObj.getIsOverdueAndIsLoading()
        self.HkShopLoanObj = HkShopLoan(self.phone)
        is_res['hks_res'] = self.HkShopLoanObj.getIsOverdueAndIsLoading()
        pd_data = pd.DataFrame(is_res)
        t_pd_data = pd_data.T
        is_overdue = 1 if int(t_pd_data['is_overdue'].sum()) > 0 else 0
        is_loaning = 1 if int(t_pd_data['is_loaning'].sum()) > 0 else 0
        self.merge_data['is_overdue'] = is_overdue
        self.merge_data['is_loaning'] = is_loaning

    def mergeLoanData(self):
        if len(self.return_data) < 1:
            return 
        pd_data = pd.DataFrame(self.return_data)
        t_pd_data = pd_data.T
        self.merge_data['mth3_dlq_num'] = int(t_pd_data.mth3_dlq_num.sum())
        self.merge_data['mth3_dlq7_num'] = int(t_pd_data.mth3_dlq7_num.sum())
        self.merge_data['mth3_wst_sys'] = int(t_pd_data.mth3_wst_sys.max())
        self.merge_data['wst_dlq_sts'] = int(t_pd_data.wst_dlq_sts.max())
        mth6_total_num = int(t_pd_data.mth6_total_num.sum())
        mth6_dlq_num = int(t_pd_data.mth6_dlq_num.sum())
        self.merge_data['mth6_dlq_num'] = mth6_dlq_num
        self.merge_data['mth6_total_num'] = mth6_total_num
        self.merge_data['mth6_dlq_ratio'] = float('%.2f' % (mth6_dlq_num / mth6_total_num)) if mth6_total_num > 0 else 0
        self.merge_data['success_num'] = int(t_pd_data.success_num.sum())
        self.merge_data['user_loan_total'] = int(t_pd_data.user_loan_total.sum())
        self.merge_data['type'] = 2 if int(t_pd_data.success_num.sum()) > 0 else 1
        self.merge_data['last_success_loan_days'] = int(t_pd_data.sort_values(['last_repay_time'], ascending=False).head(1)['last_success_loan_days'])
        realadl_tot_dlq14_num = int(t_pd_data.realadl_tot_dlq14_num.sum())
        realadl_dlq14_ratio_denominator = int(t_pd_data.realadl_dlq14_ratio_denominator.sum())
        self.merge_data['realadl_dlq14_ratio'] =  999999 if realadl_dlq14_ratio_denominator == 0 else float('%.2f' % (realadl_tot_dlq14_num / realadl_dlq14_ratio_denominator))
        self.merge_data['realadl_tot_dlq14_num'] = realadl_tot_dlq14_num
        self.merge_data['realadl_tot_freject_num'] = int(t_pd_data.realadl_tot_freject_num.sum())
        self.merge_data['realadl_tot_reject_num'] = int(t_pd_data.realadl_tot_reject_num.sum())
        self.merge_data['realadl_tot_sreject_num'] = int(t_pd_data.realadl_tot_sreject_num.sum())
        self.merge_data['realadl_wst_dlq_sts'] = int(t_pd_data.realadl_wst_dlq_sts.max())
        self.merge_data['com_c_user'] = 1 if int(t_pd_data.com_c_user.sum()) > 0 else 0
        self.merge_data['history_bad_status'] = int(t_pd_data.history_bad_status.max())
        self.merge_data['loan_all'] = int(t_pd_data.loan_all.sum())
        self.merge_data['overdue_norepay'] = int(t_pd_data.overdue_norepay.sum())
        self.merge_data['overdue_repay'] = int(t_pd_data.overdue_repay.sum())
        self.merge_data['overdue7_norepay'] = int(t_pd_data.overdue7_norepay.sum())
        self.merge_data['overdue7_repay'] = int(t_pd_data.overdue7_repay.sum())
        self.merge_data['loan_total'] = int(t_pd_data.loan_total.sum())
        self.merge_data['last_loan_day'] = str(t_pd_data.last_loan_day.max())
        self.merge_data['normal_repay'] = int(t_pd_data.normal_repay.sum())
        
        

        
    
   


    