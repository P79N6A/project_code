# -*- coding:utf-8 -*-
'''
通讯详单顶点导入
'''

import time
import math
import json

from datetime import datetime, timedelta
from module.yiyiyuan.model import YiUser
from .base_command import BaseCommand
from util.orientdbUtil import orientdbUtil
from util.ssdbUtil import ssdbUtil

class DetailimportCommand(BaseCommand):
    limit = 200  # 用于手机号分页
    ssdb_limit = 500 #ssdb分页
    Oorientdb = ''  #图数据库类
    class_name = 'class_phone' #类名
    property = 'phone' #属性名
    def __init__(self):
        self.Oorientdb = orientdbUtil()
        #   连接ssdb
        ssdb = ssdbUtil('ssdb_detail_config')
        self.ssdb_resources = ssdb.ssdbConnection()

    def runData(self, start_time, end_time):
        # 时间处理
        start_time = datetime.strptime(start_time + " 00:00:00", '%Y-%m-%d %H:%M:%S')
        end_time = datetime.strptime(end_time + " 23:59:59", '%Y-%m-%d %H:%M:%S')
        print("开始时间：%s - 结束时间：%s" % (start_time, end_time))
        # 1. 连一亿元库用户表
        oUser = YiUser()
        # 2. 查找有多少条用户
        total = oUser.getMobileCount(start_time, end_time)
        if not total:
            print("\"%s\" - \"%s\" 时间区间暂无数据！" % (start_time, end_time))
            return None

        limit = self.limit
        pages = math.ceil(total / limit);
        cur_page = 0
        # 3. 连接图数据库
        client = self.Oorientdb.connectDb()
        if not client:
            return False

        # 4. 打开数据库
        open_database = self.Oorientdb.openDatabas(client)
        if not open_database:
            return False

        # 5.查看类是否存在
        self.Oorientdb.createClass(client, self.class_name)

        # 6.创建属性
        self.Oorientdb.createProperty(client, self.class_name, self.property)

        # 7.创建索引
        self.Oorientdb.createIndex(client, self.class_name, self.property)
        num = 0
        insert_num = 0
        # 开始时间
        start = time.time()
        # 8.循环手机号
        while cur_page < pages:
            offset = cur_page * limit
            # 自增
            cur_page += 1
            mobile_data = oUser.getMobileUser(start_time, end_time, offset, limit)
            for user_info in mobile_data:
                phone = user_info.mobile.strip()
                # 9.从ssdb中获取数数据
                phone_detail = self.ssdb_resources.get(phone)
                # phone_detail = '{"phoneArr":["15125954590","073188040149","15125887806","18719499607","18287885870","13764022964"],"create_time":"2018-03-15 09:52;01","modify_time":"2018-06-24 12:30:06"}'

                message_list = []
                if phone_detail is not None:
                    message_list = json.loads(phone_detail)
                    message_list = list(set(message_list['phoneArr']))

                # 去掉当前手机号
                if phone in message_list:
                    message_list.remove(phone)

                # 加入当前手机号到通讯详单中
                message_list.append(phone)
                num += len(message_list)
                # ssdb分页
                ssdb_limit = self.ssdb_limit;
                ssdb_cur_page = 0
                while ssdb_cur_page < len(message_list):
                    ssdb_offset = ssdb_cur_page + ssdb_limit
                    print("%s---%d---%d" % (phone, ssdb_cur_page, ssdb_offset))
                    page_message_list = message_list[ssdb_cur_page:ssdb_offset]
                    ssdb_cur_page += ssdb_limit
                    # 批量获取数据
                    get_batch_data = self.Oorientdb.getBatchData(client, self.class_name, page_message_list)
                    # 去掉重复的数据
                    mobile_data = list(set(page_message_list).difference(set(get_batch_data)))
                    #mobile_data = get_batch_data

                    # 格式数据
                    if not mobile_data:
                        continue
                    phone_tuple = ''
                    for mobile in mobile_data:
                        phone_tuple += "('" + mobile + "'),"
                    phone_tuple = phone_tuple.strip("\,")
                    # 批量插入

                    insert_batch_data = self.Oorientdb.insertBatchData(client, self.class_name, phone_tuple)
                    if not insert_batch_data:
                        continue
                    insert_num += len(mobile_data)

        # 结束时间
        end = time.time()
        # 耗时
        time_consuming = "耗时：" + str(end - start)
        print(time_consuming)
        print("扫描：%s 条" % str(num))
        print("插入：%s 条" % str(insert_num))
        print("done!")
