# -*- coding: utf-8 -*-
'''
通话报告分析
@author: jin
'''
import pandas as pd
import re


class Report41(object):

    def __init__(self, data):
        self.originData = data
        behavior_check = self.originData["JSON_INFO"]['behavior_check']
        self.pdBehavior = pd.DataFrame(behavior_check)

        self.data_source = self.originData["JSON_INFO"]['data_source'][0]
        # 行为报告
        application_check = self.originData["JSON_INFO"]['application_check']
        self.pdApplication = pd.DataFrame(application_check)

    def chkBeHavior(self, point):
        '''检测行为'''
        ismatch = self.chkResult(self.pdBehavior, point, '无通话记录')
        if ismatch == 1:
            return 0

        ismatch = self.chkResult(self.pdBehavior, point, '无数据')
        if ismatch == 1:
            return 0
        return 1

    def chkResult(self, pdSub, point, result):
        res = self.getResult(pdSub, point)
        return 1 if res == result else 0

    def getResult(self, pdSub, point):
        '''检测报告是否存在字符串'''
        res = pdSub[pdSub.check_point == point]
        if len(res.index) == 0:
            return ''
        else:
            return res.iloc[0].result

    def getEvidence(self, pdSub, point):
        '''同上'''
        res = pdSub[pdSub.check_point == point]
        if len(res.index) == 0:
            return ''
        else:
            return res.iloc[0].evidence

    def getUseMonth(self, pdBehavior):
        ''' 获取号码使用时长'''
        evidence = self.getEvidence(pdBehavior, '号码使用时间')
        m = re.search('号码使用了(\d+)个月', evidence)
        if m:
            return int(m.group(1))
        else:
            return None

    def getShutDownNum(self, pdBehavior):
        # 获取关机时长(天)
        result = self.getResult(pdBehavior,  '关机情况')
        m = re.search('关机共(\d+)天', result)
        if m:
            return int(m.group(1))
        else:
            return 0

    def getNightPercent(self, pdBehavior):
        evidence = self.getEvidence(pdBehavior, '夜间活动情况')
        m = re.search('晚间活跃频率占全天的(.*)%', evidence)
        if m:
            return float(m.group(1))
        else:
            return 0.0

    def run(self):
        # 87 出现澳门电话通话情况
        # 88 出现与110电话通话记录
        # 89 出现与120电话通话记录
        # 90 多次出现与律师电话通话记录
        # 91 多次出现与法院电话通话记录
        report_aomen = self.chkBeHavior('澳门电话通话情况')
        report_110 = self.chkBeHavior('110话通话情况')
        report_120 = self.chkBeHavior('120话通话情况')
        report_lawyer = self.chkBeHavior('律师号码通话情况')
        report_court = self.chkBeHavior('法院号码通话情况')
        report_use_time = self.getUseMonth(self.pdBehavior)
        report_shutdown = self.getShutDownNum(self.pdBehavior)
        report_night_percent = self.getNightPercent(self.pdBehavior)
        report_loan_connect = self.getResult(self.pdBehavior, '贷款类号码联系情况')

        # 行为检测, 金融黑名单中
        pdApplication = self.pdApplication
        report_name_match = self.chkResult(pdApplication, '姓名是否与运营商数据匹配', '匹配成功')

        notblack = self.chkResult(pdApplication, '申请人姓名+身份证是否出现在金融服务类机构黑名单', '未出现')
        report_fcblack_idcard = 0 if notblack else 1

        notblack = self.chkResult(pdApplication, '申请人姓名+手机号码是否出现在金融服务类机构黑名单', '未出现')
        report_fcblack_phone = 0 if notblack else 1

        report_fcblack = 1 if report_fcblack_idcard == 1 or report_fcblack_phone == 1 else 0

        # 个人信息
        report_operator_name = self.data_source.get('name', '')
        report_reliability = self.data_source.get('reliability', '') == '实名认证'
        report_reliability = 1 if report_reliability else 0

        return {
            'report_aomen': int(report_aomen),
            'report_110': int(report_110),
            'report_120': int(report_120),
            'report_lawyer': int(report_lawyer),
            'report_court': int(report_court),
            'report_use_time': report_use_time,
            'report_shutdown': int(report_shutdown),
            'report_night_percent': float('%.2f' % report_night_percent),
            'report_loan_connect': report_loan_connect,

            'report_name_match': int(report_name_match),
            'report_fcblack_idcard': int(report_fcblack_idcard),
            'report_fcblack_phone': int(report_fcblack_phone),
            'report_fcblack': int(report_fcblack),

            'report_operator_name': report_operator_name,
            'report_reliability': int(report_reliability),
        }
