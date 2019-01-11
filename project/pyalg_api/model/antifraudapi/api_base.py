# -*- coding: utf-8 -*-
import os
from lib.application import db
from lib.logger import logger
from .base import Base
from datetime import datetime, timedelta
from sqlalchemy import and_

class ApiBase(db.Model, Base):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'api_base'

    id = db.Column(db.BigInteger, primary_key=True)
    credit_id = db.Column(db.BigInteger, nullable=False, index=True)
    aid = db.Column(db.Integer,nullable=False)
    contain = db.Column(db.Integer,nullable=False)
    jxlstat_id = db.Column(db.Integer)
    report_type = db.Column(db.Integer)
    realname = db.Column(db.String, nullable=False)
    mobile = db.Column(db.String, nullable=False, index=True)
    identity = db.Column(db.String, nullable=False, index=True)
    contact = db.Column(db.String, nullable=False)
    status = db.Column(db.Integer, nullable=False)
    create_time = db.Column(db.DateTime, nullable=False)
    modify_time = db.Column(db.DateTime, nullable=False)

    def save(self):
        db.session.add(self)
        try:
            db.session.flush()
            db.session.commit()
            db.session.refresh(self)
            return self.id
        except Exception as e:
            db.session.rollback()
            return 0

    def changeStatus(self,status):
        self.status = status
        self.modify_time = datetime.now()
        db.session.commit()
        return True

    def changeFail(self):
        self.status = self.status + 100
        self.modify_time = datetime.now()
        db.session.commit()
        return True
