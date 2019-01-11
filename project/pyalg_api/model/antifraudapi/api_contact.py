# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base


class ApiContact(db.Model, Base):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'api_contact'

    id = db.Column(db.BigInteger, primary_key=True)
    base_id = db.Column(db.BigInteger, nullable=False, index=True)
    com_r_total = db.Column(db.Integer)
    com_r_rank = db.Column(db.Integer)
    com_r_total_mavg = db.Column(db.Numeric(12, 2))
    com_r_duration = db.Column(db.Integer)
    com_r_duration_rank = db.Column(db.Integer)
    com_r_duration_mavg = db.Column(db.Numeric(12, 2))
    com_c_total = db.Column(db.Integer)
    com_c_rank = db.Column(db.Integer)
    com_c_total_mavg = db.Column(db.Numeric(12, 2))
    com_c_duration = db.Column(db.Integer)
    com_c_duration_rank = db.Column(db.Integer)
    com_c_duration_mavg = db.Column(db.Numeric(12, 2))
    com_r_overdue = db.Column(db.Integer)
    com_c_overdue = db.Column(db.Integer)
    create_time = db.Column(db.DateTime, nullable=False)
