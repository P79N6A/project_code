# -*- coding: utf-8 -*-
import json
import os
import pandas as pd
import pyorient
import pdb
import random
import re
from datetime import datetime, timedelta


from lib.logger import logger
from .base_command import BaseCommand
from model.open import OpenJxlStat
from model.antifraud import AfWsm
from module.yiyiyuan import YiUserRemitList
from module.yiyiyuan import YiFavoriteContact
from module.detail import Detail
from lib.ssdb_config import SsdbConfig

class OrientCommand(BaseCommand):
    def __init__(self):
        super(OrientCommand, self).__init__()
        self.client = None

    # ORIENT DB
    def runorient(self,start_time = None , end_time = None):
        # 连接数据库
        self.contactOrient()
        return True


