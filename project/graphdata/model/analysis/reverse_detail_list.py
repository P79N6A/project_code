# -*- coding: utf-8 -*-
from lib.application import db
from model.base_model import BaseModel
from datetime import datetime
from lib.logger import logger
from module.yiyiyuan import YiUser


class ReverseDetailList(db.Model, BaseModel):
    __bind_key__ = 'analysis_repertory'
    __tablename__ = 'reverse_detail_list'

    id = db.Column(db.BigInteger, primary_key=True)
    aid = db.Column(db.Integer)
    user_id = db.Column(db.BigInteger, index=True)
    mobile = db.Column(db.String(20), index=True)
    phone = db.Column(db.String(20), index=True)
    call_times = db.Column(db.Integer)
    use_time = db.Column(db.Integer)
    min_time = db.Column(db.DateTime)
    max_time = db.Column(db.DateTime)
    create_time = db.Column(db.DateTime)

    def getDetailByPhones(self, phones):
        if len(phones) == 0:
            return []
        oUserPhones = db.session.query(ReverseDetailList.mobile).filter(ReverseDetailList.phone.in_(phones)).limit(10000).all()
        if oUserPhones:
            return [i.mobile for i in oUserPhones]
        else :
            return []

    def saveDetailList(self, dict_res, data):
        createTime = data.create_time if data is not None else '0000-00-00 00:00:00'
        mobile =  str(data.phone) if data is not None else ''
        aid = int(data.aid) if data is not None else 0
        user = YiUser().getByMobile(mobile)
        user_id = int(user.user_id) if user is not None else 0
        insertSql = []
        for key, value in dict_res.items():
            phone = str(key)
            min_time = str(value.get('min_time','0000-00-00 00:00:00'))
            max_time = str(value.get('max_time','0000-00-00 00:00:00'))
            use_time = int(value.get('use_time',0))
            call_times = int(value.get('call_times',0))
            data_dict = {'aid': aid, 'user_id': user_id, 'mobile' : mobile, 'call_times' : call_times, 'min_time' : min_time, 'max_time' : max_time, 'use_time' : use_time, 'phone' : phone, 'create_time' : createTime}
            insertSql.append(data_dict)
        db.session.execute(self.__table__.insert(),insertSql)
        db.session.commit()