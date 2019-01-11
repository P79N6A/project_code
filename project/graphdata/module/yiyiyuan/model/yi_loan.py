# -*- coding: utf-8 -*-
# sqlacodegen  mysql://root:123!@#@127.0.0.1/xhh_test --outfile yyy.py --flask
from lib.application import db
from .base_model import BaseModel
from datetime import datetime,timedelta
from .yi_loan_flows import YiUserLoanFlow
from sqlalchemy import desc,and_
import time
import os

class YiLoan(db.Model, BaseModel):
    __bind_key__ = 'xhh_yiyiyuan'
    __tablename__ = 'yi_user_loan'

    loan_id = db.Column(db.BigInteger, primary_key=True)
    user_id = db.Column(db.BigInteger, nullable=False, index=True)
    loan_no = db.Column(db.String(64))
    amount = db.Column(db.Numeric(10, 4), nullable=False)
    recharge_amount = db.Column(db.Numeric(10, 4), nullable=False, server_default=db.FetchedValue())
    credit_amount = db.Column(db.Numeric(10, 4), nullable=False, server_default=db.FetchedValue())
    current_amount = db.Column(db.Numeric(10, 4), nullable=False)
    days = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    start_date = db.Column(db.DateTime)
    end_date = db.Column(db.DateTime)
    open_start_date = db.Column(db.DateTime)
    open_end_date = db.Column(db.DateTime)
    type = db.Column(db.Integer, server_default=db.FetchedValue())
    prome_status = db.Column(db.Integer, nullable=False)
    status = db.Column(db.Integer, nullable=False, index=True, server_default=db.FetchedValue())
    interest_fee = db.Column(db.Numeric(10, 4), nullable=False, server_default=db.FetchedValue())
    desc = db.Column(db.String(1024))
    contract = db.Column(db.String(64))
    contract_url = db.Column(db.String(128))
    last_modify_time = db.Column(db.DateTime)
    create_time = db.Column(db.DateTime)
    version = db.Column(db.Integer, server_default=db.FetchedValue())
    repay_time = db.Column(db.DateTime)
    withdraw_fee = db.Column(db.Numeric(10, 4), nullable=False, server_default=db.FetchedValue())
    chase_amount = db.Column(db.Numeric(10, 4))
    like_amount = db.Column(db.Numeric(10, 2), nullable=False, server_default=db.FetchedValue())
    collection_amount = db.Column(db.Numeric(10, 4), server_default=db.FetchedValue())
    coupon_amount = db.Column(db.Numeric(10, 4), server_default=db.FetchedValue())
    is_push = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    final_score = db.Column(db.Integer)
    repay_type = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    business_type = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    withdraw_time = db.Column(db.DateTime)
    bank_id = db.Column(db.BigInteger, nullable=False)
    is_calculation = db.Column(db.Integer, nullable=False)

    def get(self, loan_id):
        return self.query.get(loan_id)
    
    
    def getAllLoanByUids(self,user_ids):
        '''通讯录有过贷款的数量 '''
        if len(user_ids) == 0:
            return 0
        #select count(1) from yi_user_loan where user_id in (5419061,2968724,2697378,2616279)
        loanCounts = db.session.query(YiLoan).filter(YiLoan.user_id.in_(user_ids)).count()
        return loanCounts
    
    def getLoanedByUids(self,user_ids):
        '''通讯录有过放款的数量 '''
        if len(user_ids) == 0:
            return 0
        loanedStatus = [8,9,11,12,13]
        # select count(1) from yi_user_loan where user_id in (5419061,2968724,2697378,2616279) and  status in (8,9,11,12,13);
        res = db.session.query(YiLoan).filter(YiLoan.user_id.in_(user_ids),YiLoan.status.in_(loanedStatus)).count()
        return res

    def overdueAndNorepayByUids(self,user_ids):
        '''通讯录有逾期未还款的数量 '''
        if len(user_ids) == 0:
            return 0
        loanStatus = [12,13]
        # select count(1) from yi_user_loan where user_id in (5419061,2968724,2697378,2616279) and  status in (12,13);
        res = db.session.query(YiLoan).filter(YiLoan.user_id.in_(user_ids),YiLoan.status.in_(loanStatus)).count()
        return res
    
    def overdueAndRepayByUids(self,user_ids):
        '''通讯录有逾期已还款的数量 '''
        if len(user_ids) == 0:
            return 0
        #loanStatus = [8,9,11,12,13]
        loanStatus = [8]
         # select count(1) from yi_user_loan where user_id in (5419061,2968724,2697378,2616279) and  status in (12,13) and repay_time > end_date;
        res = db.session.query(YiLoan).filter(YiLoan.user_id.in_(user_ids),YiLoan.status.in_(loanStatus),YiLoan.repay_time > YiLoan.end_date).count()
        return res

    def overdue7AndNorepay(self,user_ids):
        '''逾期7天未还款数量 '''
        if len(user_ids) == 0:
            return 0
        loanStatus = [12,13]
        lastSevenday = (datetime.now() + timedelta(days=-7)).strftime('%Y-%m-%d %H:%M:%S')
        res = db.session.query(YiLoan).filter(YiLoan.user_id.in_(user_ids),YiLoan.status.in_(loanStatus),YiLoan.end_date < lastSevenday).count()
        return res
    
    def overdue7AndRepay(self,user_ids):
        '''逾期7天已还款数量 '''
        if len(user_ids) == 0:
            return 0
        userid_str ='"' + '","'.join(str(val) for val in user_ids) + '"'
        sql = '''
            SELECT
              count(1)
            FROM yi_user_loan 
            WHERE user_id IN(%s)
                AND status = 8
                AND repay_time > DATE_ADD(end_date,INTERVAL 7 DAY)
        ''' % userid_str
        count = db.session.execute(sql, bind=self.get_engine()).fetchone()
        return count[0]

    def lateApplyDay(self,user_ids):
        '''通讯录最近一次申请借款日 '''
        if len(user_ids) == 0:
            return 0
        res = db.session.query(YiLoan.create_time.label('create_time')).filter(YiLoan.user_id.in_(user_ids)).order_by(desc(YiLoan.create_time)).first()
        if res :
            return res[0]
        else:
            return 0

    def advanceRepay(self,user_ids):
        '''通讯录借款提前/正常还款 '''
        if len(user_ids) == 0:
            return 0
        loanStatus = [8]
        res = db.session.query(YiLoan).filter(YiLoan.user_id.in_(user_ids),YiLoan.status.in_(loanStatus),YiLoan.repay_time <= YiLoan.end_date).count()
        return res

    def getHistroyBadStatus(self,user_ids):
        '''通讯录中有过申请且历史最坏账单状态'''
        if len(user_ids) == 0:
            return 0
        # status = [3,7,8,9,11,12,13]
        # loan_status = [3,7]
        # datas = db.session.query(YiLoan.status,YiLoan.repay_time,YiLoan.end_date, YiUserLoanFlow.admin_id,YiUserLoanFlow.loan_status).outerjoin(YiUserLoanFlow,and_(YiLoan.loan_id == YiUserLoanFlow.loan_id,YiUserLoanFlow.loan_status in loan_status)).filter(YiLoan.user_id.in_(user_ids),YiLoan.status.in_(status)).limit(1000).all()
        userid_str ='"' + '","'.join(str(val) for val in user_ids) + '"'
        sql = "select ul.status,ul.repay_time,ul.last_modify_time,ul.end_date,ulf.admin_id,ulf.loan_status from yi_user_loan as ul left join yi_user_loan_flows as ulf on ul.loan_id = ulf.loan_id and ulf.loan_status in (3,7) where ul.user_id in (%s) and ul.status in (3,7,8,9,11,12,13) LIMIT 1000 " % userid_str
        datas = db.session.execute(sql, bind=self.get_engine()).fetchall()
        if len(datas) == 0:
            return None
        else:
            returnData = []
            for data in datas:
                if data.status == 3 :
                    returnData.append(-900)
                elif data.loan_status == 7 and data.admin_id and data.admin_id == -1:
                    returnData.append(-800)
                elif data.loan_status == 7 and data.admin_id and data.admin_id == -2:
                    returnData.append(-700)
                elif data.loan_status == 7 and data.admin_id and data.admin_id > 0:
                    returnData.append(-600)
                elif data.status == 9 :
                    returnData.append(-500)
                elif data.status == 8:
                    date = data.repay_time if data.repay_time else data.last_modify_time
                    diffDay = (date - data.end_date).days
                    returnData.append(diffDay-1)
                elif data.status in [11,12,13] :
                    date = data.repay_time if data.repay_time else datetime.now()
                    diffDay = (date - data.end_date).days
                    returnData.append(diffDay)
                else:
                    returnData.append(-900)
            if len(returnData) > 0 :
                realadl_tot_reject_num = len([ dt for dt in returnData if dt in [-800,-900,-700,-600]])
                realadl_tot_freject_num = len([ dt for dt in returnData if dt == -700])
                realadl_tot_sreject_num = len([ dt for dt in returnData if dt == -800])
                realadl_tot_dlq14_num = len([ dt for dt in returnData if dt > 14])
                tmp_num = len([ dt for dt in returnData if dt not in [-800,-900,-700,-600,-500]])
                realadl_dlq14_ratio = 999999 if tmp_num == 0 else float('%.2f' % (realadl_tot_dlq14_num / tmp_num))
                return {
                    'realadl_tot_reject_num':realadl_tot_reject_num,
                    'realadl_tot_freject_num':realadl_tot_freject_num,
                    'realadl_tot_sreject_num':realadl_tot_sreject_num,
                    'realadl_tot_dlq14_num':realadl_tot_dlq14_num,
                    'realadl_dlq14_ratio':realadl_dlq14_ratio,
                    'history_bad_status':max(returnData),
                }
            else:
                return None

    def getSucLoanByUids(self,user_ids):
        '''关联用户成功借款总笔数 '''
        if len(user_ids) == 0:
            return 0
        loanedStatus = [8,9,11,12,13]
        businessType = [1,4]
        res = db.session.query(YiLoan).filter(YiLoan.user_id.in_(user_ids),YiLoan.status.in_(loanedStatus),YiLoan.business_type.in_(businessType)).count()
        return res

    def overdue7day(self,user_ids):
        '''逾期7天及以上数量 '''
        if len(user_ids) == 0:
            return 0
        # 逾期未还款
        loanStatus = [11,12,13]
        lastSevenday = (datetime.now() + timedelta(days=-7)).strftime('%Y-%m-%d %H:%M:%S')
        res = db.session.query(YiLoan).filter(YiLoan.user_id.in_(user_ids),YiLoan.status.in_(loanStatus),YiLoan.end_date <= lastSevenday).count()
        # 逾期已还款
        userid_str = '"' + '","'.join(str(val) for val in user_ids) + '"'
        sql = '''
                    SELECT
                      count(1)
                    FROM yi_user_loan 
                    WHERE user_id IN(%s)
                        AND status = 8
                        AND repay_time >= DATE_ADD(end_date,INTERVAL 7 DAY)
                ''' % userid_str
        count = db.session.execute(sql, bind=self.get_engine()).fetchone()
        all_overdue = res+count[0]
        return all_overdue