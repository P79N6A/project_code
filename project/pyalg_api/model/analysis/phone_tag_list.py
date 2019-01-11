# -*- coding: utf-8 -*-
import math
from lib.application import db
from model.base_model import BaseModel


class PhoneTagList(db.Model, BaseModel):
    LIMIT = 500
    __bind_key__ = 'analysis_repertory'
    __tablename__ = 'phone_tag_list'

    id = db.Column(db.BigInteger, primary_key=True)
    phone = db.Column(db.String(20), nullable=False)
    source = db.Column(db.BigInteger, nullable=False)
    tag_type = db.Column(db.String(255), nullable=False)
    modify_time = db.Column(db.DateTime, nullable=False)
    create_time = db.Column(db.DateTime, nullable=False)
    other_info = db.Column(db.Text)


    '''
    通过手机号获取标签条数
    '''
    def getLabelAll(self, phone_tuple):
        phone_tuple = list(set(phone_tuple))
        chunk_phone_list = self.chunk(phone_tuple)
        all_list = []
        for phone_list in  chunk_phone_list:
            son_list = db.session.query(PhoneTagList). \
                    filter(PhoneTagList.phone.in_(phone_list)).\
                    limit(self.LIMIT).\
                    all()
            if len(son_list) > 0:
                all_list.extend(son_list)
        return all_list

    def chunk(self, biglist):
        #大列表分割成小列表
        limit = self.LIMIT
        mlen = len(biglist)
        mc = math.ceil(mlen/limit)
        lst = []
        for i in range(0,int(mc)):
            lst.append(biglist[i*limit:(i+1)*limit])

        return lst