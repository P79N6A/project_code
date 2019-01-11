# -*- coding: utf-8 -*-
# sqlacodegen  mysql://root:123!@#@127.0.0.1/xhh_test --outfile yyy.py --flask
from lib.application import db
from .base_model import BaseModel
from sqlalchemy import and_
from sqlalchemy.sql import func
from lib.logger import logger
import os

class YiUser(db.Model, BaseModel):
    __bind_key__ = 'xhh_yiyiyuan'
    __tablename__ = 'yi_user'

    user_id = db.Column(db.BigInteger, primary_key=True)
    openid = db.Column(db.String(64), index=True)
    mobile = db.Column(db.String(20), unique=True)
    invite_code = db.Column(db.String(32))
    invite_qrcode = db.Column(db.String(32))
    from_code = db.Column(db.String(32))
    user_type = db.Column(db.Integer, server_default=db.FetchedValue())
    status = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    identity_valid = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    school_valid = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    school = db.Column(db.String(64))
    school_id = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    edu = db.Column(db.String(64))
    school_time = db.Column(db.String(64))
    realname = db.Column(db.String(32))
    identity = db.Column(db.String(20))
    industry = db.Column(db.Integer, server_default=db.FetchedValue())
    company = db.Column(db.String(128))
    position = db.Column(db.String(128))
    telephone = db.Column(db.String(32))
    address = db.Column(db.String(128))
    pic_self = db.Column(db.String(128))
    pic_identity = db.Column(db.String(128))
    pic_type = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    come_from = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    serverid = db.Column(db.String(128))
    create_time = db.Column(db.DateTime)
    pic_up_time = db.Column(db.DateTime)
    final_score = db.Column(db.Integer)
    birth_year = db.Column(db.Integer)
    last_login_time = db.Column(db.DateTime)
    last_login_type = db.Column(db.String(16))
    verify_time = db.Column(db.DateTime)
    is_webunion = db.Column(db.String(8), nullable=False, server_default=db.FetchedValue())
    webunion_confirm_time = db.Column(db.DateTime)
    is_red_packets = db.Column(db.String(4), nullable=False, server_default=db.FetchedValue())

    def get(self, user_id):
        return self.query.get(user_id)

    def getByUserId(self, user_id):
        db_user = db.session.query(YiUser).filter(YiUser.user_id == user_id).limit(1).first()
        return db_user

    def getByMobile(self, mobile):
        db_user = db.session.query(YiUser).filter(YiUser.mobile == mobile).limit(1).first()
        return db_user

    def isOverdueMobile(self, mobiles):
        if len(mobiles) == 0:
            return []

        mobile_str ='"' + '","'.join(mobiles) + '"'
        sql = '''
            SELECT
              u.mobile,
              u.user_id,
              l.status
            FROM yi_user_loan l
              LEFT JOIN yi_user u
                ON l.user_id = u.user_id
            WHERE u.mobile IN(%s)
                AND l.status = 12
            LIMIT 0, 50;
        ''' % mobile_str
        users = db.session.execute(sql, bind=self.get_engine()).fetchall()
        return users

    def getUidsByMobiles(self,mobiles):
        if len(mobiles) == 0:
            return []
        oUsers = db.session.query(YiUser.user_id.label('user_id')).filter(YiUser.mobile.in_(mobiles)).limit(1000).all()
        if oUsers :
            return [i.user_id for i in oUsers]
        else :
            return []
            
    def getUidCounts(self,mobiles):
        if len(mobiles) == 0:
            return 0
        counts = db.session.query(YiUser).filter(YiUser.mobile.in_(mobiles)).limit(1000).count()
        return counts


    def getMobileNum(self,start,end):
        where = and_(
            YiUser.user_id >= start,
            YiUser.user_id <= end,
            YiUser.mobile != None
        )
        res = db.session.query(YiUser).filter(where).all()
        return res

    def get_maxid(self):
        maxid = db.session.query(func.max(YiUser.user_id)).scalar()
        return maxid

    def getByMobileAndIdentity(self, mobile, identity):
        where = and_(
            YiUser.mobile == mobile,
            YiUser.identity == identity
        )
        db_user = db.session.query(YiUser).filter(where).limit(1).first()
        return db_user

    def getByIdentity(self, identity):
        db_user = db.session.query(YiUser).filter(YiUser.identity == identity).first()
        return db_user