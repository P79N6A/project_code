# -*- coding:utf-8 -*-

"""
    ssdb相关类操作
    说明：按权重连接相应ssdb服务器
    eg:
        from util.ssdb import SsdbObject
        ssdb = SsdbObject()
        ssdb.run()
"""

import pyssdb
import random
import collections as coll
from lib.ssdb_config import SsdbConfig

class SsdbObject(object):

    def __init__(self, master = True):
        """True 代表生产环境。否则为测试"""
        self.system_prod = False
        self.master = master #True 主服务器  False从服务器
        self.ssdb_config = SsdbConfig(self.system_prod)

    """连接ssdb"""
    def ssdbConnection(self):
        try:
            ip_addr = self.ssdb_config.getConfig()

            if self.master:  #区别主从服务器
                ip_addr = ip_addr['master']
            else:
                ip_addr_dict = ip_addr['slave']
                ip_addr = self.weightList(ip_addr_dict)
            ip_list = ip_addr.split(":")
            ssdb_resources = pyssdb.Client(ip_list[0], int(ip_list[1]))
            return ssdb_resources
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



