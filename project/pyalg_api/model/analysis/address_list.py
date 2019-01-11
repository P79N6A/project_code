# -*- coding: utf-8 -*-
from lib.application import db
from model.base_model import BaseModel


class AddressList(db.Model, BaseModel):
    __bind_key__ = 'analysis_repertory'
    __tablename__ = 'address_list'

    id = db.Column(db.BigInteger, primary_key=True)
    aid = db.Column(db.Integer)
    user_id = db.Column(db.BigInteger)
    user_phone = db.Column(db.String(20), index=True)
    phone = db.Column(db.String(20))
    name = db.Column(db.String(32))
    modify_time = db.Column(db.DateTime)
    create_time = db.Column(db.DateTime)

    def getByUserPhone(self, user_phone):
        db_phones = db.session.query(AddressList.phone).filter(AddressList.user_phone == user_phone).limit(2000).all()
        
        phones = []
        # print(db_phones)
        for i in db_phones:
            phone = {'phone':str(i[0])}
            # phones.append(self.row2dict(i))
            phones.append(phone)
        return phones

    def getByUserPhones(self, phones):
        if len(phones) == 0:
            return []
        oUserPhones = db.session.query(AddressList.phone,AddressList.user_phone).filter(AddressList.user_phone.in_(phones)).limit(1000000).all()
        if oUserPhones:
            return oUserPhones
        else :
            return []
    def getByUserPhoneDict(self, user_phone):
        if not user_phone:
            return None
        dbAddrLists = self.query.filter(AddressList.user_phone == user_phone).limit(10000).all()
        addrLists = []
        for i in dbAddrLists:
            addrLists.append(self.row2dict(i))
        return addrLists