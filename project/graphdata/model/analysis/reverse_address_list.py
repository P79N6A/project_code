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
    """
    通过元组通讯录手机号获取间接人和创建时间返回字典
    """
    def getAddrByAll(self, phones):
        mobile_data = {}
        if len(phones) == 0:
            return mobile_data
        oUserPhones = db.session.query(ReverseAddressList.user_phone, ReverseAddressList.phone,ReverseAddressList.create_time).filter(ReverseAddressList.phone.in_(phones)).limit(10000).all()
        if oUserPhones:
            for i in oUserPhones:
                if i[0] not in mobile_data:
                    #user_phone  手机号  phone通讯录中的手机号  create_time创建时间
                    mobile_data[i[0]] = {"user_phone":i[0], "phone":i[1], "create_time":i[2]}
        return mobile_data
    """
    获取创建时间
    """
    def getAddrByData(self, mobile, phones):
        mobile_data = {}
        if len(phones) == 0:
            return mobile_data
        oUserPhones = db.session.query(ReverseAddressList.user_phone, ReverseAddressList.phone,ReverseAddressList.create_time).filter(ReverseAddressList.user_phone==mobile).filter(ReverseAddressList.phone.in_(phones)).limit(10000).all()
        if oUserPhones:
            for i in oUserPhones:
                if i[0] not in mobile_data:
                    # user_phone  手机号  phone通讯录中的手机号  create_time创建时间
                    mobile_data[i[0]] = {"user_phone": i[0], "phone": i[1], "create_time": i[2]}
        return mobile_data

    def getCreateTimeByPhone(self,phone,user_phone):
        if len(phone) == 0 or len(user_phone) ==0:
            return []
        oUserPhones = db.session.query(ReverseAddressList.user_phone,ReverseAddressList.phone,ReverseAddressList.create_time).filter(
            ReverseAddressList.phone == phone).filter(ReverseAddressList.user_phone != user_phone).order_by(ReverseAddressList.create_time).limit(1)
        oUserPhones1 = db.session.query(ReverseAddressList.user_phone,ReverseAddressList.create_time).filter(ReverseAddressList.phone == phone).filter(
            ReverseAddressList.user_phone == user_phone).order_by(ReverseAddressList.create_time).limit(1)
        if oUserPhones:
            if oUserPhones1:
                list = []
                for i in oUserPhones:
                    for j in oUserPhones1:
                        a = [j.user_phone,i.user_phone,j.create_time,i.create_time]
                        list.append(a)
                return list
        else :
            return []