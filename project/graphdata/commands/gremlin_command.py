# -*- coding: utf-8 -*-

"""
    安装包 pip install gremlinpython
"""
import json
import os
import pandas as pd
import pyorient
import pdb
import random
import re
import math
from datetime import datetime, timedelta
from module.yiyiyuan.model import YiUser
from module.yiyiyuan.model import YiAddressList
from util.ssdb import SsdbObject
from lib.logger import logger
from .base_command import BaseCommand
from lib.orientdb_config import OrientdbConfig
from datetime import datetime, timedelta


class GremlinCommand(BaseCommand):
    def __init__(self):
        config = OrientdbConfig().getConfig()
        self.host = config['http_host']
        self.port = config['http_port']
        self.username = config['user']
        self.passwd = config['password']
        self.db_name = 'graph_data1'
        #self.db_name = 'graphdata1'



    """运行数据"""
    def runData(self):
        pass