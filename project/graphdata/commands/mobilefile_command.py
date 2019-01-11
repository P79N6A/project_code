# -*- coding: utf-8 -*-

"""
跑文件
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

class MobilefileCommand(BaseCommand):
    def __init__(self):
        config = OrientdbConfig().getConfig()
        self.host = config['http_host']
        self.port = config['http_port']
        self.username = config['user']
        self.passwd = config['password']
        self.db_name = 'graph_phone'
        #self.db_name = 'graphdata1'
        self.ssdb_limit = 500 #ssdb通讯录分页，用于限制一次可以插入多少条
        self.class_name = "class_phone"  # 类名
        self.dege_name = "relation_ship"  # 边缘名

    """运行数据"""
    def runData(self, date_time):
        # 开始时间
        start = time.time()
        # 判断传入时间是否存在
        if not date_time:
            print("导入时间不能为空！")
            return None
        # 判断文件和目录是否存
        path_name = "./commands/mobile/"
        #==============================================
        # 判断文件是否存在
        mobile_file = path_name+date_time+'.txt'
        is_exists = os.path.exists(mobile_file)
        if not is_exists:
            print(date_time)
            print("导入文件不存在")
            return None
        # ==============================================
        # 判断通讯录是否存在
        mail_list_path = path_name + date_time + "/"
        is_mail_path = os.path.exists(mail_list_path)
        if not is_mail_path:
            print("通讯录文件夹不存在")
            return None
        #===============================================
        #连接orientdb数据库
        client = self.connectDb()
        #打开数据库
        open_databases = self.openData(client)
        #创建orientdb
        class_name = self.class_name  # 类名
        dege_name = self.dege_name  # 边缘名
        class_info = self.createClass(client, class_name)  # 创建类
        dege_info = self.createClass(client, dege_name, False)  # 创建边缘
        index_name = self.createIndex(client, class_name, "phone")  # 创建索引

        #===============================================
        #读取文件
        mobile_file = open(mobile_file, "r", encoding='utf-8')
        mobile_data = mobile_file.readlines()
        num = 0
        for phone in mobile_data:
            num += 1
            # 去除首位空格
            phone = phone.strip()
            # 去除尾部 \ 符号
            phone = phone.rstrip("\n")
            # 创建顶点
            phone_info = self.createVertex(client, class_name, phone)
            source = phone_info[0].rid
            #=============================================
            #读取通讯录
            mail_list_file = mail_list_path+phone+".json"
            # 判断通讯录是否存在
            is_mail_file = os.path.exists(mail_list_file)
            if is_mail_file:
                #============================================
                #读取文件
                mail_file = open(mail_list_file, "rb")
                mail_data = mail_file.readlines()
                if not mail_data:
                    continue
                message_list = list(set(json.loads(mail_data[0])))
                # 去掉当前手机号
                if phone in message_list:
                    message_list.remove(phone)
                # ssdb分页
                ssdb_limit = self.ssdb_limit;
                ssdb_cur_page = 0
                while ssdb_cur_page < len(message_list):
                    ssdb_offset = ssdb_cur_page + ssdb_limit
                    print("%s---%d---%d" % (phone, ssdb_cur_page, ssdb_offset))
                    page_message_list = message_list[ssdb_cur_page:ssdb_offset]
                    ssdb_cur_page += ssdb_limit
                    # print(page_message_list)
                    # 插入数据
                    insert_bool = self.insertValues(client, class_name, page_message_list)
                    # if not insert_bool:
                    #     pass
                        #continue
                    #获取插入数据
                    get_mobile = self.getMobileData(client, class_name, page_message_list)
                    target_list = ""
                    for target in get_mobile:
                        target_list += target._OrientRecord__rid + ","
                    target_list = target_list.strip("\,")
                    if not target_list:
                        continue
                    # 创建边缘
                    edge_bool = self.createEdgeMore(client, dege_name, source, target_list)
                mail_file.close()
        mobile_file.close()
        # 关闭数据库
        open_databases.db_close()
        # 关闭连接
        client.shutdown()
        print(num)
        # 结束时间
        end = time.time()
        # 耗时
        time_consuming = "耗时" + str(end - start)
        print(time_consuming)
        print("done!")


    """
    连接图数据库
    """
    def connectDb(self):
        client = pyorient.OrientDB(self.host, int(self.port))
        session_id = client.connect(self.username, self.passwd)
        return client

    """
    打开数据库
    """
    def openData(self, client):
        boolean = client.db_exists(self.db_name)
        if (boolean):
            # 连接数据库
            open_data = client.db_open(self.db_name, self.username, self.passwd)
        else:
            # 创建数据库
            client.db_create(self.db_name, pyorient.DB_TYPE_GRAPH, pyorient.STORAGE_TYPE_PLOCAL)
            # 连接数据库
            open_data = client.db_open(self.db_name, self.username, self.passwd)
        return open_data
    """
    创建类
        True 顶点
        False 边缘
    """
    def createClass(self, client, class_name, type=True):
        if type:
            ex_type = "V"
        else:
            ex_type = "E"

        try:
            # 查看类是否存在
            create_class = client.command("CREATE CLASS %s extends %s" % (class_name, ex_type))
            # print(create_class)
            if ex_type == 'V':
                # 创建属性
                client.command("CREATE PROPERTY %s.phone STRING" % class_name)  # 手机号
        except pyorient.exceptions.PyOrientSchemaException as f:
            pass

    """
    创建索引
    """
    def createIndex(self, client, class_name, v_value):
        # 查找索引是否存在不存在创建
        index_list = client.command("select clusters from (select expand(indexes) from metadata:indexmanager)")
        for index_name in index_list:
            name = index_name._OrientRecord__o_storage['clusters']
            for class_n in name:
                if class_n == class_name:
                    return True

        # 创建索引
        client.command("create index %s ON %s(%s) UNIQUE " % (class_name + v_value, class_name, v_value))

    """
    创建顶点
    """
    def createVertex(self, client, class_name, value):
        v_info = self.getVertext(client, class_name, value)
        if not v_info:
            client.command('create vertex %s set phone="%s"' % (class_name, value))
            v_info = self.getVertext(client, class_name, value)
        return v_info

    """
    查找顶点
    """
    def getVertext(self, client, class_name, value):
        return client.command('select @rid from %s where phone="%s" ' % (class_name, value))

    """
    插入数据
    """
    def insertValues(self, client, class_name, v_values):
        diff_mobile = self.getMobileAll(client, class_name, v_values)
        # 判断是否存在
        if not diff_mobile:
            return None
        str = ''
        for mobile in diff_mobile:
            str += "('" + mobile + "'),"
        str = str.strip("\,")
        # 批量插入
        insert_str = "insert into %s (phone) values %s" % (class_name, str)
        insert_data = client.command(insert_str)
        return insert_data

    """
    批量获取数据
    """
    def getMobileAll(self, client, class_name, v_values):
        # 批量获取数据
        get_sql = "select from %s where phone in %s" % (class_name, v_values)
        get_data = client.command(get_sql)
        mobile_data = []
        for mobile in get_data:
            mobile_data.append(mobile.phone)
        # ===============================
        # v_values中有而mobile_data中没有的
        mobile_data = list(set(v_values).difference(set(mobile_data)))
        return mobile_data

    #获取数据
    def getMobileData(self, client, class_name, v_values):
        get_sql = "select from %s where phone in %s" % (class_name, v_values)
        get_data = client.command(get_sql)
        return get_data
    """
    创建边缘批量
    """
    def createEdgeMore(self, client, edge_name, source, target):
        edget_sql = "CREATE EDGE %s from %s TO [%s] " % (edge_name, source, target)
        ret = client.command(edget_sql)
        return ret