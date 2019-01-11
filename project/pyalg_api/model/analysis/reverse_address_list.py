# -*- coding: utf-8 -*-
from lib.application import db
from model.base_model import BaseModel


class ReverseAddressList(db.Model, BaseModel):
    __bind_key__ = 'analysis_repertory'
    __tablename__ = 'reverse_address_list'

    id = db.Column(db.BigInteger, primary_key=True)
    aid = db.Column(db.Integer)
    user_id = db.Column(db.BigInteger)
    user_phone = db.Column(db.String(20), index=True)
    phone = db.Column(db.String(20))
    name = db.Column(db.String(32))
    modify_time = db.Column(db.DateTime)
    create_time = db.Column(db.DateTime)

    def getAddrByPhones(self, phones):
        if len(phones) == 0:
            return []
        oUserPhones = db.session.query(ReverseAddressList.user_phone).filter(ReverseAddressList.phone.in_(phones)).limit(10000).all()
        if oUserPhones:
            return [i.user_phone for i in oUserPhones]
        else :
            return []

    def getAddrByPhone(self, phones, loan_time):
        if len(phones) == 0:
            return []
        oUserPhones = db.session.query(ReverseAddressList.user_phone).filter(ReverseAddressList.phone == phones).filter(ReverseAddressList.create_time <= loan_time).limit(10000).all()
        if oUserPhones:
            return [i.user_phone for i in oUserPhones]
        else :
            return []