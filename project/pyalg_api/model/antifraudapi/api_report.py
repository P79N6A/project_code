# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base

class ApiReport(db.Model, Base):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'api_report'

    id = db.Column(db.BigInteger, primary_key=True)
    base_id = db.Column(db.BigInteger)
    report_aomen = db.Column(db.Integer)
    report_110 = db.Column(db.Integer)
    report_120 = db.Column(db.Integer)
    report_lawyer = db.Column(db.Integer)
    report_court = db.Column(db.Integer)
    report_use_time = db.Column(db.Integer)
    report_shutdown = db.Column(db.Integer)
    report_name_match = db.Column(db.Integer)
    report_fcblack_idcard = db.Column(db.Integer)
    report_fcblack_phone = db.Column(db.Integer)
    report_fcblack = db.Column(db.Integer)
    report_operator_name = db.Column(db.String(20))
    report_reliability = db.Column(db.Integer)
    report_night_percent = db.Column(db.Numeric(10, 2))
    report_loan_connect = db.Column(db.String(50))
    create_time = db.Column(db.DateTime, nullable=False)
