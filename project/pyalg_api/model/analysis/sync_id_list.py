# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base
from sqlalchemy import and_,desc
from datetime import datetime, timedelta
import os


class SyncIdList(db.Model, Base):
    __bind_key__ = 'analysis_repertory'
    __tablename__ = 'sync_id_list'
    # class attr
    STATUS_INIT = 0
    STATUS_DOING = 1
    STATUS_SUCCESS = 2
    SYNC_DETAIL = 'sync_detail'
    SYNC_APP = 'sync_app'
    SYNC_MSG = 'sync_msg'


    # table model
    id = db.Column(db.BigInteger, primary_key=True)
    start_id = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    end_id = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    sync_status = db.Column(db.Integer, index=True, nullable=False, server_default=db.FetchedValue())
    sync_type = db.Column(db.String(32),index=True, nullable=False, server_default=db.FetchedValue())
    create_time = db.Column(db.DateTime, index=True)
    modify_time = db.Column(db.DateTime, index=True)

    def get(self, user_id):
        return self.query.get(user_id)

    def getInitSyncId(self, sync_type):
        where = and_(
            SyncIdList.sync_status == self.STATUS_INIT,
            SyncIdList.sync_type == sync_type,
        )
        db_sync = db.session.query(SyncIdList).filter(where).order_by(desc('id')).limit(1).first()
        return db_sync

    def lockStatus(self, status):
        self.sync_status = status
        self.modify_time = datetime.now()
        db.session.commit()
