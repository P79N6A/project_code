# -*- coding: utf-8 -*-
# 注意这里使用了阿里云本地库的通讯录
#
from lib.application import db
from model.base_model import BaseModel
from sqlalchemy import MetaData
from sqlalchemy import and_,or_
from .shop_loan_repay import ShopLoanRepay
from .shop_loan_user_loan import ShopLoanUserLoan
import pandas as pd
from datetime import datetime,timedelta

class ShopLoanOverdueLoan(db.Model, BaseModel):
    __bind_key__ = 'xhh_yigeyi'
    __tablename__ = 'loan_overdue_loan'
    metadata = MetaData()

    id = db.Column(db.BigInteger, primary_key=True)
    loan_id = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    user_id = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    bank_id = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    loan_no = db.Column(db.String(64))
    amount =  db.Column(db.Numeric(10, 2), nullable=False, server_default=db.FetchedValue())
    days = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    desc = db.Column(db.String(128))
    start_date = db.Column(db.DateTime)
    end_date = db.Column(db.DateTime)
    type = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    loan_status = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    interest_fee =  db.Column(db.Numeric(10, 2), nullable=False, server_default=db.FetchedValue())
    late_fee =  db.Column(db.Numeric(10, 2), nullable=False, server_default=db.FetchedValue())
    withdraw_fee =  db.Column(db.Numeric(10, 2), nullable=False, server_default=db.FetchedValue())
    chase_amount =  db.Column(db.Numeric(10, 2), nullable=False, server_default=db.FetchedValue())
    business_type = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    create_time = db.Column(db.DateTime)
    modify_time = db.Column(db.DateTime)
    version = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())



    def overdueAndNorepayByUids(self,user_ids):
        '''通讯录有逾期未还款的数量 '''
        if len(user_ids) == 0:
            return 0
        loanStatus = [7]
        # select count(1) from yi_user_loan where user_id in (5419061,2968724,2697378,2616279) and  status in (12,13);
        res = db.session.query(ShopLoanOverdueLoan).filter(ShopLoanOverdueLoan.user_id.in_(user_ids),ShopLoanOverdueLoan.loan_status.in_(loanStatus)).count()
        return res

    def overdueAndRepayByUids(self,user_ids):
        '''通讯录有逾期已还款的数量 '''
        if len(user_ids) == 0:
            return 0
        #loanStatus = [8,9,11,12,13]
        loanStatus = [6]
         # select count(1) from yi_user_loan where user_id in (5419061,2968724,2697378,2616279) and  status in (12,13) and repay_time > end_date;
        res = db.session.query(ShopLoanOverdueLoan).filter(ShopLoanOverdueLoan.user_id.in_(user_ids),ShopLoanOverdueLoan.loan_status.in_(loanStatus)).count()
        return res

    def overdue7AndNorepay(self,user_ids):
        '''逾期7天未还款数量 '''
        if len(user_ids) == 0:
            return 0
        loanStatus = [7]
        lastSevenday = (datetime.now() + timedelta(days=-7)).strftime('%Y-%m-%d %H:%M:%S')
        res = db.session.query(ShopLoanOverdueLoan).filter(ShopLoanOverdueLoan.user_id.in_(user_ids),ShopLoanOverdueLoan.loan_status.in_(loanStatus),ShopLoanOverdueLoan.end_date < lastSevenday).count()
        return res

    def overdue7AndRepay(self,user_ids):
        '''逾期7天已还款数量 '''
        if len(user_ids) == 0:
            return 0
        userid_str ='"' + '","'.join(str(val) for val in user_ids) + '"'
        sql = '''
            SELECT
              count(1)
            FROM loan_overdue_loan
            WHERE user_id IN(%s)
                AND loan_status = 6
                AND modify_time > DATE_ADD(end_date,INTERVAL 7 DAY)
        ''' % userid_str
        count = db.session.execute(sql, bind=self.get_engine()).fetchone()
        return count[0]

    def getOverdueLoan(self,user_id):
        returnData = {
            'wst_dlq_sts':0,
            'mth3_dlq_num':0,
            'mth3_wst_sys':0,
            'mth3_dlq7_num':0,
            'mth6_dlq_ratio':0,
            'mth6_total_num':0,
            'mth6_dlq_num': 0
        }
        where = and_(
            ShopLoanOverdueLoan.user_id == user_id
        )
        fields = [
            ShopLoanOverdueLoan.end_date.label('end_date'),
            ShopLoanRepay.modify_time.label("repay_time"),
            ShopLoanUserLoan.create_time.label("create_time")
        ]
        datas = db.session.query(*fields).outerjoin(ShopLoanRepay, ShopLoanOverdueLoan.loan_id == ShopLoanRepay.loan_id).outerjoin(ShopLoanUserLoan, ShopLoanOverdueLoan.loan_id == ShopLoanUserLoan.loan_id).filter(where).all()
        if len(datas) == 0:
            return returnData
        columns = ['end_date','repay_time','create_time']
        pd_overdue_data = pd.DataFrame(datas,columns=columns)
        pd_overdue_data = pd_overdue_data.dropna()
        if len(pd_overdue_data) == 0:
            return returnData

        fuc_deal_time = lambda x: None if pd.isnull(x) else x.strftime('%Y-%m-%d')
        pd_overdue_data['repay_time'] =pd.to_datetime(pd_overdue_data['repay_time'],errors='ignore').apply(fuc_deal_time)
        pd_overdue_data['create_time'] = pd.to_datetime(pd_overdue_data['create_time'],errors='ignore').apply(fuc_deal_time)
        pd_overdue_data['end_date'] = pd.to_datetime(pd_overdue_data['end_date']).apply(fuc_deal_time)
        due_day_series = pd.to_datetime(pd_overdue_data['repay_time']) - pd.to_datetime(pd_overdue_data['end_date'])
        fuc_deal_day = lambda x: int(x.days)
        pd_overdue_data['due_day'] = due_day_series.apply(fuc_deal_day)
        returnData['wst_dlq_sts'] = int(pd_overdue_data['due_day'].max())
        nowday = datetime.now().strftime('%Y-%m-%d')
        interval_day_series = pd.to_datetime(nowday) - pd.to_datetime(pd_overdue_data['create_time'])
        pd_overdue_data['interval_day'] = interval_day_series.apply(fuc_deal_day)
        mth3_loan_num = pd_overdue_data['due_day'][pd_overdue_data['interval_day']<90].count()
        if mth3_loan_num > 0:
            returnData['mth3_dlq_num'] = pd_overdue_data['interval_day'][(pd_overdue_data['interval_day']<90) & (pd_overdue_data['due_day'] > 0)].count()
            returnData['mth3_wst_sys'] = pd_overdue_data['due_day'][pd_overdue_data['interval_day']<90].max()
            returnData['mth3_dlq7_num'] = pd_overdue_data['due_day'][(pd_overdue_data['interval_day']<90) & pd_overdue_data['due_day'] >= 7].count()
        mth6_total_num = pd_overdue_data['due_day'][(pd_overdue_data['interval_day']<180)].count()
        returnData['mth6_total_num'] = mth6_total_num
        if mth6_total_num > 0:
            mth6_dlq_num = pd_overdue_data['due_day'][(pd_overdue_data['interval_day']<180) & pd_overdue_data['due_day'] > 0].count()
            returnData['mth6_dlq_num'] = mth6_dlq_num
            returnData['mth6_dlq_ratio'] = float('%.2f' % (mth6_dlq_num / mth6_total_num))
        return returnData


    def getIsOverdue(self, user_id):
        if not user_id:
            return 0
        where = or_(
            ShopLoanRepay.status == 4,
            and_(
                ShopLoanOverdueLoan.loan_status == 7,
                ShopLoanOverdueLoan.user_id == user_id
            )
        )
        data = db.session.query(ShopLoanOverdueLoan.user_id).outerjoin(ShopLoanRepay, ShopLoanOverdueLoan.loan_id == ShopLoanRepay.loan_id).filter(where).limit(1).first()
        if data is None:
            return 0
        return 1