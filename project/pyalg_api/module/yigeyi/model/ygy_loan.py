# -*- coding: utf-8 -*-
#
from lib.application import db
from model.base_model import BaseModel
from datetime import datetime,timedelta
from .ygy_loan_flows import YgyUserLoanFlow
from .ygy_user import YgyUser
from sqlalchemy import desc,and_
import time
import os
import pandas as pd
from sqlalchemy import MetaData

class YgyLoan(db.Model, BaseModel):
    __bind_key__ = 'xhh_yigeyi'
    __tablename__ = 'yi_user_loan'
    metadata = MetaData()

    loan_id = db.Column(db.BigInteger, primary_key=True)
    number = db.Column(db.BigInteger, nullable=False, index=True)
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
        loanCounts = db.session.query(YgyLoan).filter(YgyLoan.user_id.in_(user_ids)).count()
        return loanCounts
    
    def getLoanedByUids(self,user_ids):
        '''通讯录有过放款的数量 '''
        if len(user_ids) == 0:
            return 0
        loanedStatus = [8,9,11,12,13]
        # select count(1) from yi_user_loan where user_id in (5419061,2968724,2697378,2616279) and  status in (8,9,11,12,13);
        res = db.session.query(YgyLoan).filter(YgyLoan.user_id.in_(user_ids),YgyLoan.status.in_(loanedStatus)).count()
        return res

    def overdueAndNorepayByUids(self,user_ids):
        '''通讯录有逾期未还款的数量 '''
        if len(user_ids) == 0:
            return 0
        loanStatus = [12,13]
        # select count(1) from yi_user_loan where user_id in (5419061,2968724,2697378,2616279) and  status in (12,13);
        res = db.session.query(YgyLoan).filter(YgyLoan.user_id.in_(user_ids),YgyLoan.status.in_(loanStatus)).count()
        return res
    
    def overdueAndRepayByUids(self,user_ids):
        '''通讯录有逾期已还款的数量 '''
        if len(user_ids) == 0:
            return 0
        #loanStatus = [8,9,11,12,13]
        loanStatus = [8]
         # select count(1) from yi_user_loan where user_id in (5419061,2968724,2697378,2616279) and  status in (12,13) and repay_time > end_date;
        res = db.session.query(YgyLoan).filter(YgyLoan.user_id.in_(user_ids),YgyLoan.status.in_(loanStatus),YgyLoan.repay_time > YgyLoan.end_date).count()
        return res

    def overdue7AndNorepay(self,user_ids):
        '''逾期7天未还款数量 '''
        if len(user_ids) == 0:
            return 0
        loanStatus = [12,13]
        lastSevenday = (datetime.now() + timedelta(days=-7)).strftime('%Y-%m-%d %H:%M:%S')
        res = db.session.query(YgyLoan).filter(YgyLoan.user_id.in_(user_ids),YgyLoan.status.in_(loanStatus),YgyLoan.end_date < lastSevenday).count()
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
        res = db.session.query(YgyLoan.create_time.label('create_time')).filter(YgyLoan.user_id.in_(user_ids)).order_by(desc(YgyLoan.create_time)).first()
        if res :
            return res[0].strftime('%Y-%m-%d %H:%m:%S')
        else:
            return 0

    def advanceRepay(self,user_ids):
        '''通讯录借款提前/正常还款 '''
        if len(user_ids) == 0:
            return 0
        loanStatus = [8]
        res = db.session.query(YgyLoan).filter(YgyLoan.user_id.in_(user_ids),YgyLoan.status.in_(loanStatus),YgyLoan.repay_time <= YgyLoan.end_date).count()
        return res

    def getHistroyBadStatus(self,user_ids):
        '''通讯录中有过申请且历史最坏账单状态'''
        if len(user_ids) == 0:
            return 0
        # status = [3,7,8,9,11,12,13]
        # loan_status = [3,7]
        # datas = db.session.query(YgyLoan.status,YgyLoan.repay_time,YgyLoan.end_date, YiUserLoanFlow.admin_id,YiUserLoanFlow.loan_status).outerjoin(YiUserLoanFlow,and_(YgyLoan.loan_id == YiUserLoanFlow.loan_id,YiUserLoanFlow.loan_status in loan_status)).filter(YgyLoan.user_id.in_(user_ids),YgyLoan.status.in_(status)).limit(1000).all()
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
                    diffDay = 0
                    if data.end_date is not None and date is not None:
                        diffDay = (date - data.end_date).days
                        diffDay = diffDay-1
                    returnData.append(diffDay)
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
                    'realadl_dlq14_ratio_denominator':realadl_dlq14_ratio_denominator
                }
            else:
                return None

    def getSucLoanByUids(self,user_ids):
        '''关联用户成功借款总笔数 '''
        if len(user_ids) == 0:
            return 0
        loanedStatus = [8,9,11,12,13]
        businessType = [1,4]
        res = db.session.query(YgyLoan).filter(YgyLoan.user_id.in_(user_ids),YgyLoan.status.in_(loanedStatus),YgyLoan.business_type.in_(businessType)).count()
        return res

    def overdue7day(self,user_ids):
        '''逾期7天及以上数量 '''
        if len(user_ids) == 0:
            return 0
        # 逾期未还款
        loanStatus = [11,12,13]
        lastSevenday = (datetime.now() + timedelta(days=-7)).strftime('%Y-%m-%d %H:%M:%S')
        res = db.session.query(YgyLoan).filter(YgyLoan.user_id.in_(user_ids),YgyLoan.status.in_(loanStatus),YgyLoan.end_date <= lastSevenday).count()
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

    def getOverdueLoan(self, user_id):
        returnData = {
            'wst_dlq_sts': 0,
            'mth3_dlq_num': 0,
            'mth3_wst_sys': 0,
            'mth3_dlq7_num': 0,
            'mth6_dlq_ratio': 0,
            'mth6_total_num':0,
            'mth6_dlq_num': 0
        }
        loan_status = [8, 9, 11, 12, 13]
        business_type = [1, 4]
        where = and_(
            YgyLoan.status.in_(loan_status),
            YgyLoan.business_type.in_(business_type),
            YgyLoan.user_id == user_id,
            YgyLoan.repay_time.isnot(None)
        )
        datas = db.session.query(YgyLoan).filter(where).all()
        if len(datas) > 0:
            loan_data = []
            for data in datas:
                loan_data.append(self.row2dict(data))
            pd_loan_data = pd.DataFrame(loan_data)
            fuc_deal_time = lambda x: None if pd.isnull(x) else x.strftime('%Y-%m-%d') 
            pd_loan_data['repay_time'] = pd.to_datetime(pd_loan_data['repay_time']).apply(fuc_deal_time)
            pd_loan_data['end_date'] = pd.to_datetime(pd_loan_data['end_date']).apply(fuc_deal_time)
            pd_loan_data['create_time'] = pd.to_datetime(pd_loan_data['create_time']).apply(fuc_deal_time)
            due_day_series = pd.to_datetime(pd_loan_data['repay_time']) - pd.to_datetime(pd_loan_data['end_date'])
            fuc_deal_day = lambda x: int(x.days)
            pd_loan_data['due_day'] = due_day_series.apply(fuc_deal_day)
            returnData['wst_dlq_sts'] = int(pd_loan_data['due_day'].max())
            nowday = datetime.now().strftime('%Y-%m-%d')
            interval_day_series = pd.to_datetime(nowday) - pd.to_datetime(pd_loan_data['create_time'])
            pd_loan_data['interval_day'] = interval_day_series.apply(fuc_deal_day)

            mth3_loan_num = pd_loan_data['due_day'][pd_loan_data['interval_day']<90].count()
            if mth3_loan_num > 0 :
                returnData['mth3_dlq_num'] = pd_loan_data['interval_day'][
                    (pd_loan_data['interval_day'] < 90) & (pd_loan_data['due_day'] > 0)].count()
                returnData['mth3_wst_sys'] = pd_loan_data['due_day'][pd_loan_data['interval_day'] < 90].max()
                returnData['mth3_dlq7_num'] = pd_loan_data['due_day'][
                    (pd_loan_data['interval_day'] < 90) & pd_loan_data['due_day'] >= 7].count()
            mth6_total_num = pd_loan_data['due_day'][(pd_loan_data['interval_day'] < 180)].count()
            returnData['mth6_total_num'] = int(mth6_total_num)
            if mth6_total_num > 0:
                mth6_dlq_num = pd_loan_data['due_day'][(pd_loan_data['interval_day']<180) & pd_loan_data['due_day'] > 0].count()
                returnData['mth6_dlq_num'] = int(mth6_dlq_num)
                returnData['mth6_dlq_ratio'] = float('%.2f' % (mth6_dlq_num / mth6_total_num))
        return returnData

    def getLastSuccLoan(self, user_id):
        returnData = {
            'last_end_date': '',
            'last_repay_time': '',
            'last_success_loan_days': 0
        }
        loan_status = [8]
        business_type = [1, 4]
        where = and_(
            YgyLoan.status.in_(loan_status),
            YgyLoan.business_type.in_(business_type),
            YgyLoan.user_id == user_id
        )
        data = db.session.query(YgyLoan).filter(where).order_by(YgyLoan.repay_time.desc()).limit(1).first()
        if data is not None:
            returnData['last_end_date'] = data.end_date.strftime('%Y-%m-%d %H:%m:%S')
            returnData['last_repay_time'] = data.repay_time.strftime('%Y-%m-%d %H:%m:%S')
            returnData['last_success_loan_days'] = int(data.days)
        return returnData

    def getIsLoading(self, user_id):
        if not user_id:
            return 0

        loan_status = [6, 9]
        business_type = [1, 4]
        where = and_(
            YgyLoan.status.in_(loan_status),
            YgyLoan.business_type.in_(business_type),
            YgyLoan.user_id == user_id
        )
        data = db.session.query(YgyLoan).filter(where).order_by(YgyLoan.repay_time.desc()).limit(1).first()
        if data is None:
            return 0
        return 1

    def getIsOverdue(self, user_id):
        if not user_id:
            return 0

        loan_status = [11, 12, 13]
        business_type = [1, 4]
        where = and_(
            YgyLoan.status.in_(loan_status),
            YgyLoan.business_type.in_(business_type),
            YgyLoan.user_id == user_id
        )
        data = db.session.query(YgyLoan).filter(where).order_by(YgyLoan.repay_time.desc()).limit(1).first()
        if data is None:
            return 0
        return 1

    def getApplyLoan(self, user_id):
        if not user_id:
            return 0
        business_type = [1, 4]
        where = and_(
            YgyLoan.user_id == user_id,
            YgyLoan.number == 0,
            YgyLoan.business_type.in_(business_type)
        )
        data = db.session.query(YgyLoan).filter(where).count()
        return data

    def getSuccessNum(self, user_id):
        sql = 'select count(1) as success_num from yi_user_loan ul inner join yi_user u on ul.user_id = u.user_id  where u.user_id = "%s" and ul.status =8 and ul.number= 0 and ul.business_type in (1,4)' %( user_id)
        count = db.session.execute(sql, bind=self.get_engine()).fetchone()
        return count[0]