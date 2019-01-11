# -*- coding: utf-8 -*-
from lib.application import db
from model.base_model import BaseModel
from sqlalchemy import and_
from sqlalchemy.sql import func
import os

class YiApplicationList(db.Model, BaseModel):
    __bind_key__ = 'xhh_yiyiyuan'
    __tablename__ = 'yi_application_list'

    id = db.Column(db.BigInteger, primary_key=True)
    user_id = db.Column(db.BigInteger, index=True)
    content = db.Column(db.Text, nullable=True)
    create_time = db.Column(db.DateTime, index=True)
    last_modify_time = db.Column(db.DateTime, index=True)
    type = db.Column(db.Integer)
    version = db.Column(db.Integer)

    def getDateByUserIds(self, start, end, type):
        where = and_(
            YiApplicationList.id >= start,
            YiApplicationList.id < end,
            YiApplicationList.type == type
        )
        oApp = db.session.query(YiApplicationList.user_id,YiApplicationList.content,YiApplicationList.last_modify_time).filter(where).limit(1000).all()
        return oApp

    def get_maxid(self):
        maxid = db.session.query(func.max(YiApplicationList.id)).scalar()
        return maxid
