# -*- coding: utf-8 -*-
from lib.application import db
from sqlalchemy import desc, and_
from model.base_model import BaseModel
from datetime import datetime, timedelta

class AppBlackRoom(db.Model, BaseModel):
    # 指定数据库
    __bind_key__ = 'analysis_repertory'
    __tablename__ = 'app_black_room'

    # table model
    id = db.Column(db.Integer, primary_key=True)
    app_package = db.Column(db.String(100), index=True, nullable=False, server_default=db.FetchedValue())
    down_from = db.Column(db.SmallInteger, nullable=False, server_default=db.FetchedValue())
    over_time = db.Column(db.DateTime, index=True)

    def getBlackRoom(self, app_package, down_from):
        where = and_(
            AppBlackRoom.app_package == app_package,
            AppBlackRoom.down_from == down_from,
        )
        return db.session.query(AppBlackRoom).filter(where).order_by(desc('id')).limit(1).first()

    def putBlackRoom(self, app_package, down_from):
        oInfo = self.getBlackRoom(app_package, down_from)
        if oInfo is None:
            data = {}
            data['app_package'] = app_package
            data['down_from'] = down_from
            data['over_time'] = datetime.now() + timedelta(hours=+8)
            self.addByDict(data)
        else:
            oInfo['over_time'] = datetime.now() + timedelta(hours=+8)
            db.session.commit()

