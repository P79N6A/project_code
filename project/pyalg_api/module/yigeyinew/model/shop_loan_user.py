# -*- coding: utf-8 -*-

from lib.application import db
from model.base_model import BaseModel
from sqlalchemy import and_
from sqlalchemy.sql import func
from lib.logger import logger
from sqlalchemy import MetaData
import os

class ShopLoanUser(db.Model, BaseModel):
    __bind_key__ = 'xhh_yigeyi'
    __tablename__ = 'loan_user'
    metadata = MetaData()

    user_id = db.Column(db.BigInteger, primary_key=True)
    user_no = db.Column(db.String(32))
    mobile = db.Column(db.String(12))
    invite_code =db.Column(db.String(32))
    from_code = db.Column(db.String(32))
    head_img = db.Column(db.String(128))
    realname = db.Column(db.String(32))
    identity = db.Column(db.String(20))
    sex = db.Column(db.Integer, server_default=db.FetchedValue())
    email = db.Column(db.String(64))
    marriage = db.Column(db.Integer, server_default=db.FetchedValue())
    status = db.Column(db.Integer, server_default=db.FetchedValue())
    identity_valid = db.Column(db.Integer, server_default=db.FetchedValue())
    come_from = db.Column(db.Integer, server_default=db.FetchedValue())
    last_login_time = db.Column(db.DateTime)
    last_login_position = db.Column(db.Integer, server_default=db.FetchedValue())
    create_time = db.Column(db.DateTime)
    modify_time = db.Column(db.DateTime)
    version = db.Column(db.Integer, server_default=db.FetchedValue())


    def getUidsByMobiles(self,mobiles):
        if len(mobiles) == 0:
            return []
        oUsers = db.session.query(ShopLoanUser.user_id.label('user_id')).filter(ShopLoanUser.mobile.in_(mobiles)).limit(1).all()
        if oUsers :
            return [i.user_id for i in oUsers]
        else :
            return []


    def getUidCounts(self,mobiles):
        if len(mobiles) == 0:
            return 0
        counts = db.session.query(ShopLoanUser).filter(ShopLoanUser.mobile.in_(mobiles)).limit(1000).count()
        return counts

    def getByIdentity(self, identity):
        db_user = db.session.query(ShopLoanUser).filter(ShopLoanUser.identity == identity).first()
        return db_user

    def getByMobile(self, mobile):
        db_user = db.session.query(ShopLoanUser).filter(ShopLoanUser.mobile == mobile).limit(1).first()
        return db_user