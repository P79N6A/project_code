# -*- coding: utf-8 -*-
from lib.application import db
from lib.logger import logger
import os
from .base import Base
from datetime import datetime, timedelta
from sqlalchemy import and_

class AfJacBase(db.Model, Base):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_jac_base'

    id = db.Column(db.BigInteger, primary_key=True)
    aid = db.Column(db.Integer,nullable=False)
    jac_match_id = db.Column(db.BigInteger, nullable=False, index=True)
    request_id = db.Column(db.BigInteger, nullable=False, index=True)
    base_id = db.Column(db.BigInteger, nullable=False, index=True)
    user_id = db.Column(db.BigInteger, nullable=False, index=True)
    loan_id = db.Column(db.BigInteger, nullable=False, index=True)
    mobile = db.Column(db.String(32), nullable=False, index=True)
    jac_status = db.Column(db.Integer, nullable=False)
    base_status = db.Column(db.Integer, nullable=False)
    create_time = db.Column(db.DateTime, nullable=False, index=True)
    modify_time = db.Column(db.DateTime, nullable=False)

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

    def getJaccardData(self, start_time):
        '''
        获取需要匹配间接关系(jaccard)的数据
        '''
        where = and_(
            AfJacBase.create_time >= start_time,
            AfJacBase.jac_status == 0
        )

        res = db.session.query(AfJacBase) \
            .filter(where) \
            .order_by(AfJacBase.create_time) \
            .limit(500) \
            .all()

        return res
    def getRelationData(self, start_time):
        '''
        获取已存在关系(relation)的数据
        '''
        where = and_(
            AfJacBase.create_time >= start_time,
            AfJacBase.jac_status == 2
        )

        res = db.session.query(AfJacBase) \
            .filter(where) \
            .order_by(AfJacBase.create_time) \
            .limit(500) \
            .all()
        return res
    def getJaccardDataByids(self, ids):
        '''
        获取需要匹配间接关系(jaccard)的数据
        '''
        where = and_(
            AfJacBase.jac_status == 0
        )
        res = db.session.query(AfJacBase) \
            .filter(AfJacBase.match_status.in_([0,1,2])) \
            .order_by(AfJacBase.create_time) \
            .group_by(AfJacBase.user_id) \
            .filter(AfJacBase.user_id.in_(ids)) \
            .limit(500) \
            .all()
        return res

    def getAfBaseData(self, start_time):
        where = and_(
            AfJacBase.create_time >= start_time,
            AfJacBase.jac_status == 0, #初始
            # AfBase.aid == aid
        )

        res = db.session.query(AfJacBase)   \
            .filter(where) \
            .order_by(AfJacBase.create_time) \
            .limit(1000) \
            .all()

        return res

    def lockMatchStatus(self, data, status):
        if len(data) == 0:
            return False

        now = datetime.now()
        for o in data:
            o.modify_time = now
            o.jac_status = status  # 锁定 | 完成

        db.session.commit()

        return True

    def finishBase(self, base_id):
        '''
        反欺诈分析结束更新base_id = 2
        '''
        res = db.session.query(AfJacBase)\
            .filter(AfJacBase.base_id == base_id)\
            .update({AfJacBase.base_status:2,AfJacBase.modify_time:datetime.now()})
        if res == 0:
            logger.error("af_base.id:%s update af_jac_base is fail" % (base_id))
            return False
        db.session.commit()
        return True

    def finishJac(self, match_status):
        '''
        杰卡德关系结束匹配流程
        '''
        self.match_status = match_status
        self.modify_time = datetime.now()
        db.session.commit()