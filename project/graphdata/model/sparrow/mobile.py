# -*- coding: utf-8 -*-
from lib.application import db
from model.base_model import BaseModel


class Mobile(db.Model, BaseModel):
    __bind_key__ = 'sparrow'
    __tablename__ = 'mobile'

    id = db.Column(db.BigInteger, primary_key=True)
    aid = db.Column(db.Integer)
    user_id = db.Column(db.BigInteger, index=True)
    mobile = db.Column(db.String(20), index=True)
    realname = db.Column(db.String(32))
    identity = db.Column(db.String(20))
    create_time = db.Column(db.DateTime)
   
    def getUidsByMobiles(self,mobiles):
        if len(mobiles) == 0:
            return []
        oUsers = db.session.query(Mobile.mobile).filter(Mobile.mobile.in_(mobiles)).limit(1000).all()
        if oUsers :
            return [i.mobile for i in oUsers]
        else :
            return []
            
