# -*- coding:utf-8 -*-

'''
文件相关操作
'''

import os

class fileUtil(object):

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
    写入通讯详单文件
    '''
    def writePhoneText(self, filename, phone_str):
        try:
            with open(filename, "a+") as f:
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