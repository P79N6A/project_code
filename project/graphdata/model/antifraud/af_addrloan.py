# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base

class AfAddrloan(db.Model, Base):
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_addr_loan'

    id = db.Column(db.BigInteger, primary_key=True)
    request_id = db.Column(db.BigInteger, nullable=False,index=True)
    aid = db.Column(db.Integer)
    user_id = db.Column(db.BigInteger, nullable=False,index=True)
    user_total = db.Column(db.Integer, nullable=False)
    loan_all = db.Column(db.Integer, nullable=False)
    overdue_norepay = db.Column(db.Integer, nullable=False)
    overdue_repay = db.Column(db.Integer, nullable=False)
    overdue7_norepay = db.Column(db.Integer, nullable=False)
    overdue7_repay = db.Column(db.Integer, nullable=False)
    loan_total = db.Column(db.Integer, nullable=False)
    last_loan_day = db.Column(db.Integer, nullable=False)
    normal_repay = db.Column(db.Integer, nullable=False)
    realadl_tot_reject_num = db.Column(db.Integer, nullable=True)
    realadl_tot_freject_num = db.Column(db.Integer, nullable=True)
    realadl_tot_sreject_num = db.Column(db.Integer, nullable=True)
    realadl_tot_dlq14_num = db.Column(db.Integer, nullable=True)
    realadl_dlq14_ratio = db.Column(db.Numeric(12, 2),nullable=True)
    history_bad_status = db.Column(db.Integer, nullable=True)
    create_time = db.Column(db.DateTime, nullable=False)
