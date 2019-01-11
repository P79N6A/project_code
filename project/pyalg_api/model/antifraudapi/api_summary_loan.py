# -*- coding: utf-8 -*-
# 通讯录vsloan后的summaryData

from lib.application import db
from .base import Base

class ApiSummaryLoan(db.Model, Base):
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'api_summary_loan'

    id = db.Column(db.BigInteger, primary_key=True)
    base_id = db.Column(db.BigInteger, nullable=False, index=True)
    mth3_dlq_num = db.Column(db.Integer)
    mth3_dlq7_num = db.Column(db.Integer)
    mth3_wst_sys = db.Column(db.Integer)
    wst_dlq_sts = db.Column(db.Integer)
    mth6_dlq_num = db.Column(db.Integer)
    mth6_total_num = db.Column(db.Integer)
    mth6_dlq_ratio = db.Column(db.Numeric(12, 2))
    success_num = db.Column(db.Integer)
    user_loan_total = db.Column(db.Integer)
    type = db.Column(db.Integer)
    last_success_loan_days = db.Column(db.Integer)
    realadl_dlq14_ratio = db.Column(db.Numeric(12, 2))
    realadl_tot_dlq14_num = db.Column(db.Integer)
    realadl_tot_freject_num = db.Column(db.Integer)
    realadl_tot_reject_num = db.Column(db.Integer)
    realadl_tot_sreject_num = db.Column(db.Integer)
    realadl_wst_dlq_sts = db.Column(db.Integer)
    com_c_user = db.Column(db.Integer)
    history_bad_status = db.Column(db.Integer)
    loan_all = db.Column(db.Integer)
    overdue_norepay = db.Column(db.Integer)
    overdue_repay = db.Column(db.Integer)
    overdue7_norepay = db.Column(db.Integer)
    overdue7_repay = db.Column(db.Integer)
    loan_total = db.Column(db.Integer)
    last_loan_day = db.Column(db.DateTime)
    normal_repay = db.Column(db.Integer)
    is_overdue = db.Column(db.Integer)
    is_loaning = db.Column(db.Integer)
    create_time = db.Column(db.DateTime)
