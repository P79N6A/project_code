# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base

class AfDetailOther(db.Model, Base):
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_detail_other'

    id = db.Column(db.BigInteger, primary_key=True)
    request_id = db.Column(db.BigInteger, nullable=False,index=True)
    aid = db.Column(db.Integer)
    user_id = db.Column(db.Integer, nullable=False, index=True)
    last3_answer = db.Column(db.Integer, nullable=True)
    last3_all = db.Column(db.Integer, nullable=True)
    last6_answer = db.Column(db.Integer, nullable=True)
    last6_all = db.Column(db.Integer, nullable=True)
    same_phone_num = db.Column(db.Integer, nullable=True)
    phone_register_month = db.Column(db.Integer, nullable=True)
    total_duration = db.Column(db.BigInteger, nullable=True)
    tot_phone_num = db.Column(db.Integer, nullable=True)
    last3_not_mobile_count = db.Column(db.Integer, nullable=True)
    last6_not_mobile_count = db.Column(db.Integer, nullable=True)
    shutdown_duration_count = db.Column(db.Integer)
    shutdown_sum_days = db.Column(db.Integer)
    shutdown_max_days = db.Column(db.Integer)
    shutdown_min_days = db.Column(db.Integer)
    shutdown_median_days = db.Column(db.Integer)
    shutdown_mode_days = db.Column(db.Integer)
    create_time = db.Column(db.DateTime, nullable=False)
