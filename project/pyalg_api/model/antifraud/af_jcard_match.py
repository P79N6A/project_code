# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base
from sqlalchemy import and_
from lib.logger import logger
import json
import urllib.request
import urllib.error
import urllib.parse
import socket

class AfJcardMatch(db.Model, Base):
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_jcard_match'

    id = db.Column(db.BigInteger, primary_key=True)
    request_id = db.Column(db.BigInteger, nullable=False,index=True)
    aid = db.Column(db.Integer,nullable=False)
    user_id = db.Column(db.BigInteger, nullable=False, index=True)
    jcard_result = db.Column(db.Text)
    create_time = db.Column(db.DateTime, nullable=False)

    def getRelationData(self, user_id, request_id, aid):
        '''
        获取已存在关系(relation)的数据
        '''
        where = and_(
            AfJcardMatch.request_id == request_id,
            AfJcardMatch.user_id == user_id,
            AfJcardMatch.aid == aid,
        )

        res = db.session.query(AfJcardMatch) \
            .filter(where) \
            .order_by(AfJcardMatch.id.desc()) \
            .limit(1) \
            .first()

        return res

    def getRelation(self, url):
        # 获取详情
        strings = self._getByUrl(url)
        data = json.loads(strings)
        return data

    def _getByUrl(self, url):
        # 获取url中的内容, 设置超时
        html = self._getByUrl2(url)
        if html is None:
            # 重试
            html = self._getByUrl2(url)

        if html is None:
            raise Exception(1000, 'cant download by ' + url)

        return html

    def _getByUrl2(self, url):
        socket.setdefaulttimeout(25)
        try:
            response = urllib.request.urlopen(url)
            html = response.read()
        except Exception as e:
            logger.error('url get fail %s' % e)
            html = None
        return html