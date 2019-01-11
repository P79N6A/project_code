# -*- coding: utf-8 -*-
from lib.application import db
from model.base_model import BaseModel
from datetime import datetime, timedelta
import json
import urllib.request
import urllib.error
import urllib.parse
import socket
from lib.logger import logger
from sqlalchemy import desc, and_


class GatherResult(db.Model, BaseModel):
    # 指定数据库
    __bind_key__ = 'xhh_open'
    __tablename__ = 'xhh_gather_result'

    id = db.Column(db.Integer, primary_key=True)
    aid = db.Column(db.Integer, nullable=False, server_default='0')
    source = db.Column(db.Integer, nullable=False, server_default='0')
    request_id = db.Column(db.Integer, nullable=False, server_default='0')
    user_id = db.Column(db.Integer, nullable=False, server_default='0')
    mobile = db.Column(db.String(20), nullable=False, server_default='')
    data_url = db.Column(db.String(200), nullable=False, server_default='')
    create_time = db.Column(db.DateTime, nullable=False)

    # 1[学信] 2[社保] 3[公积金]
    def getOtherData(self, user_id, source):
        if user_id is None:
            return False
        where = and_(GatherResult.user_id == user_id,
                     GatherResult.source == source)
        data = db.session.query(GatherResult).filter(where).order_by(GatherResult.id.desc()).first()
        return data

    # 1[学信] 2[社保] 3[公积金]
    def getOtherDataToMobile(self, mobile, source):
        if mobile is None:
            return False
        where = and_(GatherResult.mobile == mobile,
                     GatherResult.source == source)
        data = db.session.query(GatherResult).filter(where).order_by(GatherResult.id.desc()).first()
        return data

    #查看社保，学信，公积金数据
    def getOne(self, mobile):
        if mobile is None:
            return False
        where = and_(GatherResult.mobile == mobile,
                     GatherResult.source.in_(("1", "2", "3")))
        data = db.session.query(GatherResult).filter(where).order_by(GatherResult.id.desc()).first()
        return data


    def getCount(self, user_id, source):
        if user_id is None:
            return False
        where = and_(GatherResult.user_id == user_id,
                     GatherResult.source == source)
        data = db.session.query(GatherResult).filter(where).count()
        return data


    def getCountToMoble(self, mobile, source):
        if mobile is None:
            return False
        where = and_(GatherResult.mobile == mobile,
                     GatherResult.source == source)
        data = db.session.query(GatherResult).filter(where).count()
        return data

    # 1[学信] 2[社保] 3[公积金] 7[银行流水]
    def getOtherDataUrl(self, aid, user_id, source):
        if user_id is None or user_id == 0:
            return None
        where = and_(
            GatherResult.aid == aid,
            GatherResult.user_id == user_id,
            GatherResult.source == source
        )
        data = db.session.query(GatherResult).filter(where).order_by(GatherResult.id.desc()).first()
        if data is None:
            return None
        if len(data.data_url) == 0:
            return None
        return self._getDomain() + data.data_url

    def _getDomain(self):
        from lib.config import get_config
        if get_config().TESTING:
            return 'http://182.92.80.211:8091'
        return 'http://openapi.xianhuahua.com'
