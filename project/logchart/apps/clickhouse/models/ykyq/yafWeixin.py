from infi.clickhouse_orm import fields, engines
from apps.clickhouse.models.clickhouseModel import clickhouseModel

class yafWeixin(clickhouseModel):
    creat_time = fields.StringField()
    mil_timestmp = fields.StringField()
    _aid = fields.StringField()
    activity = fields.StringField()
    area = fields.StringField()
    channelid = fields.StringField()
    cookeid = fields.StringField()
    event_name = fields.StringField()
    from_url = fields.StringField()
    g_uid = fields.StringField()
    ip = fields.StringField()
    Is_login = fields.StringField()
    logId = fields.StringField()
    nickname = fields.StringField()
    openId = fields.StringField()
    request_time = fields.StringField()
    sessionId = fields.StringField()
    sex = fields.StringField()
    source = fields.StringField()
    url = fields.StringField()
    user_agent = fields.StringField()
    uuid = fields.StringField()
    theday = fields.DateField()

    engines= engines.MergeTree('theday', ('g_uid','theday'),8192)

    # 表名
    @classmethod
    def table_name(cls):
        return 'yaf_youkayouqian_weixin_all'

    # 项目代号
    @classmethod
    def project_num(cls):
        return 4

    # 获取最大时间
    def getLastTime(self):
        lastTime = self.objects_in(self.ykyqModel).filter(creat_time__between=['2000-01-01 00:00:00', '2100-12-31 23:59:59']).order_by('-creat_time').only('creat_time')
        if lastTime.count() == 0:
            return '2000-01-01 00:00:00'
        else:
            return lastTime[0].creat_time
