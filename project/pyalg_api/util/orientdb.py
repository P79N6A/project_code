# -*- coding:utf-8 -*-

"""
    OrientDB相关类操作
    说明：连接相应OrientDB服务器
    eg:
        from util.orientdb import OrientdbObject
        ssdb = SsdbObject()
        ssdb.run()
"""
import pyorient
import random
import os
from lib.orientdb_config import OrientdbConfig

class OrientdbObject(object):

    def __init__(self, local = True):
        """True 代表生产环境。否则为测试"""
        self.system_prod = False
        self.local = False
        if not self.system_prod:
            self.local = local  # True 本地  False  开发
        self.orientdb_config = OrientdbConfig(self.system_prod,self.local)

    """连接orientdb"""

    def contactOrient(self):
        try:
            # create connection
            self.client = pyorient.OrientDB("localhost", 2424)
            print(self.client)
            session_id = self.client.connect("root", "123456")
            print(session_id)
            # create a database
            # res = self.client.db_create('xhh_relation', pyorient.DB_TYPE_GRAPH, pyorient.STORAGE_TYPE_MEMORY)
            # print(res)
            # open databse
            res = self.client.db_open('xhh_relation', "admin", "admin")
            print(res)
            os._exit(0)
            pass
        except TimeoutError as e:
            print(e)
        except TypeError as t:
            print(t)


    """权重计算--列表"""
    def weightList(self, ip_dict):
        try:
            all_data = []
            for v, w in ip_dict.items():
                temp = []
                for i in range(w):
                    temp.append(v)
                all_data.extend(temp)
            n = random.randint(0, len(all_data) - 1)
            return all_data[n]
        except AttributeError as a:
            print(a)


    """计算权重 能过权重数计算"""
    def weight(self, ip_dict):
        try:
            total = sum(ip_dict.values())
            rad = random.randint(1, total)
            cur_total = 0
            res = ""
            for k, v in self.ssdb_config.pollingIp().items():
                cur_total += v
                if rad <= cur_total:
                    res = k
                    break
            return res
        except AttributeError as a:
            print(a)