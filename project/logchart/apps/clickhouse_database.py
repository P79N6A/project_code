from infi.clickhouse_orm.database import Database


class database():

    def get_yiyiyuan_database(self):
        # clickhouse的数据库连接信息
        database = Database('yiyiyuan', db_url='http://192.168.17.128:8123', username='default')
        return database

    def get_peanut_database(self):
        # clickhouse的数据库连接信息
        database = Database('peanut', db_url='http://192.168.17.128:8123', username='default')
        return database

    def get_zrkey_database(self):
        # clickhouse的数据库连接信息
        database = Database('zrkey', db_url='http://192.168.17.128:8123', username='default')
        return database

    def get_youkayouqian_database(self):
        # clickhouse的数据库连接信息
        database = Database('youkayouqian', db_url='http://192.168.17.128:8123', username='default')
        return database



