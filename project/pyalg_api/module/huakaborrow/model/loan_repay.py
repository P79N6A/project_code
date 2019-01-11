# -*- coding: utf-8 -*-
# 注意这里使用了阿里云本地库的通讯录
#
from lib.application import db
from lib.logger import logger
from model.base_model import BaseModel
from sqlalchemy import and_


class LoanRepay(db.Model, BaseModel):
    __bind_key__ = 'spark'
    __tablename__ = 'loan_repay'

    id = db.Column(db.BigInteger, primary_key=True)
    order_id = db.Column(db.String(64))
    bank_id = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    user_id = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    loan_id = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    status = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    error_code = db.Column(db.String(32))
    error_msg = db.Column(db.String(50))
    money =  db.Column(db.Numeric(10, 2), nullable=False, server_default=db.FetchedValue())
    actual_money =  db.Column(db.Numeric(10, 2), nullable=False, server_default=db.FetchedValue())
    paybill = db.Column(db.String(64))
    platform = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    come_from = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    coupon_amount =  db.Column(db.Numeric(10, 2), nullable=False, server_default=db.FetchedValue())
    create_time = db.Column(db.DateTime)
    modify_time = db.Column(db.DateTime)
    version = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())


    def advanceRepay(self,user_ids):
        '''通讯录借款提前/正常还款 '''
        if len(user_ids) == 0:
            return 0
        userid_str = '"' + '","'.join(str(val) for val in user_ids) + '"'

        sql = "select count(DISTINCT(repay.loan_id)) from loan_repay as repay left join loan_user_loan as loan on(repay.loan_id=loan.loan_id) where repay.user_id in(%s) and repay.status=6 and repay.modify_time < loan.end_date" % userid_str
        #logger.info("aaaaa=%s" % sql)
        count = db.session.execute(sql, bind=self.get_engine()).fetchone()
        all_overdue = count[0]
        return all_overdue


    def getSuccessNum(self, user_id):
        if not user_id:
            return 0
        sql = 'select count(1) as success_num  from loan_repay lr inner join loan_user_loan ul on ul.loan_id = lr.loan_id where lr.user_id = "%s"  and lr.status =6 and ul.number= 0 ' %(user_id)
        count = db.session.execute(sql, bind=self.get_engine()).fetchone()
        return count[0]