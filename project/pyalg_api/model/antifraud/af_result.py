# -*- coding: utf-8 -*-
from lib.application import db
from lib.logger import logger
from model.base_model import BaseModel
from datetime import datetime


class AfResult(db.Model, BaseModel):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_result'

    id = db.Column(db.BigInteger, primary_key=True)
    request_id = db.Column(db.BigInteger, nullable=False, index=True, server_default=db.FetchedValue())
    aid = db.Column(db.Integer)
    user_id = db.Column(db.BigInteger, nullable=False, index=True, server_default=db.FetchedValue())
    setting_id = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    score = db.Column(db.Integer, server_default=db.FetchedValue())
    result_status = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    result_subject = db.Column(db.Text, nullable=False)
    create_time = db.Column(db.DateTime, nullable=False)

    def addResult(self, dict_data):

        try:
            if dict_data is None or len(dict_data) == 0:
                return False

            self.request_id = dict_data.get('request_id')
            self.user_id = dict_data.get('user_id')
            self.aid = dict_data.get('aid',1)
            self.setting_id = dict_data.get('setting_id', 0)
            self.score = dict_data.get('score', 0)
            self.result_status = dict_data.get('result_status')
            self.result_subject = dict_data.get('result_subject')
            self.create_time = datetime.now()
            self.add()
            db.session.commit()
            return True
        except Exception as e:
            logger.error("AfResult-addOne:%s" % e)
            return False
