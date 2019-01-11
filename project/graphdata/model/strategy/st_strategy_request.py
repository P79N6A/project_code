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
    # version = db.Column(db.Integer, nullable=False)
    
    def getById(self, id):
        db_strategy = self.query.get(int(id))
        return db_strategy

    def getByReqId(self, request_id):
        if request_id is None:
            return None
        where = and_(StrategyRequest.req_id == int(request_id))
        data = self.query.filter(where).order_by(StrategyRequest.create_time).first()
        return data
    
    def getData(self, end_time):
        '''
        获取需要处理的数据, 默认查询一小时内
        '''
        # 1. 精确到分
        end_time = datetime.strptime(end_time, '%Y-%m-%d %H:%M:%S')
        end_time = end_time - timedelta(seconds=end_time.second)

        # 2. 查询一小时内
        #start_time = end_time - timedelta(seconds=3600)
        where = and_(
            #StrategyRequest.create_time >= start_time,
            #StrategyRequest.create_time < end_time,
            StrategyRequest.status == 0
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
        db.session.commit()
