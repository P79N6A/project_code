# -*- coding: utf-8 -*-
# 注意这里使用了阿里云本地库的通讯录
#
from lib.application import db
from model.base_model import BaseModel
from sqlalchemy import MetaData

class ShopLoanUserLoanFlows(db.Model, BaseModel):
    __bind_key__ = 'loan_shop'
    __tablename__ = 'loan_user_loan_flows'
    metadata = MetaData()

    id = db.Column(db.Integer, primary_key=True)
    loan_id = db.Column(db.Integer, nullable=False)
    admin_id = db.Column(db.Integer, nullable=False)
    loan_status = db.Column(db.Integer, nullable=False)
    relative = db.Column(db.String(1024))
    reason = db.Column(db.String(1024))
    create_time = db.Column(db.DateTime)
    modify_time = db.Column(db.DateTime)
    version = db.Column(db.Integer, nullable=False)
