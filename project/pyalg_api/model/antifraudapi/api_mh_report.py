# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base


class ApiMhReport(db.Model, Base):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'api_mh_report'

    id = db.Column(db.BigInteger, primary_key=True)
    base_id = db.Column(db.BigInteger, nullable=False, index=True)
    credit_point = db.Column(db.String(64))
    account_balance = db.Column(db.String(64))
    credit_level = db.Column(db.String(64))
    pay_count = db.Column(db.String(64))
    pay_sum_fee = db.Column(db.String(64))
    pay_mean_fee = db.Column(db.String(64))
    pay_max_fee = db.Column(db.String(64))
    bill_month_mean = db.Column(db.String(64))
    bill_month_max = db.Column(db.String(64))
    sms_count_fee = db.Column(db.String(64))
    sms_total_fee = db.Column(db.String(64))
    sms_phone_count_nodup = db.Column(db.String(64))
    sms_max_count_day = db.Column(db.String(64))
    net_count_4g = db.Column(db.String(64))
    net_count_type = db.Column(db.String(64))
    call_count_call_time_1min5min = db.Column(db.String(64))
    call_count_call_time_5min10min = db.Column(db.String(64))
    call_duration_holiday_3month_t_7 = db.Column(db.String(64))
    call_duration_workday_3month_t_7 = db.Column(db.String(64))
    call_time_late_night_3month = db.Column(db.String(64))
    call_time_late_night_6month = db.Column(db.String(64))
    call_time_work_time_3month = db.Column(db.String(64))
    call_time_work_time_6month = db.Column(db.String(64))
    create_time = db.Column(db.DateTime, nullable=False)
