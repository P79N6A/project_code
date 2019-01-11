# -*- coding:utf-8 -*-

'''
通话详单下载
'''
import os
import time
import math
import json

from datetime import datetime, timedelta
from module.yiyiyuan.model import YiUser
from .base_command import BaseCommand
from util.orientdbUtil import orientdbUtil
from util.ssdbUtil import ssdbUtil

class DetaildownCommand(BaseCommand):
    limit = 200  # 用于手机号分页

    dir_path = os.path.dirname(os.path.abspath(__file__))  # 获取commands绝对目录
    delimiter = os.sep  # 目录分隔符
    phone_dir = 'phone_detail'

    def __init__(self):
        # 连接ssdb
        ssdb = ssdbUtil('ssdb_detail_config')
        self.ssdb_resources = ssdb.ssdbConnection()


    def runData(self, date_time):
        # 开始时间
        start = time.time()

        # 时间处理
        start_time = datetime.strptime(date_time + " 00:00:00", '%Y-%m-%d %H:%M:%S')
        end_time = datetime.strptime(date_time + " 23:59:59", '%Y-%m-%d %H:%M:%S')

        print("开始时间：%s - 结束时间：%s" % (start_time, end_time))
        # 1. 连一亿元库用户表
        oUser = YiUser()
        # 2. 查找有多少条用户
        total = oUser.getMobileCount(start_time, end_time)
        if not total:
            print("\"%s\" - \"%s\" 间时区间暂无数据！" % (start_time, end_time))
            return None

        limit = self.limit
        pages = math.ceil(total / limit);
        cur_page = 0
        # 开始时间
        start = time.time()

        save_path = "%s%s%s%s" % (self.dir_path, self.delimiter, self.phone_dir, self.delimiter)
        #创建目录
        self.mkdir(save_path)

        #保存文件名
        filename = "%s%s%s" % (save_path, date_time, '.txt')

        # 通讯详单目录
        ssdb_path = "%s%s%s" % (save_path, date_time, self.delimiter)

        # 创建目录
        self.mkdir(ssdb_path)

        num = 0
        # 判断文件是否存在，存在就删除
        if os.path.exists(filename):
            os.remove(filename)

        # 8.循环手机号
        while cur_page < pages:
            offset = cur_page * limit
            # 自增
            cur_page += 1
            mobile_data = oUser.getMobileUser(start_time, end_time, offset, limit)
            user_phone = ''
            for c_phone in mobile_data:
                num += 1

                #查找ssdb上的数据
                phone = c_phone.mobile.strip()
                user_phone += str(phone) + "\n"
                # 判断目录是否存在
                ssdb_filename = "%s%s%s" % (ssdb_path, phone, '.txt')
                phone_detail = self.ssdb_resources.get(phone)
                #phone_detail = '{"phoneArr":["15125954590","073188040149","15125887806","18719499607","18287885870","13764022964"],"create_time":"2018-03-15 09:52;01","modify_time":"2018-06-24 12:30:06"}'
                if not phone_detail:
                    continue
                print(type(phone_detail))

                if os.path.exists(ssdb_filename):
                    os.remove(ssdb_filename)
                # 记录到文件中
                ssdb_save = self.writeDetailText(ssdb_filename, phone_detail)
                if ssdb_save:
                    print("保存 \"%s\" 详单成功" % phone)
                else:
                    print("保存 \"%s\" 详单失败" % phone)

            if not user_phone:
                continue
            #记录到文件中
            user_phone_save = self.writeText(filename, user_phone)
            if user_phone_save:
                print("%s用户手机号保存成功" % user_phone)
            else:
                print("%s用户手机号保存失败" % user_phone)

        save_num = "%s 条" % str(num)
        print(save_num)
        # 结束时间
        end = time.time()
        # 耗时
        time_consuming = "耗时：" + str(end - start)
        print(time_consuming)
        print("done!")



    '''
    写入文件
    '''
    def writeText(self, filename, phone_str):
        try:
            with open(filename, "a+", encoding="utf-8") as f:
                f.write(phone_str)
            return True
        except FileNotFoundError as f:
            print(f)
            return False

    '''
    写入通讯详单文件
    '''
    def writeDetailText(self, filename, phone_str):
        try:
            with open(filename, "wb+") as f:
                f.write(phone_str)
            return True
        except FileNotFoundError as f:
            print(f)
            return False

    '''
    创建目录
    '''
    def mkdir(self, path):
        # 去除首位空格
        path = path.strip()
        # 去除尾部 \ 符号
        path = path.rstrip("\\")
        # 判断路径是否存在
        # 存在     True
        # 不存在   False
        isExists = os.path.exists(path)
        # 判断结果
        if not isExists:
            # 如果不存在则创建目录
            # 创建目录操作函数
            os.makedirs(path)
            print(path + ' create dir success')
            return True
        else:
            # 如果目录存在则不创建，并提示目录已存在
            print(path + ' dir existence')
            return False


