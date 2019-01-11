# -*- coding: utf-8 -*-
# 注意这里使用了阿里云本地库的通讯录
# sqlacodegen  mysql://root:123!@#@127.0.0.1/xhh_test --outfile yyy.py --flask
from lib.application import db
from .base_model import BaseModel
from sqlalchemy import and_, func, distinct
from .yi_user import YiUser


class YiAddressList(db.Model, BaseModel):
    __bind_key__ = 'own_yiyiyuan'
    __tablename__ = 'yi_address_list'

    id = db.Column(db.BigInteger, primary_key=True)
    user_id = db.Column(db.BigInteger, nullable=False)
    phone = db.Column(db.String(20), nullable=False)
    name = db.Column(db.String(20), nullable=False)
    modify_time = db.Column(db.DateTime, nullable=False)
    create_time = db.Column(db.DateTime, nullable=False)

    def getByUserid(self, user_id):
        if not user_id:
            return None
        #@todo 这里会不会有问题
        dbAddrLists = self.query.filter(YiAddressList.user_id == user_id).limit(10000).all()
        addrLists = []
        for i in dbAddrLists:
            addrLists.append(self.row2dict(i))
        return addrLists

    def blackNum(self, user_id):
        '''
        通讯录中有黑名单
        '''
        return 0
        #where = and_(YiAddressList.user_id == user_id, YiUser.status == 5)
        #t = db.session.query(func.count(distinct(YiAddressList.id)).label("user_count")).outerjoin(
        #    YiUser, YiAddressList.phone == YiUser.mobile)
        #res = t.filter(where).limit(1).first()
        #return res.user_count
