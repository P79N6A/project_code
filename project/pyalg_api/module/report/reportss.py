# -*- coding: utf-8 -*-
'''
上数报告分析 fastjson 
@author: luchao 
'''
from datetime import datetime, timedelta
import re
import math
import json
import traceback
import pandas as pd
from chinese_calendar import is_holiday
from lib.logger import logger

class Reportss(object):

    def __init__(self, data):
        try:
            first = json.loads(data['bizContent'])
            self.biz_content = json.loads(first['bizContent'])
            self.data_report = json.loads(first['dataReport'])
        except Exception as e:
            logger.error('ssReport Json formal error')
            raise e

    def run(self):
        if self.biz_content is None or self.data_report is None:
            return None
        res = {
            'score': '',
            'rain_risk_reason': '',
            'rain_score': '',
            'consume_fund_index': '',
            'indentity_risk_index': '',
            'social_stability_index': '',
            'phone_register_month': ''
        }
        res['phone_register_month'] = self.mobileRegisterTime()
        for val in self.data_report:
            if val['labelName'] == 'score':
                res['score'] = None if val['value'] == 'NAN' else int(val['value'])
            if val['labelName'] == 'rain_risk_reason':
                res_reason = json.loads(val['value'])
                for key, reason in enumerate(res_reason):
                    if 'riskDescription' in reason.keys():
                        del (reason['riskDescription'])
                    if 'riskFactorName' in reason.keys():
                        del (reason['riskFactorName'])
                res['rain_risk_reason'] = json.dumps(res_reason)
            if val['labelName'] == 'rain_score':
                res['rain_score'] = val['value']
            if val['labelName'] == 'consume_fund_index':
                res['consume_fund_index'] = val['value']
            if val['labelName'] == 'indentity_risk_index':
                res['indentity_risk_index'] = val['value']
            if val['labelName'] == 'social_stability_index':
                res['social_stability_index'] = val['value']

        resolveInfo = self.__resolve()
        res.update(resolveInfo)
        return res

    def mobileRegisterTime(self):
        '''手机号码注册时长'''
        try:
            starttime = self.biz_content['operatorBasic']['extendJoinDt']
            if starttime:
                now = datetime.now()
                start = datetime.strptime(starttime, '%Y-%m-%d %H:%M:%S')
                spaceDays = (now - start).days
                return math.ceil(spaceDays / 30)
            else:
                return None
        except Exception as e:
            exstr = traceback.format_exc()
            logger.error('reportss : %s \n %s' % (e, exstr))

    def __resolve(self):
        self.report_data = {
            'credit_point': "",  # 积分
            'account_balance': "",  # 账户余额(分)
            'credit_level': "",  # 账户星级
            'pay_count': "",  # 缴费次数
            'pay_sum_fee': "",  # 缴费总金额
            'pay_mean_fee': "",  # 平均每次缴费金额
            'pay_max_fee': "",  # 最大缴费金额
            'bill_month_mean': "",  # 平均每月消费金额
            'bill_month_max': "",  # 每月最大消费金额
            'sms_count_fee': "",  # 付费信息总数
            'sms_total_fee': "",  # 短信通信费
            'sms_phone_count_nodup': "",  # 对方有效号码数量[去重统计]
            'sms_max_count_day': "",  # 每天最多接发短信条数[仅有效手机号]
            'net_count_4g': "",  # 4G上网次数
            'net_count_type': "",  # 上网方式统计
            'call_count_call_time_1min5min': "",  # 近3月通话时长1-5分钟的通话次数
            'call_count_call_time_5min10min': "",  # 近3月通话时长5-10分钟的通话次数
            'call_duration_holiday_3month_t_7': "",  # 近3月周末14:00-16:00通话时长
            'call_duration_workday_3month_t_7': "",  # 近3月工作日14:00-16:00通话时长
            'call_time_late_night_3month': "",  # 近3月深夜通话时长
            'call_time_late_night_6month': "",  # 近6月深夜通话时长
            'call_time_work_time_3month': "",  # 近3月工作时间通话时长
            'call_time_work_time_6month': "",  # 近6月工作时间通话时长
        }

        self.__setBasicInfo(self.biz_content.get('operatorBasic', None))
        self.__setPayInfo()
        self.__setBillInfo(self.biz_content.get('operatorBills', None))
        self.__setNetInfo(self.biz_content.get('operatorNet', None))
        self.__setSmsInfo(self.biz_content.get('operatorSms', None))
        self.__setCallInfo(self.biz_content.get('operatorVoices', None))
        return self.report_data

    # 设置通话报表基本信息
    def __setBasicInfo(self, basicDict):
        if basicDict is None or len(basicDict) == 0:
            logger.info('basicInfo is empty')
            return None
        try:
            creditPoint = basicDict.get('basicAllBonus', None)
            self.report_data['credit_point'] = "" if creditPoint is None else int(creditPoint)  # 积分
            accountBalance = basicDict.get('basicBalance', None)
            self.report_data['account_balance'] = "" if accountBalance is None else int(accountBalance)  # 账户余额(分)
            self.report_data['credit_level'] = self.__getCreditLevel(basicDict.get('basicStarLevel', ""))  # 账户星级
        except Exception as e:
            exstr = traceback.format_exc()
            logger.error('resolveBasicInfoError : %s \n %s' % (e, exstr))
        finally:
            return None

    # 获取用户星级
    def __getCreditLevel(self, basicStarLevel):
        try:
            if '五' in basicStarLevel:
                return 5
            if '四' in basicStarLevel:
                return 4
            if '三' in basicStarLevel:
                return 3
            if '二' in basicStarLevel:
                return 2
            if '一' in basicStarLevel:
                return 1
            return ""
        except:
            return ""

    # 设置通话报表缴费信息
    def __setPayInfo(self):
        self.report_data['pay_count'] = ""  # 缴费次数
        self.report_data['pay_sum_fee'] = ""  # 缴费总金额
        self.report_data['pay_mean_fee'] = ""  # 平均每次缴费金额
        self.report_data['pay_max_fee'] = ""  # 最大缴费金额

    # 设置通话报表缴费信息
    def __setBillInfo(self, billList):
        if billList is None or len(billList) == 0:
            logger.info('billList is empty')
            return None
        try:
            billPd = pd.DataFrame(billList, columns=['billMonthAmt'])
            billNum = len(billPd)
            if billNum == 0:
                return None
            billSum = billPd['billMonthAmt'].sum()
            billMax = billPd['billMonthAmt'].max()
            self.report_data['bill_month_mean'] = "" if math.isnan(billSum) else float('%.2f' % (billSum/billNum))  # 平均每月消费金额
            self.report_data['bill_month_max'] = "" if math.isnan(billMax) else int(billMax)  # 每月最大消费金额
        except Exception as e:
            exstr = traceback.format_exc()
            logger.error('resolveBillInfoError : %s \n %s' % (e, exstr))
        finally:
            return None

    # 设置通话报表网络信息
    def __setNetInfo(self, netList):
        if netList is None or len(netList) == 0:
            logger.info('netList is empty')
            return None
        try:
            netPd = pd.DataFrame(netList, columns=['netType'])
            netPd['cleanNetType'] = netPd['netType'].apply(self.__getUseNetType)
            self.report_data['net_count_4g'] = len(netPd[(netPd['cleanNetType'] == '4G')])
            self.report_data['net_count_type'] = len(netPd.drop_duplicates(subset='cleanNetType'))
        except Exception as e:
            exstr = traceback.format_exc()
            logger.error('resolveNetInfoError : %s \n %s' % (e, exstr))
        finally:
            return None

    # 获取用户网络类型
    def __getUseNetType(self, netType):
        try:
            if "无线" in netType:
                return "无线"
            if netType in ["WLAN", "WiFi", "Wifi", "宽带", "融合宽带"]:
                return "无线"
            if netType in ["2G 3G 4G", "2G 4G", "2G 4G 3G", "2G/3G", "2G/3G/4G", "2G/4G", "3G 4G", "3G/4G", "4G 2G", "CMNET(2G/4G)"]:
                return "2g/3g/4g"
            if "4G" in netType:
                return "4G"
            if "4g" in netType:
                return "4G"
            if "3G" in netType:
                return "3G"
            if "3g" in netType:
                return "3G"
            if "2G" in netType:
                return "2G"
            if "2g" in netType:
                return "2G"
            if netType != "":
                return "其他(未解析)"
            return ""
        except:
            return ""

    # 设置通话报表短信信息
    def __setSmsInfo(self, smsList):
        if smsList is None or len(smsList) == 0:
            logger.info('smsList is empty')
            return None
        try:
            SmsPd = pd.DataFrame(smsList, columns=['smsPhoneNum', 'smsFee', 'smsDate'])
            if len(SmsPd) == 0:
                return None
            self.report_data['sms_count_fee'] = len(SmsPd[(SmsPd.smsFee <= 300) & (SmsPd.smsFee > 0)])  # 付费信息总数
            self.report_data['sms_total_fee'] = int(SmsPd.smsFee[(SmsPd.smsFee <= 300) & (SmsPd.smsFee > 0)].sum())  # 短信通信费

            SmsPd['isMobile'] = SmsPd.smsPhoneNum.apply(self._checkPhone)
            SmsPd = SmsPd[SmsPd.isMobile]
            self.report_data['sms_phone_count_nodup'] = len(SmsPd.drop_duplicates(subset='smsPhoneNum'))  # 对方有效号码数量[去重统计]
            SmsPd['days'] = SmsPd.smsDate.str[:10]
            dayMax = SmsPd.groupby('days')['days'].count().max()
            self.report_data['sms_max_count_day'] = "" if math.isnan(dayMax) else int(dayMax)  # 每天最多接发短信条数[仅有效手机号]
        except Exception as e:
            exstr = traceback.format_exc()
            logger.error('resolveSmsInfoError : %s \n %s' % (e, exstr))
        finally:
            return None

    # 手机校验
    def _checkPhone(self, mobile):
        if mobile is None or len(mobile) != 11:
            return False
        mobile = str(mobile)
        if mobile >= '19000000000' or mobile <= '13000000000':
            return False
        return True

    # 设置通话报表电话信息
    def __setCallInfo(self, callList):
        if callList is None or len(callList) == 0:
            logger.info('smsList is empty')
            return None
        try:
            CallPd = pd.DataFrame(callList, columns=['voiceToNumber', 'voiceDate', 'voiceDuration'])
            CallPd = CallPd[-CallPd.voiceDate.isnull()]
            if len(CallPd) == 0:
                return None
            # 增加时列
            CallPd['hour'] = CallPd.voiceDate.str[11:13]
            # 增加是否是节假日列
            CallPd['isHoliday'] = pd.to_datetime(CallPd['voiceDate']).apply(lambda x: is_holiday(x))

            timeFormat = "%Y-%m-01"
            now = datetime.now()
            nowDate = now.strftime(timeFormat)
            last3 = (now + timedelta(days=-90)).strftime(timeFormat)
            last6 = (now + timedelta(days=-180)).strftime(timeFormat)
            # 1-5min通话检索条件
            min1to5 = ((CallPd.voiceDuration >= 60) & (CallPd.voiceDuration < 300))
            # 5-10min通话检索条件
            min5to10 = ((CallPd.voiceDuration >= 300) & (CallPd.voiceDuration < 600))
            # 近三个月的检索条件
            last3Where = ((CallPd.voiceDate >= last3) & (CallPd.voiceDate < nowDate))
            # 近六个月的检索条件
            last6Where = ((CallPd.voiceDate >= last6) & (CallPd.voiceDate < nowDate))
            # 工作日的检索条件
            workdayWhere = (CallPd.isHoliday == False)
            # 节假日的检索条件
            holidayWhere = (CallPd.isHoliday == True)
            # 中午[14-16点]的检索条件
            noonWhere = ((CallPd.hour >= '14') & (CallPd.hour < '16'))
            # 晚上[00-06点]的检索条件
            nightWhere = ((CallPd.hour >= '00') & (CallPd.hour < '06'))
            # 工作时间[08-18点]的检索条件
            worktimeWhere = ((CallPd.hour >= '08') & (CallPd.hour < '18'))

            self.report_data['call_count_call_time_1min5min'] = len(CallPd[last3Where & min1to5])  # 近3月通话时长1-5分钟的通话次数
            self.report_data['call_count_call_time_5min10min'] = len(CallPd[last3Where & min5to10])  # 近3月通话时长5-10分钟的通话次数
            holiday37Sum = CallPd.voiceDuration[last3Where & noonWhere & holidayWhere].sum()
            self.report_data['call_duration_holiday_3month_t_7'] = "" if math.isnan(holiday37Sum) else int(holiday37Sum)  # 近3月周末14:00-16:00通话时长
            workday37Sum = CallPd.voiceDuration[last3Where & noonWhere & workdayWhere].sum()
            self.report_data['call_duration_workday_3month_t_7'] = "" if math.isnan(workday37Sum) else int(workday37Sum)  # 近3月工作日14:00-16:00通话时长
            night3Sum = CallPd.voiceDuration[last3Where & nightWhere].sum()
            self.report_data['call_time_late_night_3month'] = "" if math.isnan(night3Sum) else int(night3Sum)  # 近3月深夜通话时长
            night6Sum = CallPd.voiceDuration[last6Where & nightWhere].sum()
            self.report_data['call_time_late_night_6month'] = "" if math.isnan(night6Sum) else int(night6Sum)  # 近6月深夜通话时长
            work3Sum = CallPd.voiceDuration[last3Where & worktimeWhere & workdayWhere].sum()
            self.report_data['call_time_work_time_3month'] = "" if math.isnan(work3Sum) else int(work3Sum)  # 近3月工作时间通话时长
            work6Sum = CallPd.voiceDuration[last6Where & worktimeWhere & workdayWhere].sum()
            self.report_data['call_time_work_time_6month'] = "" if math.isnan(work6Sum) else int(work6Sum)  # 近6月工作时间通话时长
        except Exception as e:
            exstr = traceback.format_exc()
            logger.error('resolveCallInfoError : %s \n %s' % (e, exstr))
        finally:
            return None
