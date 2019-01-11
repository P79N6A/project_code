# -*- coding: utf-8 -*-
'''
个人借贷相关数据
'''
from .model import YiLoan
from .model import YiUser
import json

class Loan(object):

    def __init__(self, mobile):
        db_user = YiUser().getByMobile(mobile)
        if db_user is None:
            self.user_id = 0
        else:
            self.user_id = db_user.user_id

    def run(self,contact):
        try:
            mobile = json.loads(contact).get("mobile")
        except Exception as e:
            mobile = 0
        com_c_user = 1 if YiUser().getUidCounts([mobile]) > 0 else 0 
        success_num = YiLoan().getSuccessNum(self.user_id)
        last_succ_loan = YiLoan().getLastSuccLoan(self.user_id)
        overdue_loan = YiLoan().getOverdueLoan(self.user_id)
        
        user_loan_total = YiLoan().getApplyLoan(self.user_id)

        last_end_date = last_succ_loan.get('last_end_date')
        last_repay_time = last_succ_loan.get('last_repay_time')
        last_success_loan_days = last_succ_loan.get('last_success_loan_days')
        mth3_dlq_num = overdue_loan.get('mth3_dlq_num')
        mth3_dlq7_num = overdue_loan.get('mth3_dlq7_num')
        mth3_wst_sys = overdue_loan.get('mth3_wst_sys')
        mth6_dlq_ratio = overdue_loan.get('mth6_dlq_ratio')
        wst_dlq_sts = overdue_loan.get('wst_dlq_sts')
        mth6_total_num = overdue_loan.get('mth6_total_num')
        mth6_dlq_num = overdue_loan.get('mth6_dlq_num')

        frist_succ_loan = YiLoan().getFristSuccLoan(self.user_id)
        cd_amount = frist_succ_loan.get('amount')

        info =  {
            'com_c_user':int(com_c_user),
            'success_num':int(success_num) ,
            'last_end_date':str(last_end_date),
            'last_repay_time': str(last_repay_time),
            'last_success_loan_days':int(last_success_loan_days),
            'mth3_dlq_num': int(mth3_dlq_num),  
            'mth3_dlq7_num': int(mth3_dlq7_num), 
            'mth3_wst_sys': int(mth3_wst_sys),   
            'mth6_dlq_ratio': mth6_dlq_ratio, 
            'wst_dlq_sts': int(wst_dlq_sts),
            'mth6_dlq_num':int(mth6_dlq_num),
            'mth6_total_num':int(mth6_total_num),
            'user_loan_total':int(user_loan_total),
            'cd_amount':int(cd_amount)
        }
        return info
    
    def getIsOverdueAndIsLoading(self):
        is_overdue = YiLoan().getIsOverdue(self.user_id)
        is_loaning = YiLoan().getIsLoading(self.user_id)
        return_data = {
            'is_overdue':int(is_overdue),
            'is_loaning':int(is_loaning),
        }
        return return_data

