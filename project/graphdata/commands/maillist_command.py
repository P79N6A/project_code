# -*- coding: utf-8 -*-

"""
下载一亿用户和ssdb通讯录用户
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
from datetime import datetime, timedelta
from module.yiyiyuan.model import YiUser
from module.yiyiyuan.model import YiAddressList
from util.ssdb import SsdbObject


from lib.logger import logger
from .base_command import BaseCommand
from lib.orientdb_config import OrientdbConfig
from datetime import datetime, timedelta

class MaillistCommand(BaseCommand):
    def __init__(self):
        config = OrientdbConfig().getConfig()
        self.host = config['http_host']
        self.port = config['http_port']
        self.username = config['user']
        self.passwd = config['password']
        self.db_name = 'graph_data'
        #self.db_name = 'graphdata1'
        self.limit = 500 #用于手机号分页
        self.oUser = YiUser()

    """运行数据"""
    def runData(self, start_time = None , end_time = None):
        # 1. 判断开始和结束时间，如果不存在就返回当前时间
        last_day = datetime.now() + timedelta(days=-1)
        if start_time is None:
            start_time = last_day.strftime('%Y-%m-%d 00:00:00')
        if end_time is None:
            end_time = last_day.strftime('%Y-%m-%d 23:59:59')
        # =======================================
        #连接ssdb
        ssdb = SsdbObject(False)
        ssdb_resources = ssdb.ssdbConnection()
        #开始时间
        start = time.time()
        # 连接一亿元用户表
        #查找有多少条用户
        total = self.oUser.getMobileCount(start_time, end_time)
        limit = self.limit
        pages = math.ceil(total / limit);
        cur_page = 0;
        num = 0
        #目录
        path_name = "./commands/mobile/"
        path_a = self.mkdir(path_name)
        #打开文件
        mobile_filename = path_name+start_time+".txt"
        fo = open(mobile_filename, "w+")
        while cur_page < pages:
            #分页到出数据
            offset = cur_page * limit
            mobile_data = self.oUser.getMobileUser(start_time, end_time, offset, limit)
            mobile_str = ''
            for mobile in mobile_data:
                #单条词ssdb数据
                phone = mobile.mobile.strip()
                print("user_mobile=%s" % phone)
                if not phone:
                    continue
                ssdb_message_str = ssdb_resources.get(phone)
                if ssdb_message_str:
                    ssdb_path_name = path_name + start_time+"/"
                    path_s = self.mkdir(ssdb_path_name)
                    ssdb_filename = ssdb_path_name + phone + ".json"
                    fo_ssdb = open(ssdb_filename, "wb+")
                    fo_ssdb.write(ssdb_message_str)
                mobile_str += phone + "\n"
            fo.write(mobile_str)
            num += 1
            # 自增
            cur_page += 1
        print(num)
        end = time.time()
        #耗时
        time_consuming = "耗时" + str(end-start)
        print(time_consuming)
        #fo.write(time_consuming)
        fo.close()
        print("done!")
        #end_time = time.time()


    """
    创建目录
    """
    def mkdir(self, path):
        # 去除首位空格
        path=path.strip()
        # 去除尾部 \ 符号
        path=path.rstrip("\\")
        # 判断路径是否存在
        # 存在     True
        # 不存在   False
        isExists=os.path.exists(path)
        # 判断结果
        if not isExists:
            # 如果不存在则创建目录
            # 创建目录操作函数
            os.makedirs(path)
            print(path+' 创建成功')
            return True
        else:
            # 如果目录存在则不创建，并提示目录已存在
            print(path+' 目录已存在')
            return False