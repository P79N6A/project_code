# -*- coding:utf-8 -*-
'''
通过ip找到手机号
'''

import os
import time
import math
import json

from datetime import datetime, timedelta
from module.yiyiyuan.model import YiUser
from module.yiyiyuan.model import YiUserExtend
from .base_command import BaseCommand
from util.orientdbUtil import orientdbUtil
from util.fileUtil import fileUtil


class IpdownCommand(BaseCommand):
    limit = 200  # 用于手机号分页

    dir_path = os.path.dirname(os.path.abspath(__file__))  # 获取commands绝对目录
    delimiter = os.sep  # 目录分隔符
    phone_dir = 'phone_ip'
    fileUtil = ''
    oUserExtend = ''
    oUser = ''


    def __init__(self):
        self.fileUtil = fileUtil()
        # 用户扩展表
        self.oUserExtend = YiUserExtend()
        # 1. 连一亿元库用户表
        self.oUser = YiUser()


    def runData(self, date_time):
        # 开始时间
        start = time.time()

        # 时间处理
        start_time = datetime.strptime(date_time + " 00:00:00", '%Y-%m-%d %H:%M:%S')
        end_time = datetime.strptime(date_time + " 23:59:59", '%Y-%m-%d %H:%M:%S')

        print("开始时间：%s - 结束时间：%s" % (start_time, end_time))

        # 2. 查找有多少条用户
        total = self.oUser.getMobileCount(start_time, end_time)
        if not total:
            print("\"%s\" - \"%s\" 间时区间暂无数据！" % (start_time, end_time))
            return None



        limit = self.limit
        pages = math.ceil(total / limit);
        cur_page = 0
        # 开始时间
        start = time.time()

        save_path = "%s%s%s%s" % (self.dir_path, self.delimiter, self.phone_dir, self.delimiter)

        # 创建目录
        self.fileUtil.mkdir(save_path)

        # 保存文件名
        filename = "%s%s%s" % (save_path, date_time, '.txt')

        # 通讯详单目录
        ssdb_path = "%s%s%s" % (save_path, date_time, self.delimiter)

        # 创建目录
        self.fileUtil.mkdir(ssdb_path)

        num = 0
        # 判断文件是否存在，存在就删除
        if os.path.exists(filename):
            os.remove(filename)

        num = 0
        # 8.循环手机号
        while cur_page < pages:
            offset = cur_page * limit
            # 自增
            cur_page += 1
            mobile_data = self.oUser.getMobileUser(start_time, end_time, offset, limit)
            user_phone = ''
            for c_phone in mobile_data:
                num += 1

                phone = c_phone.mobile.strip()
                user_phone += str(phone) + "\n"
                user_id = str(c_phone.user_id).strip()
                # 查找用户扩展信息是否存在
                ip_str = self.ipHandler(user_id)
                if not ip_str:
                    continue
                #保存ip文件
                # 判断目录是否存在
                ip_filename = "%s%s%s" % (ssdb_path, phone, '.txt')
                if os.path.exists(ip_filename):
                    os.remove(ip_filename)

                self.fileUtil.writePhoneText(ip_filename, json.dumps(ip_str))
                num += len(ip_str)
            #保存手机号
            self.fileUtil.writeText(filename, user_phone)

        save_num = "%s 条" % str(num)
        print(save_num)
        # 结束时间
        end = time.time()
        # 耗时
        time_consuming = "耗时：" + str(end - start)
        print(time_consuming)
        print("done!")

    '''
    ip处理
    '''
    def ipHandler(self, user_id):
        if not user_id:
            return False
        # 要找用户ip
        ip_user_info = self.oUserExtend.getUserIp(user_id)
        if not ip_user_info:
            return False
        ip_str = ip_user_info.reg_ip.strip()
        if not ip_str:
            return False

        # 通过ip查找手机号
        # print(ip_str)
        ip_cpunt = self.oUserExtend.getIpCount(ip_str)

        limit = self.limit
        pages = math.ceil(ip_cpunt / limit);
        cur_page = 0
        user_phone = []
        while cur_page < pages:
            offset = cur_page * limit
            # 自增
            cur_page += 1

            # 获取同一个ip的所有信息
            get_ip_data = self.oUserExtend.getIpData(ip_str, offset, limit)
            if not get_ip_data:
                continue

            #获取用户user_id
            ip_tuple = []
            for ip_info in get_ip_data:
                ip_tuple.append(str(ip_info.user_id))

            # 去掉重复的user_id
            if user_id in ip_tuple:
                ip_tuple.remove(user_id)

            # 判断是否为空，为空跳过
            if not ip_tuple:
                continue

            ip_tuple = tuple(ip_tuple)

            #判断是否需要in
            if len(ip_tuple) > 1:
                user_data = self.oUser.getMobileByUserId(ip_tuple)
                user_phone.extend(user_data)

            if (len(ip_tuple)) == 1:
                user_id = ip_tuple[0]
                user_info = self.oUser.getByUserId(user_id)
                try:
                    if user_info.mobile:
                        user_phone.append(user_info.mobile)
                except AttributeError as f:
                    print(f)
                    continue
        return user_phone

