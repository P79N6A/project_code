# -*- coding:utf-8 -*-

import os
import json
import time

from .base_command import BaseCommand
from util.orientdbUtil import orientdbUtil

class DetaildownEdgeCommand(BaseCommand):

    dir_path = os.path.dirname(os.path.abspath(__file__))  # 获取commands绝对目录
    delimiter = os.sep  # 目录分隔符
    phone_dir = 'phone_detail'

    ssdb_limit = 500
    Oorientdb = ''  # 图数据库类
    class_name = 'class_phone'  # 类名
    property = 'phone'  # 属性名
    edge_name = 'edge_detail'  # 边缘名
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

        # 5.查看类是否存在
        self.Oorientdb.createClass(self.client, self.class_name)

        # 6.创建属性
        self.Oorientdb.createProperty(self.client, self.class_name, self.property)

        # 7.创建索引
        self.Oorientdb.createIndex(self.client, self.class_name, self.property)

        # 创建边缘
        self.Oorientdb.createEdge(self.client, self.edge_name)

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

                # 创建边缘

                insert_num = self.createEdge(phone, detail_list)
                num += insert_num
        # 结束时间
        end = time.time()
        # 耗时
        time_consuming = "耗时：" + str(end - start)
        print(time_consuming)
        print(num)
        print("done!")

    '''
    创建边缘
    '''
    def createEdge(self, user_phone, detail_data):
        # ssdb分页
        ssdb_limit = self.ssdb_limit;
        ssdb_cur_page = 0
        insert_num = 0

        data_list = []
        # 如果是字典
        if type(detail_data).__name__ == 'dict':
            for k, v in detail_data.items():
                data_list.append(v)

        # 如果是列表
        if type(detail_data).__name__ == 'list':
            data_list = detail_data

        del detail_data #删除数据
        # 数据处理
        while ssdb_cur_page < len(data_list):
            ssdb_offset = ssdb_cur_page + ssdb_limit
            print("%s---%d---%d" % (user_phone, ssdb_cur_page, ssdb_offset))
            detail_list = data_list[ssdb_cur_page:ssdb_offset]
            # 累加
            ssdb_cur_page += ssdb_limit

            # 将当前手机号加入到查询列表中
            detail_list.append(user_phone)

            # 批量查找
            get_mobile_data = self.Oorientdb.getMobileData(self.client, self.class_name, detail_list)
            # 获取rid
            source_rid = ''  # 来源rid
            # ============
            target_list = []  # 连接rid列表
            for m in get_mobile_data:
                if m.phone == user_phone:
                    source_rid = m._OrientRecord__rid
                    continue
                target_list.append(m._OrientRecord__rid)
            # ============
            # 如果来源为空或目标为空就跳过
            if not source_rid or not target_list:
                continue
            # 查找边缘是否存在
            get_edge_data = self.Oorientdb.getEdgeData(self.client, self.edge_name, source_rid)
            edge_list = []  # 连接边缘rid
            for edge_data in get_edge_data:
                # 去掉当前手机号
                if source_rid == edge_data._OrientRecord__rid:
                    continue
                edge_list.append(edge_data._OrientRecord__rid)
            # 去掉重复的rid
            target_rid = list(set(target_list).difference(set(edge_list)))
            if not target_rid:
                continue

            # 创建成功累加
            insert_num += len(target_rid)
            create_edge = ''
            for edge_data in target_rid:
                create_edge += edge_data + ","

            create_edge = create_edge.strip("\,")

            del edge_list,target_list,get_edge_data

            # 创建边缘
            insert_edge = self.Oorientdb.createEdgeMore(self.client, self.edge_name, source_rid, create_edge)


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


