# -*- coding: utf-8 -*-
# sqlacodegen  mysql://root:123!@#@127.0.0.1/xhh_test --outfile yyy.py --flask

from sqlalchemy import and_
from datetime import datetime, timedelta

from lib.application import db
from .base_model import BaseModel
from .yi_user import YiUser

class YiAntiFraud(db.Model, BaseModel):
    __bind_key__ = 'xhh_yiyiyuan'
    __tablename__ = 'yi_anti_fraud'

    id = db.Column(db.BigInteger, primary_key=True)
    user_id = db.Column(db.BigInteger, nullable=False, index=True, server_default=db.FetchedValue())
    loan_id = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    type = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    model_status = db.Column(db.Integer, nullable=False, index=True, server_default=db.FetchedValue())
    result_status = db.Column(db.Integer, nullable=False, index=True, server_default=db.FetchedValue())
    result_subject = db.Column(db.Text, nullable=False)
    result_time = db.Column(db.DateTime, nullable=False)
    modify_time = db.Column(db.DateTime, nullable=False)
    create_time = db.Column(db.DateTime, nullable=False)
    #version = db.Column(db.Integer, nullable=False)
    
    def getById(self, id):
        db_afraud = self.query.get(int(id))
        return db_afraud

    def getByUserId(self, user_id):
        '''
        获取认证
        '''
        where = and_(YiAntiFraud.user_id == user_id)
        res = db.session.query(YiAntiFraud, YiUser)   \
            .outerjoin(YiUser, YiAntiFraud.user_id == YiUser.user_id) \
            .filter(where) \
            .order_by(YiAntiFraud.id) \
            .limit(1000).all()

        return res

    def getData(self, end_time):
        '''
        获取需要处理的数据, 默认查询一小时内
        '''
        # 1. 精确到分
        end_time = datetime.strptime(end_time, '%Y-%m-%d %H:%M:%S')
        end_time = end_time - timedelta(seconds=end_time.second)

        # 2. 五分钟内 @todo
        #start_time = end_time - timedelta(seconds=3600)
        where = and_(
            #YiAntiFraud.create_time >= start_time,
            #YiAntiFraud.create_time < end_time,
            YiAntiFraud.model_status == 1,
            YiAntiFraud.result_status == 0
        )

        res = db.session.query(YiAntiFraud, YiUser)   \
            .outerjoin(YiUser, YiAntiFraud.user_id == YiUser.user_id) \
            .filter(where) \
            .order_by(YiAntiFraud.id) \
            .limit(1000) \
            .all()

        return res

    def lock(self, data, status):
        if len(data) == 0:
            return False

        now = datetime.now()
        for o in data:
            o.modify_time = now
            o.model_status = status  # 锁定 | 完成

        db.session.commit()

        return True


    def finished(self, result_status, result_subject):
        '''
        结束借款流程
        '''
        self.model_status = 3
        self.result_status = 0
        self.result_subject = result_subject
        self.result_time = self.modify_time = datetime.now()
        db.session.commit()
    
    