# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base
from sqlalchemy import desc, and_
import os

class UserAppSnapshot(db.Model, Base):
    __bind_key__ = 'analysis_repertory'
    __tablename__ = 'user_app_snapshot'

    # table model
    id = db.Column(db.BigInteger, primary_key=True)
    mobile = db.Column(db.String(32), index=True, nullable=False, server_default=db.FetchedValue())
    app_list = db.Column(db.Text, nullable=False)
    create_time = db.Column(db.DateTime, nullable=False)

    def getAppList(self, mobile):
        if len(mobile) == 0:
            return None
        return db.session.query(UserAppSnapshot.app_list).filter(UserAppSnapshot.mobile == mobile).order_by(desc('id')).limit(1).first()

    def saveAppList(self, mobile, time, app_list):
        if len(mobile) == 0 or len(time) == 0 or len(app_list) == 0:
            return False
        data = {}
        data['mobile'] = mobile
        data['app_list'] = app_list
        data['create_time'] = time
        self.addByDict(data)

    def getAppListWithTime(self, mobile, time):
        if mobile is None or time is None or len(mobile) != 11 or len(time) != 19:
            return []

        where = and_(
            UserAppSnapshot.mobile == str(mobile),
            UserAppSnapshot.create_time <= time
        )
        appList = db.session.query(UserAppSnapshot.app_list).filter(where).order_by(desc('id')).limit(1).first()
        if appList is None or len(appList) < 0:
            return []

        return appList.app_list
