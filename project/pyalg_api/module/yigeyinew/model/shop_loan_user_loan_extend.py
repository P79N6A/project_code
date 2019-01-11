# -*- coding: utf-8 -*-
# sqlacodegen  mysql://root:123!@#@127.0.0.1/xhh_test --outfile yyy.py --flask
from lib.application import db
from .base_model import BaseModel
from sqlalchemy import and_,desc
from sqlalchemy.sql import func

from datetime import datetime,timedelta
from sqlalchemy import MetaData

class ShopLoanUserLoanExtend(db.Model, BaseModel):
    __bind_key__ = 'xhh_yigeyi'
    __tablename__ = 'loan_user_loan_extend'
    metadata = MetaData()

    id = db.Column(db.BigInteger, primary_key=True)
    loan_id = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    user_id = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    device_tokens = db.Column(db.String(64))
    device_type = db.Column(db.String(20))
    is_outmoney = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    user_ip = db.Column(db.String(16))
    loan_total = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    loan_success = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    modify_time = db.Column(db.DateTime)
    create_time = db.Column(db.DateTime)
    version = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())


    def getSuccessNum(self, user_id):
        if user_id is None:
            return 0
        where = and_(
            ShopLoanUserLoanExtend.user_id == user_id,
        )
        res = db.session.query(ShopLoanUserLoanExtend).filter(where).order_by(desc(ShopLoanUserLoanExtend.modify_time)).first()
        return res
