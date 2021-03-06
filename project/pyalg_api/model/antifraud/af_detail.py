# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base
from sqlalchemy import and_

class AfDetail(db.Model, Base):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_detail'

    id = db.Column(db.BigInteger, primary_key=True)
    request_id = db.Column(db.BigInteger, index=True, server_default=db.FetchedValue())
    aid = db.Column(db.Integer)
    user_id = db.Column(db.BigInteger, index=True, server_default=db.FetchedValue())
    com_start_time = db.Column(db.DateTime)
    com_end_time = db.Column(db.DateTime)
    com_days = db.Column(db.Integer)
    com_month_num = db.Column(db.Numeric(10, 2))
    com_use_time = db.Column(db.Integer)
    com_count = db.Column(db.Integer)
    com_call = db.Column(db.Integer)
    com_answer = db.Column(db.Integer)
    com_duration = db.Column(db.BigInteger)
    com_call_duration = db.Column(db.BigInteger)
    com_answer_duration = db.Column(db.BigInteger)
    com_month_connects = db.Column(db.Numeric(12, 2))
    com_month_call = db.Column(db.Numeric(12, 2))
    com_month_answer = db.Column(db.Numeric(12, 2))
    com_month_duration = db.Column(db.Numeric(12, 2))
    com_month_call_duration = db.Column(db.Numeric(12, 2))
    com_month_answer_duration = db.Column(db.Numeric(12, 2))
    com_people = db.Column(db.Integer)
    com_mobile_people = db.Column(db.Integer)
    com_tel_people = db.Column(db.Integer)
    com_month_people = db.Column(db.Numeric(12, 2))
    com_mobile_people_mavg = db.Column(db.Numeric(12, 2))
    com_tel_people_mavg = db.Column(db.Numeric(12, 2))
    com_night_connect = db.Column(db.Integer)
    com_night_duration = db.Column(db.BigInteger)
    com_night_connect_mavg = db.Column(db.Numeric(12, 2))
    com_night_duration_mavg = db.Column(db.Numeric(12, 2))
    com_night_connect_p = db.Column(db.Numeric(12, 2))
    com_night_duration_p = db.Column(db.Numeric(12, 2))
    com_day_connect = db.Column(db.Integer)
    com_days_call = db.Column(db.Integer)
    com_days_answer = db.Column(db.Integer)
    com_day_connect_mavg = db.Column(db.Numeric(12, 2))
    com_days_call_mavg = db.Column(db.Numeric(12, 2))
    com_days_answer_mavg = db.Column(db.Numeric(12, 2))
    com_hours_connect = db.Column(db.Integer)
    com_hours_call = db.Column(db.Integer)
    com_hours_answer = db.Column(db.Integer)
    com_hours_connect_davg = db.Column(db.Numeric(12, 2))
    com_hours_call_davg = db.Column(db.Numeric(12, 2))
    com_hours_answer_davg = db.Column(db.Numeric(12, 2))
    com_people_90 = db.Column(db.Integer)
    com_shutdown_total = db.Column(db.Integer)
    com_offen_connect = db.Column(db.Integer)
    com_offen_duration = db.Column(db.Integer)
    com_max_mobile_connect = db.Column(db.Integer)
    com_max_mobile_duration = db.Column(db.BigInteger)
    com_max_tel_connect = db.Column(db.Integer)
    com_max_tel_duration = db.Column(db.BigInteger)
    com_valid_all = db.Column(db.Integer)
    com_valid_mobile = db.Column(db.Integer)
    vs_valid_match = db.Column(db.Integer)
    vs_connect_match = db.Column(db.Integer)
    vs_duration_match = db.Column(db.Integer)
    vs_phone_match = db.Column(db.Integer)
    create_time = db.Column(db.DateTime)

    def getVsConnectMatchByUserIds(self, user_ids):
        '''
        获取af_detail
        '''
        if len(user_ids) == 0:
            return  []
        userid_str = '"' + '","'.join(str(val) for val in user_ids) + '"'
        sql = '''SELECT vs_connect_match from (select * from af_detail  WHERE user_id in(%s)  ORDER BY id DESC  ) tmp GROUP BY user_id ''' % userid_str
        res = db.session.execute(sql, bind=self.get_engine()).fetchall()
        vs_connect_match= []
        if len(res) > 0:
            for i in res:
                if i[0] is None:
                    vs_connect_match.append(0)
                else:
                    vs_connect_match.append(i[0])
        return vs_connect_match
