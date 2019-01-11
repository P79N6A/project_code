# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base


class AfSsReport(db.Model, Base):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_ss_report'

    id = db.Column(db.BigInteger, primary_key=True)
    request_id = db.Column(db.BigInteger, nullable=False, index=True, server_default=db.FetchedValue())
    aid = db.Column(db.Integer)
    user_id = db.Column(db.BigInteger, nullable=False, index=True, server_default=db.FetchedValue())
    score = db.Column(db.Integer,nullable=True) 
    rain_risk_reason = db.Column(db.Text,nullable=True)
    rain_score = db.Column(db.String,nullable=True)
    consume_fund_index = db.Column(db.String,nullable=True)
    indentity_risk_index = db.Column(db.String,nullable=True)
    social_stability_index = db.Column(db.String,nullable=True)
    phone_register_month = db.Column(db.Integer,nullable=True)
    create_time = db.Column(db.DateTime, nullable=False)
