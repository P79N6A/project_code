# -*- coding: utf-8 -*-

"""
批量运行文件导入
"""
import json
import os
import pandas as pd
import pyorient
import pdb
import random
import re
import math
import time
from module.yiyiyuan.model import YiUser
from module.yiyiyuan.model import YiAddressList
from util.ssdb import SsdbObject


from lib.logger import logger
from .base_command import BaseCommand
from lib.orientdb_config import OrientdbConfig
from datetime import datetime, timedelta

from .mobilefile_command import MobilefileCommand

class FileCommand(BaseCommand):
    def __init__(self):
        pass

    """运行数据"""
    def runData(self, start_time = None , end_time = None):
        max_day = (datetime.strptime(end_time, '%Y-%m-%d') - datetime.strptime(start_time, '%Y-%m-%d')).days
        #max_day = 303
        cur_data = 0
        while cur_data <= max_day:
            dayA = datetime.strptime(start_time, '%Y-%m-%d')
            delta = (dayA+timedelta(days=cur_data)).strftime("%Y-%m-%d")
            command = MobilefileCommand()
            command.runData(delta)
            cur_data += 1



