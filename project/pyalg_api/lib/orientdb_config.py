class OrientdbConfig(object):
    def __init__(self, SYSTEM_PROD = False, LOCAL = True):
        self.SYSTEM_PROD = SYSTEM_PROD
        self.LOCAL = LOCAL

    """本地"""
    def loacalServer(self):
        db_data = {
            "http_host" : "127.0.0.1",
            "http_port" : "2424",
            "user"      : "root",
            "password"  : "123456",
            "database"  : "xhh_relation",
        }
        return db_data
    """开发"""
    def devServer(self):
        db_data = {
            "http_host" : "140.143.34.13",
            "http_port" : "2424",
            "user"      : "root",
            "password"  : "xhh123",
            "database"  : "test",
        }
        return db_data
    """生产"""
    def proServer(self):
        db_data = {
            "http_host" : "",
            "http_port" : "",
        }
        return db_data

    def getConfig(self):
        if self.SYSTEM_PROD:
            return self.proServer() # 生产
        if self.LOCAL:
            return self.loacalServer() # 本地
        return self.devServer() # 测试
