# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base

class ApiDetailOther(db.Model, Base):
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'api_detail_other'

    id = db.Column(db.BigInteger, primary_key=True)
    base_id = db.Column(db.BigInteger,index=True)
    last3_answer = db.Column(db.Integer)
    last3_all = db.Column(db.Integer)
    last6_answer = db.Column(db.Integer)
    last6_all = db.Column(db.Integer)
    same_phone_num = db.Column(db.Integer)
    phone_register_month = db.Column(db.Integer)
    total_duration = db.Column(db.BigInteger)
    tot_phone_num = db.Column(db.Integer)
    last3_not_mobile_count = db.Column(db.Integer)
    last6_not_mobile_count = db.Column(db.Integer)
    shutdown_duration_count = db.Column(db.Integer)
    shutdown_sum_days = db.Column(db.Integer)
    shutdown_max_days = db.Column(db.Integer)
    shutdown_min_days = db.Column(db.Integer)
    shutdown_median_days = db.Column(db.Integer)
    shutdown_mode_days = db.Column(db.Integer)
    retain_ratio = db.Column(db.String(32))
    last_3mth_Oth_ratio = db.Column(db.String(32))
    last_3mth_oth_incr = db.Column(db.String(32))
    create_time = db.Column(db.DateTime, nullable=False)
