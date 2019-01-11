from lib.logger import logger
import json
import re
import pandas as pd
from math import isnan,ceil
from chinese_calendar import is_workday
from datetime import datetime, timedelta

class Reportmh(object):
    def __init__(self, data, report_data):
        try:
            self.operator_json_data = report_data.get('operatorData')
            self.task_data = data.get('task_data', [])
            self.account_info = self.task_data.get('account_info',[])
            self.payment_info = self.task_data.get('payment_info',[])
        except Exception as e:
            logger.error("mh_report:report is fail: %s", e)
            raise e

    def __get_phone_register_month(self) :
        '''手机号码注册时长'''
        if self.operator_json_data is None:
            return None
        operator_data = json.loads(self.operator_json_data)
        basic_info = operator_data.get('basicInfo')
        if basic_info is None:
            return None
        innet_date = basic_info.get('inNetDate')
        if innet_date is None:
            return None
        now = datetime.now()
        try:  
            start = datetime.strptime(innet_date, "%Y年%m月%d日")
            start_year = int(start.year)
            if start_year == 1970:
                return None 
            interval_days = (now - start).days
            interval_month = ceil(interval_days/30)
            return interval_month
        except Exception as  e:
            logger.error("mh report mobile register month is fail: %s" % e)
            return None


    def run(self):
        # 充值信息
        payment_info = self.__get_pay_data()
        # 账单信息
        bill_info = self.__get_bill_data()
        #短信信息
        sms_info = self.__get_sms_data()
        # net info
        net_info = self.__get_net_data()
        # detail info
        detail_info = self.__get_detail_data()
         
        phone_register_month = self.__get_phone_register_month()

        return {
            'phone_register_month':phone_register_month,
            # 账户信息
            'credit_point':str(self.__get_credit_point()),
            'account_balance': str(self.__get_account_balance()),
            'credit_level': str(self.__get_credit_level()),
            # 充值信)息
            'pay_count': str(payment_info.get('pay_count','')),
            'pay_sum_fee': str(payment_info.get('pay_sum_fee','')),
            'pay_mean_fee': str(payment_info.get('pay_mean_fee','')),
            'pay_max_fee': str(payment_info.get('pay_max_fee','')),
            # 账单信)息
            'bill_month_mean': str(bill_info.get('bill_month_mean','')),
            'bill_month_max': str(bill_info.get('bill_month_max','')),
            # 短信信)息
            'sms_count_fee': str(sms_info.get('sms_count_fee','')),
            'sms_total_fee': str(sms_info.get('sms_total_fee','')),
            'sms_phone_count_nodup': str(sms_info.get('sms_phone_count_nodup','')),
            'sms_max_count_day': str(sms_info.get('sms_max_count_day','')),
            # 流量信)息
            'net_count_4g': str(net_info.get('net_count_4g','')),
            'net_count_type': str(net_info.get('net_count_type','')),
            # 通话详)单
            'call_count_call_time_1min5min': str(detail_info.get('call_count_call_time_1min5min','')),
            'call_count_call_time_5min10min': str(detail_info.get('call_count_call_time_5min10min','')),
            'call_duration_holiday_3month_t_7': str(detail_info.get('call_duration_holiday_3month_t_7','')),
            'call_duration_workday_3month_t_7': str(detail_info.get('call_duration_workday_3month_t_7','')),
            'call_time_late_night_3month': str(detail_info.get('call_time_late_night_3month','')),
            'call_time_late_night_6month': str(detail_info.get('call_time_late_night_6month','')),
            'call_time_work_time_3month': str(detail_info.get('call_time_work_time_3month', '')),
            'call_time_work_time_6month': str(detail_info.get('call_time_work_time_6month', '')),
        }
    def __get_detail_data(self):
        time_format = "%Y-%m-01"
        maxday = datetime.now()
        maxtime = maxday.strftime(time_format)
        last3 = (maxday + timedelta(days=-90)).strftime(time_format)
        last6 = (maxday + timedelta(days=-180)).strftime(time_format)
        # check report start
        call_info = self.task_data.get('call_info',[])
        if len(call_info) <= 0:
            return {}
        call_list = []
        [call_list.extend(d.get('call_record', [])) for d in call_info]
        if len(call_list) == 0:
            return {}
        call_df = pd.DataFrame(call_list)
        call_df['call_time'] = call_df[['call_time']].apply(pd.to_numeric, errors='ignore')
        # check_time
        last3_time = ((call_df.call_start_time >= last3) & (call_df.call_start_time < maxtime))
        last6_time = ((call_df.call_start_time >= last6) & (call_df.call_start_time < maxtime))
        # 近3月通话时长1-5分钟的通话次数
        call_count_call_time_1min5min = call_df.call_start_time[last3_time & (call_df.call_time < 300) & (call_df.call_time > 60)].count()
        # 近3月通话时长5-10分钟的通话次数
        call_count_call_time_5min10min = call_df.call_start_time[last3_time & (call_df.call_time > 300) & (call_df.call_time < 600)].count()
        # 近3月周末14:00-16:00通话时长
        call_hours = call_df.call_start_time.str[11:13]
        # check_work_holiday
        call_df = call_df[(-call_df['call_start_time'].isin(['未知']))]
        is_work = pd.to_datetime(call_df['call_start_time']).apply(lambda x: is_workday(x))
        is_holiday = is_work == False
        call_duration_holiday_3month_t_7 = call_df.call_time[last3_time & (call_hours >= '14') & (call_hours < '16') & is_holiday].sum()
         # 近3月工作时间14:00-16:00通话时长
        call_duration_workday_3month_t_7 = call_df.call_time[last3_time & (call_hours >= '14') & (call_hours < '16') & is_work].sum()
        # 近3月深夜通话时长
        call_time_late_night_3month = call_df.call_time[last3_time & (call_hours >= '00') & (call_hours < '06')].sum()
        # 近6月深夜通话时长
        call_time_late_night_6month = call_df.call_time[last6_time & (call_hours >= '00') & (call_hours < '06')].sum()
        # 近3月工作时间通话时长
        call_time_work_time_3month = call_df.call_time[last3_time & (call_hours >= '08') & (call_hours < '18') & is_work].sum()
        # 近六月工作时间通话时长
        call_time_work_time_6month = call_df.call_time[last6_time & (call_hours >= '08') & (call_hours < '18') & is_work].sum()
        return {
            'call_count_call_time_1min5min' : call_count_call_time_1min5min,
            'call_count_call_time_5min10min' : call_count_call_time_5min10min,
            'call_duration_holiday_3month_t_7' : call_duration_holiday_3month_t_7,
            'call_duration_workday_3month_t_7' : call_duration_workday_3month_t_7,
            'call_time_late_night_3month' : call_time_late_night_3month,
            'call_time_late_night_6month' : call_time_late_night_6month,
            'call_time_work_time_3month' : call_time_work_time_3month,
            'call_time_work_time_6month' : call_time_work_time_6month,
        }
    def __get_net_data(self):
        '''
        流量信息
        '''
        data_info = self.task_data.get('data_info',[])
        if len(data_info) <= 0 :
            return {}

        data_list = []
        [data_list.extend(d.get('data_record', [])) for d in data_info]
        if len(data_list) == 0:
            return {}
        data_df = pd.DataFrame(data_list)

        temp_data_type = data_df['data_type'].apply(self.clean_data_type)
        # 4G上网次数
        net_count_4g = temp_data_type[temp_data_type.str.contains('4G')].count()
        # 上网类型
        net_count_type = len(temp_data_type.groupby(temp_data_type))
        return {
            'net_count_4g' : net_count_4g,
            'net_count_type' : net_count_type,
        }

    # 数据清洗
    def clean_data_type(self, data_type):
        if data_type is None:
            return '其他(未解析)'
            
        if "无线" in data_type:
            return "无线"
            
        wifi_list = ["wlan", "WLAN", "WiFi", "Wifi", "宽带", "融合宽带"]
        if data_type in wifi_list:
            return '无线'

        gprs_list = ["2G 3G 4G", "2G 4G", "2G 4G 3G", "2G/3G", "2G/3G/4G", "2G/4G", "3G 4G", "3G/4G", "4G 2G",
                     "CMNET(2G/4G)"]
        if data_type in gprs_list:
            return '2g/3g/4g'

        if data_type is None or data_type == 'None':
            return '缺失'
        if type(data_type).__name__ == 'str':
            if data_type.count('4') > 0:
                return '4G'
            if data_type.count('3') > 0:
                return '3G'
            if data_type.count('2') > 0:
                return '2G'
        return '其他(未解析)'

    def __get_sms_data(self):
        '''
        短信详单
        '''
        sms_info = self.task_data.get('sms_info',[])
        if len(sms_info) <= 0:
            return {}
        sms_list = []
        [sms_list.extend(d.get('sms_record', [])) for d in sms_info]
        if len(sms_list) == 0:
            return {}
        sms_df = pd.DataFrame(sms_list)
        # sms_df['msg_cost'] = sms_df[['msg_cost']].astype(int)
        sms_df['day'] = sms_df.msg_start_time.str[:10]
        sms_df['msg_cost'] = sms_df[['msg_cost']].apply(pd.to_numeric, errors='ignore')
        # 付费信息个数
        sms_count_fee = sms_df.msg_cost[(sms_df.msg_cost > 0) & (sms_df.msg_cost < 300)].count()
        # sms_total_fee 信息费用
        sms_total_fee = sms_df.msg_cost[(sms_df.msg_cost > 0) & (sms_df.msg_cost < 300)].sum()
        # 过滤手机
        # mobile_pattern = self.__getReMobile()
        is_mobile = sms_df.msg_other_num.apply(self.__checkPhone)
        # sms_phone_count_nodup	对方号码数量(仅手机号且去重)
        sms_phone_count_nodup = sms_df.msg_other_num[is_mobile].drop_duplicates().count()
        # sms_max_count_day 一天之内最多信息个数（仅包含phone)
        sms_max_count_day = sms_df[is_mobile].groupby('day').count().max().msg_other_num
        return {
            'sms_count_fee': sms_count_fee,
            'sms_total_fee': sms_total_fee,
            'sms_phone_count_nodup' : '' if isnan(sms_phone_count_nodup) else sms_phone_count_nodup,
            'sms_max_count_day' : '' if isnan(sms_max_count_day) else sms_max_count_day,
        }

    def __get_bill_data(self):

        '''
        账单信息
        '''
        bill_info = self.task_data.get('bill_info',[])
        if len(bill_info) <= 0 :
            return {}
        bill_df = pd.DataFrame(bill_info)
        # 按月份去重并保留最后一个
        bill_df = bill_df.drop_duplicates(subset=['bill_cycle'], keep='last')
        bill_fee = bill_df['bill_fee'].apply(pd.to_numeric, errors='ignore')
        # 清洗负值账单
        bill_fee = bill_fee[bill_fee > 0]
        if len(bill_fee) <= 0:
            return {}
        # 平均每月消费金额
        bill_all = bill_fee.sum()
        bill_count = bill_fee.count()
        bill_month_mean = float('%.2f' % (bill_all / bill_count)) if bill_count > 0 else 0
        # 每月账单金额最大值
        bill_month_max = bill_fee.max()
        return {
            'bill_month_mean' : bill_month_mean,
            'bill_month_max' : bill_month_max,
        }

    def __get_pay_data(self):
        if len(self.payment_info) <= 0 :
            return {}
        pay_df = pd.DataFrame(self.payment_info)[['pay_fee']].apply(pd.to_numeric)
        # 清洗负值充值
        pay_df = pay_df[pay_df.pay_fee > 0]
        if len(pay_df) <= 0:
            return {}
        # 缴费次数
        pay_count = len(pay_df)
        # 缴费总金额
        pay_sum_fee = pay_df.pay_fee.sum()
        # 平均每次缴费金额
        pay_mean_fee = float('%.2f' % (pay_sum_fee / pay_count)) if pay_count > 0 else 0
        # 最大缴费金额
        pay_max_fee = pay_df.pay_fee.max()
        return  {
            'pay_count' : pay_count,
            'pay_sum_fee': pay_sum_fee,
            'pay_mean_fee' : pay_mean_fee,
            'pay_max_fee' : pay_max_fee
        }

    def __get_credit_level(self):
        credit_level_init= self.account_info.get('credit_level', 0) if self.account_info else 0
        credit_level_dict = {'一':1,'二':2,'三':3,'四':4,'五':5,'未知':0,'1':1,'2':2,'3':3,'4':4,'5':5}
        credit_level = credit_level_dict.get(credit_level_init,0)
        return credit_level

    def __get_account_balance(self):
        account_balance_init = self.account_info.get('account_balance', 0) if self.account_info else 0
        account_balance = 0 if account_balance_init == '-0' else account_balance_init
        return '' if account_balance is None else account_balance

    def __get_credit_point(self):
        credit_point = self.account_info.get('credit_point', 0) if self.account_info else 0
        return '' if credit_point is None else credit_point


    # 手机号并去重
    def __getReMobile(self):
        # 手机正则
        substr = '^1[2-9][0-9]\d{8}$'
        p = re.compile(substr, re.DOTALL)
        return p

    # 手机校验
    def __checkPhone(self, mobile):
        if mobile is None or len(mobile) != 11:
            return False
        mobile = str(mobile)
        if mobile >= '19000000000' or mobile <= '13000000000':
            return False
        return True