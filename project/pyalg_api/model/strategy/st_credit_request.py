# -*- coding: utf-8 -*-
# 评测数据表

from lib.application import db
from model.base_model import BaseModel

class CreditRequest(db.Model, BaseModel):
    __bind_key__ = 'xhh_strategy'
    __tablename__ = 'st_credit_request'

    credit_id = db.Column(db.BigInteger, primary_key=True)
    st_req_id = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    mobile = db.Column(db.String(12), index=True)
    aid = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    create_time = db.Column(db.DateTime, nullable=False, index=True)
    come_from = db.Column(db.Integer, nullable=True, server_default=db.FetchedValue())
    modify_time = db.Column(db.DateTime, nullable=False)
    credit_data = db.Column(db.TEXT)

    def getByStReqId(self, st_req_id):
        oCredit = db.session.query(CreditRequest.credit_data).filter(CreditRequest.st_req_id == st_req_id).limit(1).first()
        return oCredit
