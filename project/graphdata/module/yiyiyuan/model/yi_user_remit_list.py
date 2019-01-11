# -*- coding: utf-8 -*-
from lib.application import db
from .base_model import BaseModel
from lib.logger import logger
from sqlalchemy import desc, and_

class YiUserRemitList(db.Model, BaseModel):
    __bind_key__ = 'xhh_yiyiyuan'
    __tablename__ = 'yi_user_remit_list'

    user_id = db.Column(db.BigInteger, primary_key=True)
    loan_id = db.Column(db.BigInteger, nullable=False, index=True)
    remit_status = db.Column(db.String(12))
    fund = db.Column(db.Integer, nullable=False)
    create_time = db.Column(db.DateTime)

    def getMobiles(self,start_time,end_time):
        '''
        获取需要处理的数据, 默认查询1000条
        '''
        start_time = start_time
        end_time = end_time
        # 借款资方数据
        sql = "SELECT u.mobile,u.user_id,l.loan_id FROM yi_user_remit_list l LEFT JOIN yi_user u ON l.user_id = u.user_id WHERE l.remit_status = 'SUCCESS' AND l.fund = 6 AND l.create_time >= '"+ start_time +"' AND l.create_time <= '"+ end_time +"' order by l.id DESC LIMIT 1000;"

        users = db.session.execute(sql, bind=self.get_engine()).fetchall()

        if len(users) == 0:
            return None
        return users

