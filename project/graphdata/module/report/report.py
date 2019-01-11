# -*- coding: utf-8 -*-
'''
通话报告分析
聚信立报告有4.1和4.2版本
@author: jin
'''
from .report41 import Report41
from .report42 import Report42


class Report(object):

    def __init__(self, data):
        version = data["JSON_INFO"]['report']['version']
        if version == '4.1':
            self.oReport = Report41(data)
        elif version == '4.2':
            self.oReport = Report42(data)
        else:
            self.oReport = None

    def run(self):
        if self.oReport is None:
            return None
        return self.oReport.run()
