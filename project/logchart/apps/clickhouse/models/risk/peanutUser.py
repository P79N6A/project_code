from infi.clickhouse_orm import fields, engines
from apps.clickhouse.models.clickhouseModel import clickhouseModel

class peanutUser(clickhouseModel):
    user_id = fields.StringField()
    nickname = fields.StringField()
    status = fields.StringField()
    mobile = fields.StringField()
    identity = fields.StringField()
    realname = fields.StringField()
    last_login_time = fields.StringField()
    create_time = fields.StringField()
    last_modify_time = fields.StringField()
    theday = fields.DateField()

    engines = engines.MergeTree('theday', ('user_id', 'theday'), 8192)

    # 表名
    @classmethod
    def table_name(cls):
        return 'pea_user_all'

    def count(self, user_id):
        return self.objects_in(self.riskModel).filter(user_id = user_id).count()

    def batchSave(self, insertList):
        try:
            self.riskModel.insert(insertList)
            return True
        except:
            return False