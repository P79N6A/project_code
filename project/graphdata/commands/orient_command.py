# -*- coding: utf-8 -*-
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

class OrientCommand(BaseCommand):
    def __init__(self):
        config = OrientdbConfig().getConfig()
        self.host = config['http_host']
        self.port = config['http_port']
        self.username = config['user']
        self.passwd = config['password']
        self.db_name = 'graph_phone'
        # self.db_name = 'graphdata1'
        self.class_name = "class_phone"  # 类名
        self.dege_name = "relation_ship"  # 边缘名
        self.ssdb_limit = 500 #ssdb通讯录分页，用于限制一次可以插入多少条
        self.limit = 500 #用于手机号分页

    """运行数据"""
    def runData(self, start_time = None , end_time = None):
        # self.example();
        # return None
        #=======================================
        # 开始时间
        start = time.time()
        #1. 判断开始和结束时间，如果不存在就返回当前时间
        last_day = datetime.now() + timedelta(days=-1)
        if start_time is None:
            start_time = last_day.strftime('%Y-%m-%d 00:00:00')
        if end_time is None:
            end_time = last_day.strftime('%Y-%m-%d 23:59:59')
        # =======================================
        #2. 连接ssdb
        ssdb = SsdbObject(False)
        ssdb_resources = ssdb.ssdbConnection()
        # print(ssdb_resources)
        # =======================================
        #3. 连一亿元库用户表
        oUser = YiUser()
        # 5.连接orientdb数据库
        client = self.connectDb()
        # 6.打开数据库
        open_databases = self.openData(client)
        # 7.创建类和边缘
        class_name = self.class_name  # 类名
        dege_name = self.dege_name  # 边缘名
        class_info = self.createClass(client, class_name)  # 创建类
        dege_info = self.createClass(client, dege_name, False)  # 创建边缘
        index_name = self.createIndex(client, class_name, "phone")  # 创建索引
        # =======================================
        # 5.查找有多少条用户
        total = oUser.getMobileCount(start_time, end_time)
        # 6.分页处理
        limit = self.limit
        pages = math.ceil(total / limit);
        cur_page = 0;
        num = 0
        while cur_page < pages:
            offset = cur_page * limit
            mobile_data = oUser.getMobileUser(start_time, end_time, offset, limit)
            for data in mobile_data:
                phone = data.mobile.strip()
                # 查找关系数据
                get_data = self.getData(client, class_name, phone)
                # 创建顶点
                phone_info = self.createVertex(client, class_name, phone)
                source = phone_info[0].rid
                #========================================
                # 9.ssdb找到用户通讯录
                if not phone:
                    continue
                message_str = ssdb_resources.get(phone)
                message_list = []
                if message_str is not None:
                    message_list = list(set(json.loads(message_str)))
                # 去掉当前手机号
                if phone in message_list:
                    message_list.remove(phone)
                #ssdb分页
                ssdb_limit = self.ssdb_limit;
                ssdb_cur_page = 0
                while ssdb_cur_page < len(message_list):
                    ssdb_offset = ssdb_cur_page + ssdb_limit
                    print("%s---%d---%d" % (phone, ssdb_cur_page, ssdb_offset))
                    page_message_list = message_list[ssdb_cur_page:ssdb_offset]
                    #print(page_message_list)
                    #return None
                    ssdb_cur_page += ssdb_limit
                    # print(page_message_list)
                    # 插入数据
                    insert_bool = self.insertValues(client, class_name, page_message_list)
                    # if not insert_bool:
                    #     pass
                    # continue
                    # 获取插入数据
                    get_mobile = self.getMobileData(client, class_name, page_message_list)
                    target_list = ""
                    for target in get_mobile:
                        target_list += target._OrientRecord__rid + ","
                    target_list = target_list.strip("\,")
                    if not target_list:
                        continue
                    # 创建边缘
                    edge_bool = self.createEdgeMore(client, dege_name, source, target_list)
                    num += 1
                    # # print(page_message_list)
                    # # 插入数据
                    # insert_bool = self.insertValues(client, class_name, page_message_list)
                    # if not insert_bool:
                    #     continue
                    # target_list = ""
                    # for target in insert_bool:
                    #     target_list += target._OrientRecord__rid + ","
                    # target_list = target_list.strip("\,")
                    # if not target_list:
                    #     continue
                    # #创建边缘
                    # edge_bool = self.createEdgeMore(client, dege_name, source, target_list)
                    # num += 1
                    # # 休息1秒中
                    # time.sleep(1)
                return None
            #自增
            cur_page += 1
        print(num)
        # 结束时间
        end = time.time()
        # 耗时
        time_consuming = "耗时" + str(end - start)
        print(time_consuming)
        print("done!")

    """
    用于测试orientDB例子
    """
    def example(self):
        # 连接服务器
        client = self.connectDb()
        # 打开数据库
        open_databases = self.openData(client)
        # 创建类
        #class_name = "class_phone"
        class_name = "phone_class" #用于测试数据

        # ==========================

        #查找
        # get_data = self.getData(client, class_name, "15061232164")
        # print(get_data)
        # # get_data = client.command('TRAVERSE outE(), inV() FROM (select from class_phone where phone="15061232164")')
        # # mobile_list = []
        # # for a in get_data:
        # #     b = a._OrientRecord__o_storage
        # #     if 'phone' in b:
        # #         mobile_list.append(b['phone'])
        # #
        # # print(mobile_list)
        # # print("18622818469" in mobile_list)
        # return None

        # ===========================
        # 创建边缘
        #dege_name = "relation_ship"
        dege_name = "ship"
        class_info = self.createClass(client, class_name)
        dege_info = self.createClass(client, dege_name, False)

        """可做循环"""
        # 创建顶点
        num = 0
        phone_list_data = self.createPhone(10000)
        phone_list_data = ['13611065598']
        for phone in phone_list_data:
            #查找关系数据
            get_data = self.getData(client, class_name, phone)
            #创建顶点
            phone_info = self.createVertex(client, class_name, phone)
            source = phone_info[0].rid

            #r = self.createPhone(200)
            r = ['15093560261',
'15207131916',
'13577140693',
'13860777759',
'13427583789',
'15960326693',
'18359971672',
'18659038135'
]
            for value in r:
                # print("%s == %s" % (phone, value))
                phone_info_e = self.createVertex(client, class_name, value)
                target = phone_info_e[0].rid
                if value not in get_data:
                    edge_bool = self.createEdge(client, dege_name, source, target)
                    num += 1
                #
                # print(edge_bool)

            print(num)
        print("done!")
        # ===========================;

    """
    连接图数据库
    """
    def connectDb(self):
        client = pyorient.OrientDB(self.host, int(self.port))
        session_id = client.connect(self.username, self.passwd)
        return client

    """
    删除数据库
    """
    def dropDatabase(self, client):
        client.db_drop(self.db_name)

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
            #查看类是否存在
            create_class = client.command("CREATE CLASS %s extends %s" % (class_name, ex_type))
            # print(create_class)
            if ex_type == 'V':
                #创建属性
                client.command("CREATE PROPERTY %s.phone STRING" % class_name) #手机号
        except pyorient.exceptions.PyOrientSchemaException as f:
            pass
    """
    删除类
    """
    def deleteCalss(self, client, class_name):
        del_info = client.command("DROP CLASS %s" % class_name)
        return del_info

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
    创建边缘
    """
    def createEdge(self, client, edge_name, source, target):
        edget_sql = "CREATE EDGE %s from %s TO %s " % (edge_name, source, target)
        ret = client.command(edget_sql)
        return ret

    """
    查找导线关系数据
    """
    def getData(self, client, class_name, mobile_v):
        get_data = client.command('TRAVERSE outE(), inV() FROM (select from %s where phone="%s")' % (class_name, mobile_v))
        mobile_list = []
        for mobile_class in get_data:
            moible_dict = mobile_class._OrientRecord__o_storage
            if 'phone' in moible_dict:
                mobile_list.append(moible_dict['phone'])
        return mobile_list

    """
    创建索引
    """
    def createIndex(self, client, class_name, v_value):
        #查找索引是否存在不存在创建
        index_list = client.command("select clusters from (select expand(indexes) from metadata:indexmanager)")
        for index_name in index_list:
            name = index_name._OrientRecord__o_storage['clusters']
            for class_n in name:
                if class_n == class_name:
                    return True

        #创建索引
        client.command("create index %s ON %s(%s) UNIQUE " % (class_name+v_value, class_name, v_value))

    """
    插入数据
    """
    def insertValues(self, client, class_name, v_values):
        diff_mobile = self.getMobileAll(client, class_name, v_values)
        #判断是否存在
        if not diff_mobile:
            return None
        str = ''
        for mobile in diff_mobile:
            str += "('" + mobile + "'),"
        str = str.strip("\,")
        #批量插入
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
        #===============================
        #v_values中有而mobile_data中没有的
        mobile_data = list(set(v_values).difference(set(mobile_data)))
        return mobile_data

    # 获取数据
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
    """
    随机生成手机号码
    """
    def createPhone(self, phone_num = 10):
        phone_data = []
        for i in range(phone_num):
            prelist = ["130", "131", "132", "133", "134", "135", "136", "137", "138", "139", "147", "150", "151", "152",
                       "153", "155", "156", "157", "158", "159", "186", "187", "188"]
            number = random.choice(prelist) + "".join(random.choice("0123456789") for i in range(8))
            phone_data.append(number)
        return phone_data