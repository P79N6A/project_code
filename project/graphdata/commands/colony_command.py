# -*- coding: utf-8 -*-

import os
import pyorient
import time
import json

from datetime import datetime, timedelta
from .base_command import BaseCommand
from lib.orientdb_config import OrientdbConfig
from lib.logger import logger

class ColonyCommand(BaseCommand):
    dir_path = os.path.dirname(os.path.abspath(__file__)) #获取commands绝对目录
    delimiter = os.sep #目录分隔符
    phone_dir = 'mobile'
    client = ''

    def __init__(self):
        config = OrientdbConfig().getConfig()
        self.host = config['http_host']
        self.port = config['http_port']
        self.username = config['user']
        self.passwd = config['password']
        #self.db_name = 'graph_phone'
        self.db_name = 'test'
        self.ssdb_limit = 500  # ssdb通讯录分页，用于限制一次可以插入多少条
        self.class_name = "class_phone"  # 类名
        self.edge_name= "relation_ship"  # 边缘名

    def runData(self, start_time, end_time):
        #1.时间列表
        date_list = self.dateList(start_time, end_time)
        #print(date_list)
        if not date_list:
            print("区间时间有误！")
            return None
        #==============================================
        #2.连接orientdb
        self.client = self.connectDb()
        #==============================================
        #3.判断本地数据库是否存在
        is_database = self.client.db_exists(self.db_name, pyorient.STORAGE_TYPE_PLOCAL)
        if not is_database:
            print("\"%s\" 数据库不存在" % self.db_name)
            return None
        #4.打开数据库
        try:
            open_databases = self.client.db_open(self.db_name, self.username, self.passwd)
        except pyorient.exceptions.PyOrientDatabaseException as e:
            print(e)
            return None
        #==============================================

        #5.创建类
        try:
            create_class_sql = "CREATE CLASS %s extends %s" % (self.class_name, "V")
            create_class = self.client.command(create_class_sql)
        except pyorient.exceptions.PyOrientSchemaException as p:
            print("创建类："+"="*50)
            print(p)
        #==============================================
        #6.创建类的属性
        try:
            class_property_sql = "CREATE PROPERTY %s.phone STRING" % self.class_name
            #print(class_property_sql)
            create_property = self.client.command(class_property_sql)  # 手机号
            #print(create_property)
        except pyorient.exceptions.PyOrientCommandException as p:
            print("创建类属性:" + "="*50)
            print(p)
        #===============================================
        #7.创建边缘
        try:
            edge_sql = "CREATE CLASS %s extends %s" % (self.edge_name, "E")
            #print(edge_sql)
            create_edge = self.client.command(edge_sql)
            #print(create_edge)
        except pyorient.exceptions.PyOrientSchemaException as f:
            print("创建边缘:" + "=" * 50)
            print(f)

        #=================================================
        #8.创建索引
        try:
            index_sql = "create index %s ON %s(%s) UNIQUE " % (self.class_name + "phone", self.class_name, "phone")
            create_edge = self.client.command(index_sql)
            print(create_edge)
        except pyorient.exceptions.PyOrientIndexException as f:
            print("创建索引:" + "=" * 50)
            print(f)
        print("创建数据库相关结束" + "=" * 40)
        #=================================================
        # 9.数据目录
        data_dir = self.dir_path + self.delimiter + self.phone_dir + self.delimiter
        # ================================================
        # 10. 按天读取文件
        for date_one in date_list:
            # 文件
            mobile_file = data_dir + date_one + '.txt'
            #通讯录目录
            mail_list =  data_dir + date_one + self.delimiter
            # 判断文件是否存在
            if not os.path.exists(mobile_file):
                print("%s导入文件不存在" % date_one)
                continue

            #一天的数据
            # 开始时间
            start = time.time()
            mobile_num = self.readFile(mobile_file, mail_list)
            # 结束时间
            end = time.time()
            # 耗时
            time_consuming = str(mobile_num) + " 条数据耗时：" + str(end - start)
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
    将时间区间按天转换成列表
    例：返回数据：['2018-03-09back', '2018-03-10']
    """
    def dateList(self, start_time, end_time):
        # 1.时间
        max_day = (datetime.strptime(end_time, '%Y-%m-%d') - datetime.strptime(start_time, '%Y-%m-%d')).days
        # max_day = 303
        cur_data = 0
        date_list = []
        while cur_data <= max_day:
            dayA = datetime.strptime(start_time, '%Y-%m-%d')
            delta = (dayA + timedelta(days=cur_data)).strftime("%Y-%m-%d")
            cur_data += 1
            date_list.append(delta)
        return date_list

    """
    读取手机号文件
    """
    def readFile(self, path_name, mail_list_path):
        print("读取文件：%s" % path_name)
        num = 0
        with open(path_name, "r", encoding='UTF-8') as file:
            mobile_data = file.readlines()
            for phone in mobile_data:
                num += 1
                # 去除首位空格
                phone = phone.strip()
                # 去除尾部 \ 符号
                phone = phone.rstrip("\n")
                # 增加一条记录
                phone_rid = self.insertOne(phone)
                break
                if not phone_rid:
                    print("插入查找记录失败：手机号-%s" % phone)
                    continue
                # 读取通讯录的数据
                mail_list_file = mail_list_path + phone + ".json"
                #判断文件是否存在
                if not os.path.exists(mail_list_file):
                    print("%s 通讯录不存在：%s" % (phone,mail_list_file))
                    continue

                mail_lsit = self.mailList(phone, phone_rid, mail_list_file)
                num = num + mail_lsit
        return num

    '''
    单条插入
    '''
    def insertOne(self, phone):
        info = self.getOne(phone)
        if not info:
            insert_str = "insert into %s (phone) values(%s)" % (self.class_name, phone)
            insert_info = self.client.command(insert_str)
            #print(insert_info[0]._OrientRecord__rid)
            info = self.getOne(phone)
        if len(info) < 1:
            return False
        return info[0].rid


    '''
    查找一条数据
    '''
    def getOne(self, phone):
        get_sql = 'select @rid from %s where phone="%s" limit 1' % (self.class_name, phone)
        return self.client.command(get_sql)

    '''
    通讯录数据
    '''
    def mailList(self, phone, phone_rid, mail_list_path):
        print("读取通讯录文件：%s" % mail_list_path)
        #查找边缘
        edge_info = self.getEdge(phone_rid)
        edge_list = ""
        for target in edge_info:
            edge_list += target._OrientRecord__rid + ","
        edge_list = edge_list.strip("\,")
        #========================================
        list_num = 0
        with open(mail_list_path, "r", encoding='UTF-8') as file:
            data = file.readlines()
            message_list = list(set(json.loads(data[0])))
            list_num = len(message_list)

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
                #print(page_message_list)
                try:
                    # 插入数据
                    insert_bool = self.insertValues(page_message_list)
                except pyorient.exceptions.PyOrientORecordDuplicatedException as f:
                    logger.error(f)
                    continue
                # 获取插入数据
                get_mobile = self.getMobileData( page_message_list)
                target_list = ""
                for target in get_mobile:
                    target_list += target._OrientRecord__rid + ","
                target_list = target_list.strip("\,")
                if not target_list:
                    continue

                #================================================
                # 创建边缘
                # 取出不同的
                mobile_data = list(set(target_list).difference(set(edge_list)))
                if mobile_data:
                    try:
                        edge_bool = self.createEdgeMore(phone_rid, target_list)
                    except pyorient.exceptions.PyOrientORecordDuplicatedException as f:
                        #print(f)
                        logger.error(f)
                        continue
        return list_num


    """
    插入数据
    """
    def insertValues(self, mail_list):
        diff_mobile = self.getMobileAll(mail_list)
        # 判断是否存在
        if not diff_mobile:
            return None
        str = ''
        for mobile in diff_mobile:
            str += "('" + mobile + "'),"
        str = str.strip("\,")
        # 批量插入
        insert_str = "insert into %s (phone) values %s" % (self.class_name, str)
        insert_data = self.client.command(insert_str)
        return insert_data


    """
    批量获取数据
    """
    def getMobileAll(self, mail_list):
        # 批量获取数据
        get_sql = "select from %s where phone in %s" % (self.class_name, mail_list)
        get_data = self.client.command(get_sql)
        mobile_data = []
        for mobile in get_data:
            mobile_data.append(mobile.phone)
        # ===============================
        # mail_list中有而mobile_data中没有的
        #mobile_data = list(set(mail_list).difference(set(mobile_data)))
        return mobile_data

    '''
    获取数据
    '''
    def getMobileData(self, v_values):
        get_sql = "select from %s where phone in %s" % (self.class_name, v_values)
        get_data = self.client.command(get_sql)
        return get_data

    """
    创建边缘批量
    """
    def createEdgeMore(self, source, target):
        edget_sql = "CREATE EDGE %s from %s TO [%s] " % (self.edge_name, source, target)
        ret = self.client.command(edget_sql)
        return ret

    '''
    查找边缘
    '''
    def getEdge(self, phone_rid):
        get_edge_sql = "SELECT in FROM (TRAVERSE outE(), inV() FROM  %s)" % phone_rid
        ret = self.client.command(get_edge_sql)
        return ret