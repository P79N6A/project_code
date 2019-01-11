# -*- coding: utf-8 -*-
# 注意这里使用了阿里云本地库的通讯录
#
from lib.application import db
from model.base_model import BaseModel
from sqlalchemy import MetaData
from sqlalchemy import desc,and_
from datetime import datetime,timedelta

class ShopLoanUserLoan(db.Model, BaseModel):
    __bind_key__ = 'loan_shop'
    __tablename__ = 'loan_user_loan'
    metadata = MetaData()


    loan_id = db.Column(db.BigInteger, primary_key=True)
    parent_loan_id = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    number = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    settle_type = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    user_id = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    loan_no = db.Column(db.String(32))
    amount =  db.Column(db.Numeric(10, 2), nullable=False, server_default=db.FetchedValue())
    days = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    desc = db.Column(db.String(64))
    start_date = db.Column(db.DateTime)
    end_date = db.Column(db.DateTime)
    type = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    status = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    service_fee = db.Column(db.Numeric(10, 2), nullable=False, server_default=db.FetchedValue())
    interest_fee = db.Column(db.Numeric(10, 2), nullable=False, server_default=db.FetchedValue())
    contract = db.Column(db.String(20))
    contract_url = db.Column(db.String(128))
    withdraw_fee = db.Column(db.Numeric(10, 2), nullable=False, server_default=db.FetchedValue())
    coupon_amount = db.Column(db.Numeric(10, 2), nullable=False, server_default=db.FetchedValue())
    business_type = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    bank_id = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    come_from = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())
    modify_time = db.Column(db.DateTime)
    create_time = db.Column(db.DateTime)
    version = db.Column(db.Integer, nullable=False, server_default=db.FetchedValue())


    def getAllLoanByUids(self,user_ids):
        '''通讯录有过贷款的数量 '''
        if len(user_ids) == 0:
            return 0
        #select count(1) from yi_user_loan where user_id in (5419061,2968724,2697378,2616279)
        loanCounts = db.session.query(ShopLoanUserLoan).filter(ShopLoanUserLoan.user_id.in_(user_ids)).count()
        return loanCounts

    def getLoanedByUids(self,user_ids):
        '''通讯录有过放款的数量 '''
        if len(user_ids) == 0:
            return 0
        #借款状态:1 初始；2 驳回；3 出款中；4 审核通过；5 已放款；6 还款成功；7 逾期
        loanedStatus = [3,5,6,7,11]
        # select count(1) from yi_user_loan where user_id in (5419061,2968724,2697378,2616279) and  status in (8,9,11,12,13);
        res = db.session.query(ShopLoanUserLoan).filter(ShopLoanUserLoan.user_id.in_(user_ids),ShopLoanUserLoan.status.in_(loanedStatus)).count()
        return res

    def lateApplyDay(self,user_ids):
        '''通讯录最近一次申请借款日 '''
        if len(user_ids) == 0:
            return ''
        res = db.session.query(ShopLoanUserLoan.create_time.label('create_time')).filter(ShopLoanUserLoan.user_id.in_(user_ids)).order_by(desc(ShopLoanUserLoan.create_time)).first()
        if res :
            return res[0].strftime('%Y-%m-%d %H:%m:%S')
        else:
            return ''

    def getHistroyBadStatus(self, user_ids):
        '''通讯录中有过申请且历史最坏账单状态'''
        if len(user_ids) == 0:
            return 0
        # status = [3,7,8,9,11,12,13]
        # loan_status = [3,7]
        # datas = db.session.query(YiLoan.status,YiLoan.repay_time,YiLoan.end_date, YiUserLoanFlow.admin_id,YiUserLoanFlow.loan_status).outerjoin(YiUserLoanFlow,and_(YiLoan.loan_id == YiUserLoanFlow.loan_id,YiUserLoanFlow.loan_status in loan_status)).filter(YiLoan.user_id.in_(user_ids),YiLoan.status.in_(status)).limit(1000).all()
        userid_str = '"' + '","'.join(str(val) for val in user_ids) + '"'
        #sql = "select ul.status,ul.repay_time,ul.last_modify_time,ul.end_date,ulf.admin_id,ulf.loan_status from yi_user_loan as ul left join yi_user_loan_flows as ulf on ul.loan_id = ulf.loan_id and ulf.loan_status in (3,7) where ul.user_id in (%s) and ul.status in (3,7,8,9,11,12,13) LIMIT 1000 " % userid_str
        sql = "select ul.status,lr.modify_time as repay_time, ul.modify_time as last_modify_time, ul.end_date,ulf.admin_id,ulf.loan_status  from loan_user_loan as ul left join loan_repay as lr on (ul.loan_id=lr.loan_id) left join loan_overdue_loan as ol on (ol.loan_id=ul.loan_id) left join loan_user_loan_flows ulf on ulf.loan_id=ul.loan_id and ulf.loan_status in (3,7) where ul.user_id in (%s) and ul.status in (2,3,5) or ol.loan_status = 7 or lr.status in (4,6) limit 1000" % userid_str
        datas = db.session.execute(sql, bind=self.get_engine()).fetchall()
        if len(datas) == 0:
            return None
        else:
            returnData = []
            for data in datas:
                if data.status == 3:
                    returnData.append(-900)
                elif data.loan_status == 7 and data.admin_id and data.admin_id == -1:
                    returnData.append(-800)
                elif data.loan_status == 7 and data.admin_id and data.admin_id == -2:
                    returnData.append(-700)
                elif data.loan_status == 7 and data.admin_id and data.admin_id > 0:
                    returnData.append(-600)
                elif data.status == 9:
                    returnData.append(-500)
                elif data.status == 8:
                    date = data.repay_time if data.repay_time else data.last_modify_time
                    diffDay = 0
                    if data.end_date is not None and date is not None:
                        diffDay = (date - data.end_date).days
                        diffDay = diffDay-1
                    returnData.append(diffDay)
                elif data.status in [11, 12, 13]:
                    date = data.repay_time if data.repay_time else datetime.now()
                    diffDay = (date - data.end_date).days
                    returnData.append(diffDay)
                else:
                    returnData.append(-900)
            if len(returnData) > 0:
                realadl_tot_reject_num = len([dt for dt in returnData if dt in [-800, -900, -700, -600]])
                realadl_tot_freject_num = len([dt for dt in returnData if dt == -700])
                realadl_tot_sreject_num = len([dt for dt in returnData if dt == -800])
                realadl_tot_dlq14_num = len([dt for dt in returnData if dt > 14])
                tmp_num = len([dt for dt in returnData if dt not in [-800, -900, -700, -600, -500]])
                realadl_dlq14_ratio = 999999 if tmp_num == 0 else float('%.2f' % (realadl_tot_dlq14_num / tmp_num))
                return {
                    'realadl_tot_reject_num': realadl_tot_reject_num,
                    'realadl_tot_freject_num': realadl_tot_freject_num,
                    'realadl_tot_sreject_num': realadl_tot_sreject_num,
                    'realadl_tot_dlq14_num': realadl_tot_dlq14_num,
                    'realadl_dlq14_ratio': realadl_dlq14_ratio,
                    'history_bad_status': max(returnData),
                    'realadl_dlq14_ratio_denominator':tmp_num
                }
            else:
                return None


    def getSuccessNum(self, mobile, identity):
        sql = 'select count(1) as success_num from loan_user_loan ul inner join loan_user u on ul.user_id = u.user_id  where u.mobile = "%s" and u.identity = "%s"  and ul.status in (6) ' %( mobile, identity)
        count = db.session.execute(sql, bind=self.get_engine()).fetchone()
        return count[0]

    def getLastSuccLoan(self, user_id):
        returnData = {
            'last_end_date':'',
            'last_repay_time':'',
            'last_success_loan_days': 0
        }
        sql = "select ul.days, ul.end_date, lr.modify_time from loan_user_loan as ul left join loan_repay as lr on (ul.loan_id = lr.loan_id) where ul.user_id = %s and lr.status=6" % user_id
        data = db.session.execute(sql, bind=self.get_engine()).first()
        if data is not None:
            returnData['last_end_date'] = data.end_date.strftime('%Y-%m-%d %H:%m:%S')
            returnData['last_repay_time'] = data.modify_time.strftime('%Y-%m-%d %H:%m:%S')
            returnData['last_success_loan_days'] = data.days
        return returnData


    def getIsLoading(self, user_id):
        if not user_id:
            return 0

        loan_status = [3, 4, 5]
        where = and_(
            ShopLoanUserLoan.status.in_(loan_status),
            ShopLoanUserLoan.user_id == user_id
        )
        data = db.session.query(ShopLoanUserLoan).filter(where).limit(1).first()
        if data is None:
            return 0
        return 1


    def getApplyLoan(self, user_id):
        if not user_id:
            return 0
        where = and_(
            ShopLoanUserLoan.user_id == user_id,
            ShopLoanUserLoan.number == 0
        )
        data = db.session.query(ShopLoanUserLoan).filter(where).count()
        return data