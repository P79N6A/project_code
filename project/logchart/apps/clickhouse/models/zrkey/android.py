from infi.clickhouse_orm import fields, engines
from apps.clickhouse.models.clickhouseModel import clickhouseModel

class android(clickhouseModel):
    source = fields.StringField()
    request_time = fields.StringField()
    start_time = fields.StringField()
    sessionId = fields.StringField()
    ip = fields.StringField()
    g_uid = fields.StringField()
    is_login = fields.StringField()
    channelid = fields.StringField()
    activity = fields.StringField()
    user_agent = fields.StringField()
    from_url = fields.StringField()
    url = fields.StringField()
    cookieId = fields.StringField()
    logId = fields.StringField()
    _aid = fields.StringField()
    sign = fields.StringField()
    uuid = fields.StringField()
    end_time = fields.StringField()
    taken_time = fields.StringField()
    theday = fields.DateField()

    engines = engines.MergeTree('theday', ('g_uid','theday'),8192)

    # 表名
    @classmethod
    def table_name(cls):
        return 'zrkey_android_all'

    # 项目代号
    @classmethod
    def project_num(cls):
        return 3

    # 获取最大时间
    def getLastTime(self):
        lastTime = self.objects_in(self.zrkeyModel).filter(request_time__between=['2000-01-01 00:00:00', '2100-12-31 23:59:59']).order_by('-request_time').only('request_time')
        if lastTime.count() == 0:
            return '2000-01-01 00:00:00'
        else:
            return lastTime[0].request_time
