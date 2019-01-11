# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base


class ApiOtherData(db.Model, Base):
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'api_other_data'

    id = db.Column(db.BigInteger, primary_key=True)
    base_id = db.Column(db.BigInteger, nullable=False, server_default='0')
    learning_letter = db.Column(db.Integer, nullable=False, server_default='0')
    learning_letter_contrast = db.Column(db.String(32), nullable=False, server_default='')
    ocial_security = db.Column(db.Integer, nullable=False, server_default='0')
    ocial_security_contrast = db.Column(db.String(32), nullable=False, server_default='')
    accumulation_fund = db.Column(db.Integer, nullable=False, server_default='0')
    accumulation_fund_contrast = db.Column(db.String(32), nullable=False, server_default='')
    max_account_detail_balance = db.Column(db.String(32), nullable=False, server_default='')
    create_time = db.Column(db.DateTime, nullable=False)
