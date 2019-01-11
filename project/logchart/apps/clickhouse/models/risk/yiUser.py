from infi.clickhouse_orm import fields, engines
from apps.clickhouse.models.clickhouseModel import clickhouseModel

class yiUser(clickhouseModel):
    user_id = fields.StringField()
    mobile = fields.StringField()
    status = fields.StringField()
    realname = fields.StringField()
    identity = fields.StringField()
    come_from = fields.StringField()
    down_from = fields.StringField()
    create_time = fields.StringField()
    birth_year = fields.StringField()
    last_login_time = fields.StringField()
    verify_time = fields.StringField()
    theday = fields.DateField()

    engines = engines.MergeTree('theday', ('user_id', 'theday'), 8192)

    # 表名
    @classmethod
    def table_name(cls):
        return 'yi_user_all'

    def count(self, user_id):
        return self.objects_in(self.riskModel).filter(user_id = user_id).count()

    def batchSave(self, insertList):
        try:
            self.riskModel.insert(insertList)
            return True
        except:
            return False