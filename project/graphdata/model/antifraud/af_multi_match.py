# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base

class AfMultiMatch(db.Model, Base):
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_multi_match'

    id = db.Column(db.BigInteger, primary_key=True)
    request_id = db.Column(db.BigInteger, nullable=False,index=True)
    aid = db.Column(db.Integer,nullable=False)
    user_id = db.Column(db.Integer, nullable=False, index=True)
    relation_ship = db.Column(db.Text)
    create_time = db.Column(db.DateTime, nullable=False)
