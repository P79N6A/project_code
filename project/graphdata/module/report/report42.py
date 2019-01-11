# -*- coding: utf-8 -*-
'''
通话报告分析
@author: jin
'''
import pandas as pd
import re


class Report42(object):

    def __init__(self, data):
        self.originData = data

        # 行为报告
        behavior_check = self.originData["JSON_INFO"]['behavior_check']
        self.pdBehavior = pd.DataFrame(behavior_check)

        # 应用报告
        application_check = self.originData["JSON_INFO"]['application_check']
        self.pdApplication = pd.DataFrame(application_check)

    def chkBeHavior(self, point):
        '''检测行为'''
        ismatch = self.chkBehaviorResult(point, '无通话记录')
        return 0 if ismatch else 1

    def chkBehaviorResult(self, point, result):
        res = self.getBehaviorResult(point)
        return 1 if res == result else 0

    def getBehaviorResult(self, point):
        '''检测报告是否存在字符串'''
        pdSub = self.pdBehavior
        res = pdSub[pdSub.check_point == point]
        if len(res.index) == 0:
            return ''
        else:
            return res.iloc[0].result

    def getBehaviorEvidence(self, point):
        '''同上'''
        pdSub = self.pdBehavior
        res = pdSub[pdSub.check_point == point]
        if len(res.index) == 0:
            return ''
        else:
            return res.iloc[0].evidence

    def getUseMonth(self):
        ''' 获取号码使用时长 #u'号码使用时间')'''
        evidence = self.getBehaviorEvidence('phone_used_time')
        m = re.search('(\d+)个月', evidence)
        if m:
            return int(m.group(1))
        else:
            return None

    def getShutDownNum(self):
        # 获取关机时长(天) u'关机情况'
        result = self.getBehaviorResult('phone_silent')
        m = re.search('(\d+)天无通话记录', result)
        if m:
            return int(m.group(1))
        else:
            return 0

    def getNightPercent(self):
        # u'夜间活动情况'
        evidence = self.getBehaviorEvidence('contact_night')
        m = re.search('晚间活跃频率占全天的(.*)%', evidence)
        if m:
            return float(m.group(1))
        else:
            return 0.0

    def getBehaviorPoint(self, check_point):
        '''获取某一 behavior_check 的 check_point '''
        pdSub = self.pdBehavior
        res = pdSub[pdSub.check_point == check_point]
        if len(res.index) == 0:
            return None
        else:
            return res.iloc[0]

    def getAppPoint(self, app_point):
        '''获取某一 application_check 的 app_point '''
        pdSub = self.pdApplication
        res = pdSub[pdSub.app_point == app_point]
        if len(res.index) == 0:
            return None
        else:
            return res.iloc[0]

    def run(self):
        # start behavior_check
        # 87 出现澳门电话通话情况
        # 88 出现与110电话通话记录
        # 89 出现与120电话通话记录
        # 90 多次出现与律师电话通话记录
        # 91 多次出现与法院电话通话记录
        report_aomen = self.chkBeHavior('contact_macao')
        report_110 = self.chkBeHavior('contact_110')
        report_120 = self.chkBeHavior('contact_120')
        report_lawyer = self.chkBeHavior('contact_lawyer')
        report_court = self.chkBeHavior('contact_court')
        report_use_time = self.getUseMonth()
        report_shutdown = self.getShutDownNum()
        report_night_percent = self.getNightPercent()

        # u'贷款类号码联系情况')
        report_loan_connect = self.getBehaviorResult('contact_loan')
        # -end  behavior_check

        # 黑名单没有对应的位置, 现在全部取消
        report_fcblack_idcard = 0
        report_fcblack_phone = 0
        report_fcblack = 0

        # 个人信息
        info = self.getAppPoint('cell_phone')

        # 姓名是否与运营商数据匹配
        check_name = info.check_points.get('check_name', '')
        m = re.search('用户姓名与运营商提供的姓名\[.*\]匹配成功', check_name)
        report_name_match = 1 if m else 0

        # 实名认证
        reliability = info.check_points.get('reliability')
        report_reliability = 1 if reliability == '实名认证' else 0

        # 运营商
        report_operator_name = info.check_points.get('website')

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
