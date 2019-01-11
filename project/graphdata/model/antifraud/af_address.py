# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base


class AfAddress(db.Model, Base):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_address'

    id = db.Column(db.BigInteger, primary_key=True)
    request_id = db.Column(db.BigInteger, nullable=False, index=True, server_default=db.FetchedValue())
    aid = db.Column(db.Integer)
    user_id = db.Column(db.BigInteger, nullable=False, index=True, server_default=db.FetchedValue())
    addr_count = db.Column(db.Integer)
    addr_parents_count = db.Column(db.Integer)
    addr_phones_nodups = db.Column(db.Integer)
    addr_phones_dups = db.Column(db.Integer)
    addr_collection_count = db.Column(db.Integer)
    addr_loan_count = db.Column(db.Integer)
    addr_gamble_count = db.Column(db.Integer)
    addr_father_count = db.Column(db.Integer)
    addr_mother_count = db.Column(db.Integer)
    addr_colleague_count = db.Column(db.Integer)
    addr_company_count = db.Column(db.Integer)
    addr_name_invalids = db.Column(db.Integer)
    addr_myphone_count = db.Column(db.Integer)
    addr_tel_count = db.Column(db.Integer)
    addr_relative_count = db.Column(db.Integer)
    addr_contacts_count = db.Column(db.Integer)
    create_time = db.Column(db.DateTime, nullable=False)
