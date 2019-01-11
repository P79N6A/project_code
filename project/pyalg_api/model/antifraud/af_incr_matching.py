# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base
from datetime import datetime

class AfIncrMatching(db.Model, Base):
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_incr_matching'

    id = db.Column(db.BigInteger, primary_key=True)
    base_id = db.Column(db.BigInteger, nullable=False, server_default="0")
    user_id = db.Column(db.BigInteger, nullable=False, server_default="0")
    proportion = db.Column(db.DECIMAL(10, 4), nullable=False, server_default="0.0000")
    status = db.Column(db.SmallInteger, nullable=False)
    create_time = db.Column(db.DateTime, nullable=False)
    modify_time = db.Column(db.DateTime, nullable=False)

    '''
    通过user_id获取信息
    '''
    def getOne(self, base_id):
        if not base_id:
            return False
        info = db.session.query(AfIncrMatching).filter(AfIncrMatching.base_id==base_id).first()
        return info

    '''
    修改数据
    '''
    def updateOne(self, tag_list, proportion):
        info = self.getOne(tag_list.base_id)
        if not info:
            return False

        info.user_id = tag_list.user_id
        info.proportion = proportion
        info.modify_time = datetime.now()
        db.session.commit()
        return True

    '''
    保存数据
    '''
    def saveData(self, tag_list, proportion, status):
        self.base_id = tag_list.base_id
        self.user_id = tag_list.user_id
        self.proportion = proportion
        self.status = status
        self.create_time = datetime.now()
        self.modify_time = datetime.now()
        db.session.add(self)
        db.session.commit()
        return True
