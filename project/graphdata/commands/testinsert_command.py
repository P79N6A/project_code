# -*- coding:utf-8 -*-
import os
import json
import time
import random

from .base_command import BaseCommand
from util.orientdbUtil import orientdbUtil

from neo4j.v1 import GraphDatabase

class TestinsertCommand(BaseCommand):
    ssdb_limit = 500
    Oorientdb = ''  # 图数据库类
    class_name = 'class_phone1'  # 类名
    property = 'phone'  # 属性名
    client = ''

    def __init__(self):
        self.Oorientdb = orientdbUtil()

    def runData(self, loop_num):
        uri = "bolt://localhost:7687"
        driver = GraphDatabase.driver(uri, auth=("neo4j", "1234567890"))
        for re in driver.session().run("MATCH (cc:CreditCard)-[r:DO_SHOPPING_WITH]->(cust:Customer) RETURN cc.name"):
            print(re['cc.name'])


    def runData_a(self, loop_num):
        if loop_num == None:
            loop_num = 10

        # 开始时间
        start = time.time()
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

        #self.client.minPool = 3
        #self.client.maxPool = 5

        # 查看类是否存在
        self.Oorientdb.createClass(self.client, self.class_name)

        # 创建属性
        self.Oorientdb.createProperty(self.client, self.class_name, self.property)

        # 创建索引
        #self.Oorientdb.createIndex(self.client, self.class_name, self.property)

        num = 0
        insert_count = 0
        while num < int(loop_num):
            num += 1
            phone = self.createPhone(500)
            #print(len(phone))
            phone_tuple = ''
            print(phone)
            for mobile in phone:
                phone_tuple += "('" + mobile + "'),"
            phone_tuple = phone_tuple.strip("\,")
            insert_num = self.Oorientdb.insertBatchData(self.client, self.class_name, phone_tuple)
            if not insert_num:
                print("插入失败")
                continue
            print("第%d插入%d条数据" % (int(num), len(insert_num)))
            insert_count += len(insert_num)

        # 结束时间
        end = time.time()
        # 耗时
        time_consuming = "耗时：" + str(end - start)
        print(time_consuming)
        print(insert_count)
        print("done!")

    """
    随机生成手机号码
    """
    def createPhone(self, phone_num=10):
        phone_data = []
        for i in range(phone_num):
            prelist = ["130", "131", "132", "133", "134", "135", "136", "137", "138", "139", "147", "150", "151", "152",
                       "153", "155", "156", "157", "158", "159", "186", "187", "188"]
            number = random.choice(prelist) + "".join(random.choice("0123456789") for i in range(8))
            phone_data.append(number)
        return phone_data