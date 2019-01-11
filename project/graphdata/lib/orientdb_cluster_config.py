class OrientdbClusterConfig(object):
    """开发"""
    def devServer(self):
        db_data = {
            "http_host" : "47.96.99.175",
            "http_port" : "2424",
            "user"      : "root",
            "password"  : "Root_xhh123#@!",
            "database"  : "test",
        }

        return db_data
    """生产"""
    def proServer(self):
        db_data = {
            "http_host" : "140.143.34.13",
            "http_port" : "2424",
            "user"      : "root",
            "password"  : "xhh123",
            "database"  : "graph",
        }
        return db_data

    def getConfig(self):
        #return self.proServer()
        return self.devServer() # 测试
