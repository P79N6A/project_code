# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base

class AfRelationMatch(db.Model, Base):
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_relation_match'

    id = db.Column(db.BigInteger, primary_key=True)
    request_id = db.Column(db.BigInteger, nullable=False,index=True)
    aid = db.Column(db.Integer,nullable=False)
    user_id = db.Column(db.BigInteger, nullable=False, index=True)
    avg_jaccard_phone_no4 = db.Column(db.Numeric(12, 4))
    p_num_android = db.Column(db.Numeric(12, 4))
    yiqi_7_p = db.Column(db.Numeric(12, 4))
    ref_model_people_pt = db.Column(db.Numeric(12, 4))
    report_shutdown_1_no4 = db.Column(db.Numeric(12, 4))
    jaccard_all_more1_no4 = db.Column(db.Numeric(12, 4))
    jaccard_all_max_no4 = db.Column(db.Numeric(12, 4))
    num_android_no4 = db.Column(db.Integer)
    vs_connect_match_1_no4 = db.Column(db.Numeric(12, 4))
    create_time = db.Column(db.DateTime, nullable=False)
