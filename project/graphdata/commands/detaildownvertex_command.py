# -*- coding:utf-8 -*-

import os
import json
import time

from .base_command import BaseCommand
from util.orientdbUtil import orientdbUtil

class DetaildownvertexCommand(BaseCommand):

    dir_path = os.path.dirname(os.path.abspath(__file__))  # 获取commands绝对目录
    delimiter = os.sep  # 目录分隔符
    phone_dir = 'phone_detail'

    ssdb_limit = 500
    Oorientdb = ''  # 图数据库类
    class_name = 'class_phone'  # 类名
    property = 'phone'  # 属性名
    client = ''

    def __init__(self):
        self.Oorientdb = orientdbUtil()


    def runData(self, date_time):
        # 开始时间
        start = time.time()
        # 数据目录
        data_path = "%s%s%s%s" % (self.dir_path, self.delimiter, self.phone_dir, self.delimiter)
        data_filename = "%s%s%s" % (data_path, date_time, ".txt")

        # 判断文件是否存在
        if not os.path.exists(data_filename):
            print("\"%s\" 文件不存在!" % (data_filename))
            return False

        #==========================================
        # 连接图数据库
        self.client = self.Oorientdb.connectDb()
        print("连接图数据库！")
        if not self.client:
            return False

        # 打开数据库
        open_database = self.Oorientdb.openDatabas(self.client)
        print("打开数据库！")
        if not open_database:
            return False

        # ==========================================

        # 查看类是否存在
        self.Oorientdb.createClass(self.client, self.class_name)

        # 创建属性
        self.Oorientdb.createProperty(self.client, self.class_name, self.property)

        # 创建索引
        self.Oorientdb.createIndex(self.client, self.class_name, self.property)

        #==========================================

        # 读取文件
        num = 0
        with open(data_filename, 'r', encoding="utf-8") as file:
            while True:
                phone = str(file.readline())
                # 去除首位空格
                phone = phone.strip()
                # 去除尾部 \ 符号
                phone = phone.rstrip("\n")
                if not phone:
                    break

                # 读取详单
                detail_path = "%s%s%s" % (data_path, date_time, self.delimiter)
                detail_fileaname = "%s%s%s" % (detail_path, phone, '.txt')
                # 判断文件是否存在
                if not os.path.exists(detail_fileaname):
                    print("详单 \"%s\" 文件不存在!" % (detail_fileaname))
                    continue
                # 详单数据
                detail_list = self.readDetailData(detail_fileaname)
                if not detail_list:
                    print("详单 \"%s\" 暂无数据" % (detail_fileaname))
                    continue
                # 去掉当前手机号
                if phone in detail_list:
                    detail_list.remove(phone)

                # 将当前手机号加入到详单手机号中
                try:
                    detail_list.append(phone)
                except AttributeError as error:
                    print("%s 详单error:%s" % (phone, error))
                    detail_dict_list = []
                    for k,v in detail_list.items():
                        detail_dict_list.append(v)
                    detail_dict_list.append(phone)
                    detail_list = detail_dict_list
                # 插入顶点
                insert_num = self.insertVertex(phone, detail_list)
                num += insert_num

        # 结束时间
        end = time.time()
        # 耗时
        time_consuming = "耗时：" + str(end - start)
        print(time_consuming)
        print(num)
        print("done!")

    '''
    插入顶点
    '''
    def insertVertex(self, user_phone, detail_data):
        # ssdb分页
        ssdb_limit = self.ssdb_limit;
        ssdb_cur_page = 0
        insert_num = 0
        while ssdb_cur_page < len(detail_data):
            ssdb_offset = ssdb_cur_page + ssdb_limit
            print("%s---%d---%d" % (user_phone, ssdb_cur_page, ssdb_offset))
            # 获取部分手机号
            detail_list = detail_data[ssdb_cur_page:ssdb_offset]
            ssdb_cur_page += ssdb_limit

            # 批量获取数据
            get_batch_data = self.Oorientdb.getBatchData(self.client, self.class_name, detail_list)
            # 去掉重复的数据
            mobile_data = list(set(detail_list).difference(set(get_batch_data)))
            # mobile_data = get_batch_data

            # 格式数据
            if not mobile_data:
                continue
            phone_tuple = ''
            for mobile in mobile_data:
                phone_tuple += "('" + mobile + "'),"
            phone_tuple = phone_tuple.strip("\,")
            insert_batch_data = self.Oorientdb.insertBatchData(self.client, self.class_name, phone_tuple)
            if not insert_batch_data:
                continue
            insert_num += len(mobile_data)
        return insert_num

    '''
    读取详单数据
    '''
    def readDetailData(self, detail_filename):
        with open(detail_filename, 'r', encoding='utf-8') as d_file:
            detail_info = d_file.readlines()
            try:
                detail_list = json.loads(detail_info[0])
                detail_list = detail_list['phoneArr']
                return detail_list
            except KeyError as errorkey:
                print("KeyError:%s" % errorkey)
                return False
            except IndexError as error1:
                print(error1)
                return False
            except json.decoder.JSONDecodeError as error2:
                print("json error:%s" % error2)
                return False


