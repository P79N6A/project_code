# -*- coding: utf-8 -*-
from lib.application import db
from lib.logger import logger
from .base import Base
from datetime import datetime
from sqlalchemy import desc,and_,or_

class ApiDetailTag(db.Model, Base):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'api_detail_tag'

    id = db.Column(db.BigInteger, primary_key=True)
    base_id = db.Column(db.BigInteger)
    detail_saynum = db.Column(db.Integer)
    detail_telnum = db.Column(db.Integer)
    advertis = db.Column(db.String(255), server_default='')
    express = db.Column(db.String(255), server_default='')
    harass = db.Column(db.String(255), server_default='')
    house_agent = db.Column(db.String(255), server_default='')
    cheat = db.Column(db.String(255), server_default='')
    company_tel = db.Column(db.String(255), server_default='')
    invite = db.Column(db.String(255), server_default='')
    taxi = db.Column(db.String(255), server_default='')
    education = db.Column(db.String(255), server_default='')
    insurance = db.Column(db.String(255), server_default='')
    ring = db.Column(db.String(255), server_default='')
    service_tel = db.Column(db.String(255), server_default='')
    delinquency = db.Column(db.String(255), server_default='')
    create_time = db.Column(db.DateTime)

