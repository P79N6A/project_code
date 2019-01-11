from infi.clickhouse_orm import models
from infi.clickhouse_orm.database import Database
from logchart.settings import CLICKHOUSE_HOST, CLICKHOUSE_USER, CLICKHOUSE_PASSWORD

class clickhouseModel(models.Model):

    # clickhouse 一亿元数据库的 Model对象
    @property
    def yyyModel(self):
        return Database('yiyiyuan', db_url=CLICKHOUSE_HOST, username=CLICKHOUSE_USER, password=CLICKHOUSE_PASSWORD)

    # clickhouse 花生米富数据库的 Model对象
    @property
    def peanutModel(self):
        return Database('peanut', db_url = CLICKHOUSE_HOST, username = CLICKHOUSE_USER, password = CLICKHOUSE_PASSWORD)

    # clickhouse 智融钥匙数据库的 Model对象
    @property
    def zrkeyModel(self):
        return Database('zrkey', db_url = CLICKHOUSE_HOST, username = CLICKHOUSE_USER, password = CLICKHOUSE_PASSWORD)

    # clickhouse 有卡有钱数据库的 Model对象
    @property
    def ykyqModel(self):
        return Database('youkayouqian', db_url = CLICKHOUSE_HOST, username = CLICKHOUSE_USER, password = CLICKHOUSE_PASSWORD)

    # clickhouse 风控数据库的 Model对象
    @property
    def riskModel(self):
        return Database('risk', db_url = CLICKHOUSE_HOST, username = CLICKHOUSE_USER, password = CLICKHOUSE_PASSWORD)

