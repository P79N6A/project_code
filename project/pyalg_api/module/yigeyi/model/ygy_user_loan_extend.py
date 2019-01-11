# -*- coding: utf-8 -*-
# sqlacodegen  mysql://root:123!@#@127.0.0.1/xhh_test --outfile yyy.py --flask
from lib.application import db
from .base_model import BaseModel
from sqlalchemy import and_
from sqlalchemy.sql import func

from sqlalchemy import MetaData

class YgyUserLoanExtend(db.Model, BaseModel):
    __bind_key__ = 'xhh_yigeyi'
    __tablename__ = 'yi_user_loan_extend'
    metadata = MetaData()

    id = db.Column(db.BigInteger, primary_key=True)
    user_id = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    loan_id = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    uuid = db.Column(db.String(55))
    outmoney = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    payment_channel = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    userIp = db.Column(db.String(64))
    extend_type = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    success_num = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    last_modify_time = db.Column(db.DateTime)
    create_time = db.Column(db.DateTime)
    fund = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    status = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    version = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    loan_total = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    loan_success = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    loan_quota = db.Column(db.Numeric(12, 2), nullable=False)


    def getSuccessNum(self, user_id):
        if user_id is None:
            return 0
        where = and_(
            YgyUserLoanExtend.user_id == user_id,
            YgyUserLoanExtend.status == "SUCCESS"
        )
        res = db.session.query(YgyUserLoanExtend).filter(where).count()
        return res
