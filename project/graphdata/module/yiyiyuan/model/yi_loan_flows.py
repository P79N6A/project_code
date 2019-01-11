# -*- coding: utf-8 -*-
# 注意这里使用了阿里云本地库的通讯录
# sqlacodegen  mysql://root:123!@#@127.0.0.1/xhh_test --outfile yyy.py --flask
from lib.application import db
from .base_model import BaseModel

class YiUserLoanFlow(db.Model, BaseModel):
    __bind_key__ = 'own_yiyiyuan'
    __tablename__ = 'yi_user_loan_flows'

    id = db.Column(db.Integer, primary_key=True)
    loan_id = db.Column(db.Integer, nullable=False)
    admin_id = db.Column(db.Integer, nullable=False)
    loan_status = db.Column(db.Integer)
    relative = db.Column(db.String(1024))
    reason = db.Column(db.String(1024))
    create_time = db.Column(db.DateTime)
    admin_name = db.Column(db.String(64))
    type = db.Column(db.Integer, server_default=db.FetchedValue())