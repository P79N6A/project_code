# -*- coding: utf-8 -*-
'''
集群导入数据查找数据增加缓存
'''
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

class ColonylistCommand(BaseCommand):
    # 图数据库连接句柄
    orientdb_client = ''
    # 图数据库配置文件
    orientdb_config = ''
    # 数据库类对象
    oUser = ''
    # 连接ssdb
    ssdb_resources = ""

    # 获取commands绝对目录
    dir_path = os.path.dirname(os.path.abspath(__file__))
    # 目录分隔符
    delimiter = os.sep
    phone_dir = 'mobile'
    key_suffix = '_orientdb_10'
    # 图数据库名
    #db_name = 'graph_phone'
    db_name = 'test'
    # ssdb通讯录分页，用于限制一次可以插入多少条
    ssdb_limit = 500
    limit = 200
    # 类名
    class_name = "class_phone_test"
    # 边缘名
    edge_name= "relation_ship_test"
    orientdb_config = {
            "http_host" : "47.96.99.175",
            "http_port" : "2424",
            "user"      : "root",
            "password"  : "Root_xhh123#@!",
            "database"  : "test",
    }


    def __init__(self):
        #self.orientdb_config = OrientdbConfig().getConfig()
        self.oUser = YiUser()
        self.orientdb_client = self.connectDb()
        #   连接ssdb
        ssdb = SsdbObject(False)
        self.ssdb_resources = ssdb.ssdbConnection()

    def runData(self, start_time, end_time):
        # 查找有多少条用户
        total = self.oUser.getMobileCount(start_time, end_time)
        if not total:
            print("没有用户信息")
            return False

        # 图数据库
        orien_oper = self.orientdbOperation()
        if not orien_oper:
            print("图数据库操作失败")
            return False

        # 分页处理
        limit = self.limit
        limit = 2
        pages = math.ceil(total / limit);
        cur_page = 0;
        num = 0
        # 开始时间
        start = time.time()
        while cur_page < pages:
            offset = cur_page * limit
            # 自增
            cur_page += 1
            # 取出用户信息
            mobile_data = self.oUser.getMobileUser(start_time, end_time, offset, limit)
            for user_info in mobile_data:
                phone = user_info.mobile.strip()
                mail_list_num = self.importOperation(phone)
                num += mail_list_num
        # 结束时间
        end = time.time()
        # 耗时
        time_consuming = str(num) + " 条数据耗时：" + str(end - start)
        print(time_consuming)

    def importOperation(self, phone):
        # 判断手机号是否存在在缓存中
        key = phone + self.key_suffix
        phone_rid = self.ssdb_resources.get(key)
        if not phone_rid:
            try:
                p_rid = self.insertOne(phone)
                self.ssdb_resources.set(key, p_rid)
                phone_rid = self.ssdb_resources.get(key)
            except pyorient.exceptions.PyOrientORecordDuplicatedException as f:
                print(f)
                vertext_info = self.getVertext(phone)
                v_rid = "#"+vertext_info[0].rid._OrientRecordLink__link
                self.ssdb_resources.set(key, v_rid)
                phone_rid = self.ssdb_resources.get(key)

        #查找通讯录
        message_str = self.ssdb_resources.get(phone)
        message_list = []
        if message_str is not None:
            message_list = list(set(json.loads(message_str)))
        # 去掉当前手机号
        if phone in message_list:
            message_list.remove(phone)
        num = len(message_list) + 1
        #====================================================
        # ssdb分页
        ssdb_limit = self.ssdb_limit;
        ssdb_cur_page = 0
        while ssdb_cur_page < len(message_list):
            ssdb_offset = ssdb_cur_page + ssdb_limit
            print("%s---%d---%d" % (phone, ssdb_cur_page, ssdb_offset))
            page_message_list = message_list[ssdb_cur_page:ssdb_offset]
            ssdb_cur_page += ssdb_limit
            # 插入数据
            try:
                insert_bool = self.insertValues(page_message_list)
            except pyorient.exceptions.PyOrientORecordDuplicatedException as f:
                print(f)
            # 获取rid
            target_list = ""
            for target_phone in page_message_list:
                t_key = target_phone + self.key_suffix
                t_rid = self.ssdb_resources.get(t_key)
                if not t_rid:
                    continue
                target_list += str(t_rid,encoding="utf-8") + ","
            target_list = target_list.strip("\,")
            #print(phone_rid)
            phone_rid = str(phone_rid,encoding="utf-8")
            # 创建边缘
            try:
                edge_bool = self.createEdgeMore( phone_rid, target_list)
            except pyorient.exceptions.PyOrientCommandException as f:
                print(f)
        return num
    '''
    图数据库操作（类，边缘，数据库，索引）
    '''
    def orientdbOperation(self):
        # 1.判断本地数据库是否存在
        is_database = self.orientdb_client.db_exists(self.db_name, pyorient.STORAGE_TYPE_PLOCAL)
        if not is_database:
            print("\"%s\" 数据库不存在" % self.db_name)
            return False

        # 2.打开数据库
        try:
            open_databases = self.orientdb_client.db_open(self.db_name, self.orientdb_config['user'], self.orientdb_config['password'])
        except pyorient.exceptions.PyOrientDatabaseException as e:
            print(e)
            return False

        # 3.创建类
        try:
            create_class_sql = "CREATE CLASS %s extends %s" % (self.class_name, "V")
            create_class = self.orientdb_client.command(create_class_sql)
        except pyorient.exceptions.PyOrientSchemaException as p:
            print("创建类："+"="*50)
            print(p)

        # 4.创建类的属性
        try:
            class_property_sql = "CREATE PROPERTY %s.phone STRING" % self.class_name
            #print(class_property_sql)
            create_property = self.orientdb_client.command(class_property_sql)  # 手机号
            #print(create_property)
        except pyorient.exceptions.PyOrientCommandException as p:
            print("创建类属性:" + "="*50)
            print(p)

        # 5.创建边缘
        try:
            edge_sql = "CREATE CLASS %s extends %s" % (self.edge_name, "E")
            #print(edge_sql)
            create_edge = self.orientdb_client.command(edge_sql)
            #print(create_edge)
        except pyorient.exceptions.PyOrientSchemaException as f:
            print("创建边缘:" + "=" * 50)
            print(f)
        # 6.创建索引
        try:
            index_sql = "create index %s ON %s(%s) UNIQUE " % (self.class_name + "_phone", self.class_name, "phone")
            create_edge = self.orientdb_client.command(index_sql)
            print(create_edge)
        except pyorient.exceptions.PyOrientIndexException as f:
            print("创建索引:" + "=" * 50)
            print(f)
        print("创建数据库相关结束" + "=" * 40)
        return True

    '''
    连接图数据库
    '''
    def connectDb(self):
        client = pyorient.OrientDB(self.orientdb_config['http_host'], int(self.orientdb_config['http_port']))
        session_id = client.connect(self.orientdb_config['user'], self.orientdb_config['password'])
        return client

    '''
    单条插入
    '''
    def insertOne(self, phone):
        insert_str = "insert into %s (phone) values(%s)" % (self.class_name, phone)
        insert_info = self.orientdb_client.command(insert_str)
        return insert_info[0]._OrientRecord__rid

    '''
    插入数据
    '''
    def insertValues(self, message_list):
        ssdb_mobile = []
        for mobile in message_list:
            m_key = mobile + self.key_suffix
            if self.ssdb_resources.get(m_key):
                continue
            ssdb_mobile.append(mobile)
        if len(ssdb_mobile) < 1:
            return False
        # 表数过滤
        diff_mobile = self.getMobileAll(ssdb_mobile)
        # 判断是否存在
        if len(diff_mobile) < 1:
            return False
        str = ''
        for mobile in diff_mobile:
            str += "('" + mobile + "'),"
        str = str.strip("\,")
        # 批量插入
        insert_str = "insert into %s (phone) values %s" % (self.class_name, str)
        insert_data = self.orientdb_client.command(insert_str)
        for insert_object in insert_data:
            p_rid = insert_object._OrientRecord__rid
            key = insert_object.phone + self.key_suffix
            if not self.ssdb_resources.get(key):
                self.ssdb_resources.set(key,p_rid)
        return True

    '''
    创建边缘批量
    '''
    def createEdgeMore(self, source, target):
        edget_sql = "CREATE EDGE %s from %s TO [%s] " % (self.edge_name, source, target)
        ret = self.orientdb_client.command(edget_sql)
        #print(ret)
        return ret

    '''
    查找顶点
    '''
    def getVertext(self, phone):
        return self.orientdb_client.command('select @rid from %s where phone="%s" ' % (self.class_name, phone))

    '''
    批量获取数据
    '''
    def getMobileAll(self, v_values):
        # 批量获取数据
        get_sql = "select from %s where phone in %s" % (self.class_name, v_values)
        get_data = self.orientdb_client.command(get_sql)
        mobile_data = []
        for mobile in get_data:
            m_key = mobile.phone + self.key_suffix
            #print(m_key)
            self.ssdb_resources.set(m_key, mobile._OrientRecord__rid)
            mobile_data.append(mobile.phone)
        #===============================
        #v_values中有而mobile_data中没有的
        mobile_data = list(set(v_values).difference(set(mobile_data)))
        return mobile_data


