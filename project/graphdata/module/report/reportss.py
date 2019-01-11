# -*- coding: utf-8 -*-
'''
上数报告分析 fastjson 
@author: luchao 
'''
from datetime import datetime
import re
import math
import json
import traceback

class Reportss(object):

    def __init__(self, data):
        first = json.loads(data['bizContent'])
        self.biz_content = json.loads(first['bizContent'])
        self.data_report = json.loads(first['dataReport'])

    def run(self):
        if self.biz_content is None or self.data_report is None:
            return None
        res = {
            'score':None,
            'rain_risk_reason':'',
            'rain_score':'',
            'consume_fund_index':'',
            'indentity_risk_index':'',
            'social_stability_index':'',
            'phone_register_month':None
        }
        res['phone_register_month'] = self.mobileRegisterTime()
        for val in self.data_report:
            if val['labelName'] == 'score' :
                res['score'] = None if val['value'] == 'NAN' else int(val['value'])
            if val['labelName'] == 'rain_risk_reason':
                res_reason = json.loads(val['value'])
                for key,reason in enumerate(res_reason):
                    if 'riskDescription' in reason.keys():
                        del(reason['riskDescription'])
                    if 'riskFactorName' in reason.keys():
                        del(reason['riskFactorName'])
                res['rain_risk_reason'] = json.dumps(res_reason)
            if val['labelName'] == 'rain_score' :
                res['rain_score'] = val['value']
            if val['labelName'] == 'consume_fund_index' :
                res['consume_fund_index'] = val['value']
            if val['labelName'] == 'indentity_risk_index' :
                res['indentity_risk_index'] = val['value']
            if val['labelName'] == 'social_stability_index' :
                res['social_stability_index'] = val['value']
        return  res
    
    def mobileRegisterTime(self) :
        '''手机号码注册时长'''
        try:
            starttime = self.biz_content['operatorBasic']['extendJoinDt']
            if starttime :
                now = datetime.now()
                start = datetime.strptime(starttime, '%Y-%m-%d %H:%M:%S')
                spaceDays = (now - start).days
                return math.ceil(spaceDays/30)
            else:
                return None;
        except Exception as e:
            exstr = traceback.format_exc()
            raise Exception("reportss: %s \n %s" % (e, exstr))

        
            

