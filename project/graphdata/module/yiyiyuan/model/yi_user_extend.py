# -*- coding: utf-8 -*-
# sqlacodegen  mysql://root:123!@#@127.0.0.1/xhh_test --outfile yyy.py --flask
from lib.application import db
from .base_model import BaseModel
from sqlalchemy import and_
from sqlalchemy.sql import func
from lib.logger import logger
import os

class YiUserExtend(db.Model, BaseModel):
    __bind_key__ = 'xhh_yiyiyuan'
    __tablename__ = 'yi_user_extend'

    id = db.Column(db.BigInteger, primary_key=True)
    user_id = db.Column(db.BigInteger, nullable=False)
    uuid = db.Column(db.String(32))
    school_valid = db.Column(db.Integer, server_default=db.FetchedValue())
    school_id = db.Column(db.Integer, server_default=db.FetchedValue())
    school = db.Column(db.String(64))
    edu = db.Column(db.String(64))
    school_time = db.Column(db.String(64))
    industry = db.Column(db.Integer)
    company = db.Column(db.String(128))
    position = db.Column(db.String(128))
    profession = db.Column(db.String(128))
    telephone = db.Column(db.String(32))
    marriage = db.Column(db.Integer)
    email = db.Column(db.String(32))
    income = db.Column(db.String(32))
    home_area = db.Column(db.Integer)
    home_address = db.Column(db.String(128))
    company_area = db.Column(db.Integer)
    company_address = db.Column(db.String(128))
    version = db.Column(db.Integer)
    is_new = db.Column(db.Integer, server_default=db.FetchedValue())
    is_callback = db.Column(db.Integer, server_default=db.FetchedValue())
    reg_ip = db.Column(db.String(16))
    last_modify_time = db.Column(db.DateTime)
    create_time = db.Column(db.DateTime)


    '''
    查找用户信息
    '''
    def getUserIp(self, user_id):
        if not user_id:
            return False
        return db.session.query(YiUserExtend).filter(YiUserExtend.user_id == user_id).limit(1).first()


    '''
    计算ip下有多少用户
    '''
    def getIpCount(self, ip_str):
        if not ip_str:
            return False
        return db.session.query(YiUserExtend).filter(YiUserExtend.reg_ip == ip_str).count()


    """
    通过ip下所有数据
    """
    def getIpData(self, reg_ip, offset, limit_num=100):
        res = db.session.query(YiUserExtend).filter(YiUserExtend.reg_ip == reg_ip).order_by(YiUserExtend.user_id.asc()).offset(offset).limit(limit_num).all()

        return res