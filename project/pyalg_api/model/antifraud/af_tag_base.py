# -*- coding: utf-8 -*-
from lib.application import db
from lib.logger import logger
import os
from .base import Base
from datetime import datetime, timedelta
from sqlalchemy import and_

class AfTagBase(db.Model, Base):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_tag_base'

    id = db.Column(db.BigInteger, primary_key=True)
    aid = db.Column(db.Integer,nullable=False)
    base_id = db.Column(db.BigInteger, nullable=False, index=True)
    user_id = db.Column(db.BigInteger, nullable=False, index=True)
    phone = db.Column(db.String(32), nullable=False, index=True)
    tag_status = db.Column(db.Integer, nullable=False)
    create_time = db.Column(db.DateTime, nullable=False, index=True)
    modify_time = db.Column(db.DateTime, nullable=False)

    TAG_STATUS_OVER = 0 #状态完成

    def getById(self, id):
        db_base = self.query.get(int(id))
        return db_base

    def save(self):
        db.session.add(self)
        try:
            db.session.flush()
            db.session.commit()
        except Exception:
            db.session.rollback()
            raise

    '''
    获取数据200条
    '''
    def getTabData(self):
        now = datetime.now()
        now = now + timedelta(hours=-1)
        current_date = now.strftime('%Y-%m-%d %H:%M:%S')
        #current_date = now.strftime('%Y-%m-%d %H:00:00')
        result = db.session.query(AfTagBase).\
            filter(AfTagBase.tag_status == self.TAG_STATUS_OVER, AfTagBase.create_time >= current_date).\
            order_by(AfTagBase.create_time).\
            limit(200).\
            all()
        return result

    '''
    锁定数据或是完成数据
    '''
    def lockTagStatus(self, data, status):
        if len(data) == 0:
            return False
        now = datetime.now()
        for o in data:
            o.modify_time = datetime.now()
            o.tag_status = status  # 锁定1 | 完成2
        db.session.commit()

        return True

    def lockTagSuccess(self, data):
        if not data:
            return False

        data.modify_time = datetime.now()
        data.tag_status = 6  # 锁定1 | 完成2
        db.session.commit()

        return True

    '''
    通过手机号查找用户信息
    '''
    def getDataByPhone(self, phone):
        if phone is None:
            return False
        return db.session.query(AfTagBase). \
            filter(AfTagBase.phone == phone). \
            order_by(AfTagBase.create_time.desc()). \
            first()


