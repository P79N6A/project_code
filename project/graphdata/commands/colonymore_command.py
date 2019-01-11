# -*- coding: utf-8 -*-
'''
功能说明：
    1.通讯录及通话详单手机号码节点及关系边的OrientDB存储
    2.IP地址作为关系边的提取及OrientDb存储，IP地址类型：用户注册IP，登入IP
    注：OrientDB中做三种关系边的Class：通讯录，通话详单，IP
    需要按装：pip install clickhouse_driver
'''
import time

from .base_command import BaseCommand
from clickhouse_driver.client import Client
from module.yiyiyuan.model import YiUser
from datetime import datetime, timedelta

class ColonymoreCommand(BaseCommand):
    # 数据库类对象
    oUser = ''

    def __init__(self):
        self.oUser = YiUser()

    #
    def runData(self, start_time, end_time):
        #时间处理
        start_time = datetime.strptime(start_time + " 00:00:00", '%Y-%m-%d %H:%M:%S')
        end_time = datetime.strptime(end_time + " 23:59:59", '%Y-%m-%d %H:%M:%S')
        #数据库操作
        self.userOperation(start_time, end_time)


    def userOperation(self, start_time, end_time):
        # 查找有多少条用户
        total = self.oUser.getMobileCount(start_time, end_time)
        print(total)


    '''
    集群图数据库配置
    '''
    def orientdbConfig(self):
        orientdb_config = {
            "http_host"                     : "47.96.99.175",
            "http_port"                     : "2424",
            "user"                          : "root",
            "password"                      : "Root_xhh123#@!",
            "db_name"                       : "test",  #数据库名
            "class_name"                    : "class_phone",  #类名
            "ip_edge_name"                  : "ip_relation_ship", #ip边缘名
            "specifications_edge_name"      : "specifications_relation_ship",  # 详单边缘名
            "maillist_edge_name"            : "maillist_relation_ship",  # 通讯录边缘名
        }
        return orientdb_config

    '''
    通讯录ssdb配置
    '''
    def maillistSsdbConfig(self):
        ip_data = {
            "master": "47.93.121.71:8888",
            "slave": {"47.93.121.71:8888": 4, "47.93.121.71:8888": 6},
        }
        return ip_data

    '''
    通话详单ssdb配置
    '''
    def specificationsConfig(self):
        ip_data = {
            "master": "47.93.121.71:8888",
            "slave": {"47.93.121.71:8888": 4, "47.93.121.71:8888": 6},
        }
        return ip_data

    '''
    clickhouse配置
    '''
    def clickhouseConfig(self):
        click_config = {
            "http"      : "47.93.121.71",
            "port"      : "9001",
            "user"      : "default",
            "pwd"       : "6lYaUiFi",
            "database"  : "",
        }
        return click_config

    '''
    clickhouse连接
    '''
    def clickhouseConnection(self):
        config = self.clickhouseConfig()
        # 连接数据库
        client = Client(config["http"], config["port"], config["database"], config["user"], config["pwd"])
        a = client.execute("show tables")
        print(a)


