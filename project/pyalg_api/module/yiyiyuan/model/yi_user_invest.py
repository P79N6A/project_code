# -*- coding: utf-8 -*-
# sqlacodegen  mysql://root:123!@#@127.0.0.1/xhh_test --outfile yyy.py --flask
from lib.application import db
from .base_model import BaseModel
from .yi_user import YiUser


class YiUserInvest(db.Model, BaseModel):
    __bind_key__ = 'xhh_yiyiyuan'
    __tablename__ = 'yi_user_invest'
    __table_args__ = (
        db.Index('userinvest', 'user_id', 'loan_id', 'loan_user_id'),
    )

    invest_id = db.Column(db.BigInteger, primary_key=True)
    user_id = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    loan_user_id = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    loan_id = db.Column(db.BigInteger, nullable=False, server_default=db.FetchedValue())
    amount = db.Column(db.Numeric(10, 4), nullable=False)
    _yield = db.Column('yield', db.Numeric(10, 4), nullable=False, server_default=db.FetchedValue())
    start_date = db.Column(db.DateTime)
    end_date = db.Column(db.DateTime)
    status = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    contract = db.Column(db.String(20))
    contract_url = db.Column(db.String(128))
    create_time = db.Column(db.DateTime)
    version = db.Column(db.Integer)

    def getInvestMe(self, loan_user_id):
        '''
        投资我的人
        '''
        fields = [YiUserInvest.loan_user_id.label('my_user_id'), YiUserInvest.user_id.label(
            'i_user_id'), YiUser.mobile.label('mobile')]

        t = db.session.query(*fields).outerjoin(YiUser, YiUser.user_id == YiUserInvest.user_id)
        t.filter(YiUserInvest.loan_user_id == loan_user_id)
        res = t.limit(1000).all()
        return res

    def getMyInvest(self, user_id):
        '''
        我投资的人
        '''
        fields = [YiUserInvest.loan_user_id.label('i_user_id'), YiUserInvest.user_id.label('my_user_id'), YiUser.mobile.label(
            'mobile')]
        t = db.session.query(*fields).outerjoin(YiUser, YiUser.user_id == YiUserInvest.loan_user_id)
        res = t.filter(YiUserInvest.user_id == user_id).limit(1000).all()
        return res
