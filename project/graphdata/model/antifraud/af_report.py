# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base

class AfReport(db.Model, Base):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_report'

    id = db.Column(db.BigInteger, primary_key=True)
    request_id = db.Column(db.BigInteger, nullable=False, index=True, server_default=db.FetchedValue())
    aid = db.Column(db.Integer)
    user_id = db.Column(db.BigInteger, nullable=False, index=True, server_default=db.FetchedValue())
    report_aomen = db.Column(db.Integer)
    report_110 = db.Column(db.Integer)
    report_120 = db.Column(db.Integer)
    report_lawyer = db.Column(db.Integer)
    report_court = db.Column(db.Integer)
    report_use_time = db.Column(db.Integer)
    report_shutdown = db.Column(db.Integer)
    report_name_match = db.Column(db.Integer)
    report_fcblack_idcard = db.Column(db.Integer)
    report_fcblack_phone = db.Column(db.Integer)
    report_fcblack = db.Column(db.Integer)
    report_operator_name = db.Column(db.String(20))
    report_reliability = db.Column(db.Integer)
    report_night_percent = db.Column(db.Numeric(10, 2))
    report_loan_connect = db.Column(db.String(50))
    create_time = db.Column(db.DateTime, nullable=False)

    def getReportShutdownByUserIds(self, user_ids):
        '''
        获取af_detail
        '''
        if len(user_ids) == 0:
            return  []
        userid_str = '"' + '","'.join(str(val) for val in user_ids) + '"'
        sql = '''SELECT report_shutdown from (select * from af_report  WHERE user_id in(%s)  ORDER BY id DESC  ) tmp GROUP BY user_id ''' % userid_str
        res = db.session.execute(sql, bind=self.get_engine()).fetchall()
        report_shutdown= []
        if len(res) > 0:
            for i in res:
                if i[0] is None:
                    report_shutdown.append(0)
                else:
                    report_shutdown.append(i[0])
        return report_shutdown
