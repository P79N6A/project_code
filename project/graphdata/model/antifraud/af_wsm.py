# -*- coding: utf-8 -*-
from lib.application import db
from lib.logger import logger
from model.base_model import BaseModel
from datetime import datetime, timedelta

class AfWsm(db.Model, BaseModel):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_wsm'

    id = db.Column(db.BigInteger, primary_key=True)
    loan_id = db.Column(db.BigInteger, nullable=False, index=True, server_default=db.FetchedValue())
    ymonth = db.Column(db.String(20), nullable=False)
    mobile = db.Column(db.String(20), nullable=False)
    zs_in_times = db.Column(db.Integer,nullable=True)
    st_in_times = db.Column(db.Integer,nullable=True)
    te_in_times = db.Column(db.Integer,nullable=True)
    etf_in_times = db.Column(db.Integer,nullable=True)
    zs_out_times = db.Column(db.Integer,nullable=True)
    st_out_times = db.Column(db.Integer,nullable=True)
    te_out_times = db.Column(db.Integer,nullable=True)
    etf_out_times = db.Column(db.Integer,nullable=True)
    zs_in_duration = db.Column(db.BigInteger, nullable=True)
    st_in_duration = db.Column(db.BigInteger, nullable=True)
    te_in_duration = db.Column(db.BigInteger, nullable=True)
    etf_in_duration = db.Column(db.BigInteger, nullable=True)
    zs_out_duration = db.Column(db.BigInteger, nullable=True)
    st_out_duration = db.Column(db.BigInteger, nullable=True)
    te_out_duration = db.Column(db.BigInteger, nullable=True)
    etf_out_duration = db.Column(db.BigInteger, nullable=True)
    zs_call_times = db.Column(db.Integer,nullable=True)
    st_call_times = db.Column(db.Integer, nullable=True)
    te_call_times = db.Column(db.Integer, nullable=True)
    etf_call_times = db.Column(db.Integer, nullable=True)
    zs_call_duration = db.Column(db.BigInteger, nullable=True)
    st_call_duration = db.Column(db.BigInteger, nullable=True)
    te_call_duration = db.Column(db.BigInteger, nullable=True)
    etf_call_duration = db.Column(db.BigInteger, nullable=True)
    work_call_times = db.Column(db.Integer, nullable=True)
    weekend_call_times = db.Column(db.Integer, nullable=True)
    total_times = db.Column(db.Integer, nullable=True)
    total_duration = db.Column(db.BigInteger, nullable=True)
    contacts_times = db.Column(db.Integer, nullable=True)
    contacts_duration = db.Column(db.BigInteger, nullable=True)
    relatives_times = db.Column(db.Integer, nullable=True)
    relatives_duration = db.Column(db.BigInteger, nullable=True)
    contacts_often_time_part = db.Column(db.String(64), nullable=True)
    relatives_often_time_part = db.Column(db.String(64), nullable=True)
    create_time = db.Column(db.DateTime, nullable=False)

    def addWsm(self, dict_data,user):
        try:
            if dict_data is None or len(dict_data) == 0:
                return False

            self.loan_id = user.loan_id
            self.ymonth = dict_data.get('ymonth', '000000')
            self.mobile = user.mobile
            self.zs_in_times = dict_data.get('zs_in_times',0)
            self.st_in_times = dict_data.get('st_in_times', 0)
            self.te_in_times = dict_data.get('te_in_times', 0)
            self.etf_in_times = dict_data.get('etf_in_times',0)
            self.zs_out_times = dict_data.get('zs_out_times',0)
            self.st_out_times = dict_data.get('st_out_times', 0)
            self.te_out_times = dict_data.get('te_out_times', 0)
            self.etf_out_times = dict_data.get('etf_out_times',0)
            self.zs_in_duration = dict_data.get('zs_in_duration',0)
            self.st_in_duration = dict_data.get('st_in_duration', 0)
            self.te_in_duration = dict_data.get('te_in_duration', 0)
            self.etf_in_duration = dict_data.get('etf_in_duration', 0)
            self.zs_out_duration = dict_data.get('zs_out_duration', 0)
            self.st_out_duration = dict_data.get('st_out_duration', 0)
            self.te_out_duration = dict_data.get('te_out_duration', 0)
            self.etf_out_duration = dict_data.get('etf_out_duration',0)
            self.zs_call_times = dict_data.get('zs_call_times', 0)
            self.st_call_times = dict_data.get('st_call_times', 0)
            self.te_call_times = dict_data.get('te_call_times', 0)
            self.etf_call_times = dict_data.get('etf_call_times', 0)
            self.zs_call_duration = dict_data.get('zs_call_duration',0)
            self.st_call_duration = dict_data.get('st_call_duration', 0)
            self.te_call_duration = dict_data.get('te_call_duration', 0)
            self.etf_call_duration = dict_data.get('etf_call_duration', 0)
            self.work_call_times = dict_data.get('work_call_times', 0)
            self.weekend_call_times = dict_data.get('weekend_call_times', 0)
            self.total_times = dict_data.get('total_times', 0)
            self.total_duration = dict_data.get('total_duration', 0)
            self.contacts_times = dict_data.get('contacts_times', 0)
            self.contacts_duration = dict_data.get('contacts_duration', 0)
            self.relatives_times = dict_data.get('relatives_times', 0)
            self.relatives_duration = dict_data.get('relatives_duration', 0)
            self.contacts_often_time_part = dict_data.get('contacts_often_time_part', '0')
            self.relatives_often_time_part = dict_data.get('relatives_often_time_part', '0')
            self.create_time = datetime.now()
            self.add()
            db.session.commit()
            return True
        except Exception as e:
            logger.error("AfWsm-addOne:%s" % e)
            return False