# -*- coding: utf-8 -*-
import os
from lib.application import db
from lib.logger import logger
from .base import Base
from datetime import datetime, timedelta
from sqlalchemy import and_

class AfBase(db.Model, Base):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_base'

    id = db.Column(db.BigInteger, primary_key=True)
    request_id = db.Column(db.BigInteger, nullable=False, index=True)
    aid = db.Column(db.Integer,nullable=False)
    user_id = db.Column(db.BigInteger, nullable=False, index=True)
    loan_id = db.Column(db.BigInteger, nullable=False, index=True)
    jxlstat_id = db.Column(db.BigInteger, nullable=False, index=True)
    match_status = db.Column(db.Integer, nullable=False)
    create_time = db.Column(db.DateTime, nullable=False)
    modify_time = db.Column(db.DateTime, nullable=False)

    def getById(self, id):
        db_base = self.query.get(int(id))
        return db_base

    def addBaseData(self, dict_data):
        try:
            if dict_data is None or len(dict_data) == 0:
                return False
            return self.addJacByDict(dict_data)
        except Exception as e:
            logger.error(self.__class__.__name__ + " addOne:%s" % e)
            return 0

    def addJacByDict(self, dict_data):
        for k, v in list(dict_data.items()):
            setattr(self, k, v)
        return self.save()

    def save(self):
        db.session.add(self)
        try:
            db.session.flush()
            db.session.commit()
            db.session.refresh(self)
            return self.id
        except Exception:
            db.session.rollback()
            return 0

    def getMulMatchData(self, start_time):
        '''
        获取需要匹配二级关系的数据
        '''
        where = and_(
            AfBase.create_time >= start_time,
            AfBase.match_status == 2
        )

        res = db.session.query(AfBase)   \
            .filter(where) \
            .order_by(AfBase.create_time) \
            .limit(500) \
            .all()

        return res

    def getJaccardData(self, start_time):
        '''
        获取需要匹配间接关系(jaccard)的数据
        '''
        where = and_(
            AfBase.create_time >= start_time,
            AfBase.match_status == 4
        )

        res = db.session.query(AfBase) \
            .filter(where) \
            .order_by(AfBase.create_time) \
            .limit(500) \
            .all()

        return res
    def getRelationData(self, start_time):
        '''
        获取已存在关系(relation)的数据
        '''
        where = and_(
            AfBase.create_time >= start_time,
            AfBase.match_status == 6
        )

        res = db.session.query(AfBase) \
            .filter(where) \
            .order_by(AfBase.create_time) \
            .limit(500) \
            .all()

        return res
    def getJaccardDataByids(self, ids):
        '''
        获取需要匹配间接关系(jaccard)的数据
        '''
        where = and_(
            AfBase.match_status == 4
        )
        res = db.session.query(AfBase) \
            .filter(AfBase.match_status.in_([4,5,6])) \
            .order_by(AfBase.create_time) \
            .group_by(AfBase.user_id) \
            .filter(AfBase.user_id.in_(ids)) \
            .limit(500) \
            .all()
        return res

    def getAfBaseData(self, start_time):
        where = and_(
            AfBase.create_time >= start_time, 
            AfBase.match_status == 0, #初始
            AfBase.aid.in_([1,14])
        )

        res = db.session.query(AfBase)   \
            .filter(where) \
            .order_by(AfBase.create_time) \
            .limit(1000) \
            .all()

        return res

    def lockMatchStatus(self, data, status):
        if len(data) == 0:
            return False

        now = datetime.now()
        for o in data:
            o.modify_time = now
            o.match_status = status  # 锁定 | 完成

        db.session.commit()

        return True

    def finishMatched(self, match_status):
        '''
        结束二级关系匹配流程
        '''
        self.match_status = match_status
        self.modify_time = datetime.now()
        db.session.commit()

    #
    def getBaseForbaseid(self, base_id):
        if not base_id:
            return False
        return db.session.query(AfBase).filter(AfBase.id==base_id).first()

    