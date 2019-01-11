# -*- coding:utf-8 -*-

'''
时间区间下载一天通过ip找到的手机号
'''

import time

from .base_command import BaseCommand
from datetime import datetime, timedelta
from .ipdown_command import IpdownCommand

class IpdownSeCommand(BaseCommand):

    def runData(self, start_time, end_time):
        max_day = (datetime.strptime(end_time, '%Y-%m-%d') - datetime.strptime(start_time, '%Y-%m-%d')).days
        #max_day = 303
        cur_data = 0
        while cur_data <= max_day:
            dayA = datetime.strptime(start_time, '%Y-%m-%d')
            delta = (dayA + timedelta(days=cur_data)).strftime("%Y-%m-%d")
            command = IpdownCommand()
            command.runData(delta)
            cur_data += 1
