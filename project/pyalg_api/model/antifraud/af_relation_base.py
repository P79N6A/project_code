# -*- coding: utf-8 -*-
import os
from lib.application import db
from lib.logger import logger
from .base import Base
from datetime import datetime, timedelta
from sqlalchemy import and_


class AfRelationBase(db.Model, Base):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_relation_base'

    id = db.Column(db.BigInteger, primary_key=True)
    base_id = db.Column(db.BigInteger, nullable=False, server_default='0')
    strategy_request_id = db.Column(db.BigInteger, nullable=False, server_default='0')
    create_time = db.Column(db.DateTime, nullable=False)


    def getData(self, base_id, strategy_request_id):
        if base_id is None:
            return False
        if strategy_request_id is None:
            return False
        where = and_(
            AfRelationBase.base_id == int(base_id),
            AfRelationBase.strategy_request_id == int(strategy_request_id)
        )
        data = self.query.filter(where).first()
        return data

    def getInfo(self, base_id):
        if base_id is None:
            return False
        data = self.query.filter(AfRelationBase.base_id == int(base_id)).first()
        return data

    '''
    保存数据
    '''
    def saveData(self, base_id, strategy_request_id):
        if base_id is None:
            return False
        if strategy_request_id is None:
            return False
        #判断是否存在
        get_data = self.getData(base_id, strategy_request_id)
        #存在不记录
        if get_data:
            return True

        try:
            self.base_id = base_id
            self.strategy_request_id = strategy_request_id
            self.create_time = datetime.now()
            self.add()
            db.session.commit()
            return True
        except AttributeError as error:
            print(error)
            return False

        except sqlalchemy.exc.IntegrityError as error:
            print(error)
            return False
