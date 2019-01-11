# -*- coding: utf-8 -*-
# 

from sqlalchemy import and_
from datetime import datetime, timedelta

from lib.application import db
from model.base_model import BaseModel

class StrategyRequest(db.Model, BaseModel):
    __bind_key__ = 'xhh_strategy'
    __tablename__ = 'st_strategy_request'

    id = db.Column(db.BigInteger, primary_key=True)
    aid = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    req_id = db.Column(db.BigInteger, nullable=False, unique=True, server_default=db.FetchedValue())
    user_id = db.Column(db.BigInteger, nullable=False, index=True, server_default=db.FetchedValue())
    loan_id = db.Column(db.BigInteger, nullable=False, index=True, server_default=db.FetchedValue())
    status = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    come_from = db.Column(db.Integer, nullable=True, server_default=db.FetchedValue())
    callbackurl = db.Column(db.String(255), nullable=False, server_default=db.FetchedValue())
    create_time = db.Column(db.DateTime, nullable=False)
    modify_time = db.Column(db.DateTime, nullable=False)
    version = db.Column(db.Integer, nullable=False)
    
    def getById(self, id):
        db_strategy = self.query.get(int(id))
        return db_strategy

    def getByReqId(self, db_base):
        if db_base.request_id is None:
            return None
        if db_base.aid is None:
            return None
        if db_base.user_id is None:
            return None
        where = and_(
            StrategyRequest.req_id == int(db_base.request_id),
            StrategyRequest.aid == int(db_base.aid),
            StrategyRequest.user_id == int(db_base.user_id),
        )
        data = self.query.filter(where).order_by(StrategyRequest.id.desc()).first()
        return data

    def getByIdData(self, id):
        if id is None:
            return None
        where = and_(
            StrategyRequest.id == int(id)
        )
        data = self.query.filter(where).first()
        return data
    
    def getData(self, end_time):
        '''
        获取需要处理的数据, 默认查询一小时内
        '''
        # 1. 精确到分
        end_time = datetime.strptime(end_time, '%Y-%m-%d %H:%M:%S')
        end_time = end_time + timedelta(seconds=end_time.second-360)

        # 2. 查询一小时内
        #start_time = end_time - timedelta(seconds=3600)
        where = and_(
            StrategyRequest.aid.in_([1,14]),
            StrategyRequest.create_time < end_time,
            StrategyRequest.status == 100
        )

        res = db.session.query(StrategyRequest)   \
            .filter(where) \
            .order_by(StrategyRequest.id) \
            .limit(1000) \
            .all()

        return res

    def lock(self, data, status):
        if len(data) == 0:
            return False
        now = datetime.now()
        for o in data:
            o.modify_time = now
            o.status = status  # 锁定 | 完成
        db.session.commit()
        return True

    def finished(self, status):
        self.status = status
        self.modify_time = datetime.now()
        self.version = self.version+1
        db.session.commit()
        return True

    # 获取待处理数据
    def getInitData(self):
        now = datetime.now()
        start_time = now + timedelta(seconds = now.second - 86400)
        where = and_(
            StrategyRequest.status == 0,
            StrategyRequest.create_time > start_time,
        )
        res = db.session.query(StrategyRequest).filter(where).order_by(StrategyRequest.id).limit(1).all()
        return res