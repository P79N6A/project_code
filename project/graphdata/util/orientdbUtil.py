# -*- coding:utf-8 -*-

'''
图数据库相关操作
'''


import pyorient
from lib.orientdb_config import OrientdbConfig
from lib.orientdb_cluster_config import OrientdbClusterConfig

class orientdbUtil(object):
    def __init__(self):
        Oconfig = self.importConfig("orientdb_cluster_config")
        self.config = Oconfig.getConfig()

    '''
    配置文件
    '''
    def importConfig(self, config_name):
        if config_name == 'orientdb_cluster_config':
            return OrientdbClusterConfig()
        return OrientdbConfig()


    '''
    连接图数据库
    '''
    def connectDb(self):
        try:
            client = pyorient.OrientDB(self.config['http_host'], int(self.config['http_port']))
            session_id = client.connect(self.config['user'], self.config['password'])
            return client
        except pyorient.exceptions.PyOrientConnectionException as error:
            print(error)
            return False
        except AttributeError as error:
            print(error)
            return False

    '''
    打开数据库
    '''
    def openDatabas(self, client):
        try:
            open_databases = client.db_open(self.config['database'], self.config['user'], self.config['password'])
            return open_databases
        except pyorient.exceptions.PyOrientDatabaseException as error:
            print(error)
            return False
        except AttributeError as error:
            print(error)
            return False

    '''
    创建类
    '''
    def createClass(self, client, class_name):
        try:
            create_class_sql = "CREATE CLASS %s extends %s" % (class_name, "V")
            create_class = client.command(create_class_sql)
            print("创建成功")
        except pyorient.exceptions.PyOrientSchemaException as error:
            print(error)

    '''
    创建类的属性
    client       连接句柄
    class_name   类名
    property     属性
    '''
    def createProperty(self, client, class_name, *property):
        if not property:
            print("属性不能为空！")
            return False
        for pro in property:
            try:
                class_property_sql = "CREATE PROPERTY %s.%s STRING" % (class_name, pro)
                create_property = client.command(class_property_sql)  # 手机号
                print("%s 创建属性 %s" % (class_name, str(pro)))
            except pyorient.exceptions.PyOrientCommandException as p:
                print(p)

    '''
    创建索引
    client       连接句柄
    class_name   类名
    property     属性
    '''
    def createIndex(self, client, class_name, *property):
        for pro in property:
            try:
                index_sql = "create index %s ON %s(%s) UNIQUE " % (class_name + ".phone", class_name, pro)
                create_edge = client.command(index_sql)
                print("%s 创建索引 %s" % (class_name, str(pro)))
            except pyorient.exceptions.PyOrientIndexException as f:
                print(f)

    '''
    创建边缘
    '''
    def createEdge(self, client, edge_name):
        try:
            edge_sql = "CREATE CLASS %s extends %s" % (edge_name, "E")
            #print(edge_sql)
            create_edge = client.command(edge_sql)
            print("%s 创建边缘成功" % edge_name)
        except pyorient.exceptions.PyOrientSchemaException as f:
            print(f)

    '''
    批量获取数据
    '''
    def getBatchData(self, client, class_name, phone_data):
        # 批量获取数据
        get_sql = "select from %s where phone in %s" % (class_name, phone_data)
        get_data = client.command(get_sql)
        mobile_data = []
        for mobile in get_data:
            mobile_data.append(mobile.phone)
        return mobile_data

    '''
    批量插入数据
    '''
    def insertBatchData(self, client, class_name, phone_tuple):
        # 批量插入
        try:
            insert_str = "insert into %s (phone) values %s" % (class_name, phone_tuple)
            insert_data = client.command(insert_str)
            return insert_data
        except pyorient.exceptions.PyOrientORecordDuplicatedException as error:
            print(error)
            return False
        except pyorient.exceptions.PyOrientCommandException as error:
            print(error)
            return False

    '''
    获取数据
    '''
    def getMobileData(self, client, class_name, phone_list):
        get_sql = "select from %s where phone in %s" % (class_name, phone_list)
        get_data = client.command(get_sql)
        return get_data

    '''
    查找边缘
    '''
    def getEdgeData(self, client, edge_name,phone_rid):
        get_edge_sql = "TRAVERSE out('%s'), inV() FROM  %s limit 5000" % (edge_name, phone_rid)
        ret = client.command(get_edge_sql)
        return ret


    '''
    创建边缘批量
    '''
    def createEdgeMore(self, client, edge_name, source, target):
        try:
            edget_sql = "CREATE EDGE %s from %s TO [%s] " % (edge_name, source, target)
            ret = client.command(edget_sql)
            return ret
        except pyorient.exceptions.PyOrientCommandException as error:
            print(error)
            return False