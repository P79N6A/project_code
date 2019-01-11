# -*- coding: utf-8 -*-
# sqlacodegen  mysql://root:123!@#@127.0.0.1/xhh_test --outfile yyy.py --flask
from lib.application import db
from .base_model import BaseModel
from .yi_user import YiUser
from sqlalchemy import and_, func


class YiFriend(db.Model, BaseModel):
    __bind_key__ = 'xhh_yiyiyuan'
    __tablename__ = 'yi_friends'

    id = db.Column(db.BigInteger, primary_key=True)
    user_id = db.Column(db.BigInteger, nullable=False, index=True)
    fuser_id = db.Column(db.BigInteger, nullable=False, index=True)
    type = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    auth = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    authed = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    company = db.Column(db.String(100), nullable=False, index=True)
    same_company = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    school_id = db.Column(db.BigInteger, nullable=False, index=True, server_default=db.FetchedValue())
    same_school = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    invite = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    like = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    modify_time = db.Column(db.DateTime, nullable=False)
    create_time = db.Column(db.DateTime, nullable=False)

    def getByUserId(self, user_id):
        '''
        获取认证
        '''
        where = and_(YiFriend.user_id == user_id, YiFriend.type < 3)
        t = db.session.query(YiFriend, YiUser).outerjoin(YiUser, YiFriend.fuser_id == YiUser.user_id)
        res = t.filter(where).order_by(YiFriend.id).limit(1000).all()
        return res

    def blackNum(self, user_id):
        '''
        一级关系中有黑名单
        '''
        where = and_(YiFriend.user_id == user_id, YiFriend.type == 1, YiUser.status == 5)
        t = db.session.query(func.count(YiFriend.id).label("user_count")).outerjoin(
            YiUser, YiFriend.fuser_id == YiUser.user_id)
        res = t.filter(where).limit(1).first()
        return res.user_count
