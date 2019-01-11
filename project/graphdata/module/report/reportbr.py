# -*- coding: utf-8 -*-
'''
百荣报告分析 json
@author: luchao
'''
from datetime import datetime
import re
import math
import json
import traceback

class Reportbr(object):

    def __init__(self, data):
        self.oReport = data

    def run(self):
        if self.oReport is None:
            return None
        mobile_register_time = self.mobileRegisterTime()
        return {
            'phone_register_month':mobile_register_time
        }
    
    def mobileRegisterTime(self) :
        '''手机号码注册时长'''
        try:
            first = self.oReport['operatorData']
            second = json.loads(first)
            starttime = second['basicInfo']['inNetDate']
            m = re.search('(\d+)年(\d+)月(\d+)日', starttime)
            if m and m.group(1) and m.group(2) and m.group(3):
                start = datetime(int(m.group(1)),int(m.group(2)),int(m.group(3)))
                now = datetime.now()
                spaceDays = (now - start).days
                return math.ceil(spaceDays/30)
            else:
                return None;
        except Exception as e:
            exstr = traceback.format_exc()
            raise Exception("reportbr: %s \n %s" % (e, exstr))

            

