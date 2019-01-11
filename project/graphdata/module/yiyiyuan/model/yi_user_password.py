# -*- coding: utf-8 -*-
# 注意这里使用了阿里云本地库的通讯录
# sqlacodegen  mysql://root:123!@#@127.0.0.1/xhh_test --outfile yyy.py --flask
from lib.application import db
from .base_model import BaseModel
from sqlalchemy import and_

class YiUserPassword(db.Model, BaseModel):
    __bind_key__ = 'xhh_yiyiyuan'
    __tablename__ = 'yi_user_password'

    id = db.Column(db.BigInteger, primary_key=True)
    user_id = db.Column(db.BigInteger, nullable=False, index=True, server_default=db.FetchedValue())
    login_password = db.Column(db.String(64))
    pay_password = db.Column(db.String(64))
    device_tokens = db.Column(db.String(64))
    device_type = db.Column(db.String(10))
    iden_address = db.Column(db.String(64))
    nation = db.Column(db.String(32))
    pic_url = db.Column(db.String(64))
    iden_url = db.Column(db.String(64))
    score = db.Column(db.Numeric(12, 4))
    create_time = db.Column(db.DateTime, nullable=False)
    last_modify_time = db.Column(db.DateTime, nullable=False)
    version = db.Column(db.BigInteger, nullable=False)

    def getAndroidcount(self, user_ids):
        '''
        获取已存在关系(relation)的数据
        '''
        if len(user_ids) == 0:
            return  0
        where = and_(
            YiUserPassword.user_id.in_(user_ids),
            YiUserPassword.device_type == 'android',
        )

        counts = db.session.query(YiUserPassword) \
            .filter(where) \
            .order_by(YiUserPassword.id.desc()) \
            .limit(1000) \
            .count()

        return counts

