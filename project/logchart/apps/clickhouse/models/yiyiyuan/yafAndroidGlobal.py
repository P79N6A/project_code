from infi.clickhouse_orm import fields, engines
from apps.clickhouse.models.clickhouseModel import clickhouseModel

class yafAndroidGlobal(clickhouseModel):
    creat_time = fields.StringField()
    mil_timestmp = fields.StringField()
    g_eventname = fields.StringField()
    g_url = fields.StringField()
    g_sessionId = fields.StringField()
    g_uid = fields.StringField()
    g_ip = fields.StringField()
    g_build = fields.StringField()
    g_source = fields.StringField()
    g_channelid = fields.StringField()
    g_activity = fields.StringField()
    g_uuid = fields.StringField()
    _residence_time = fields.StringField()
    _source_page = fields.StringField()
    _share_friend = fields.StringField()
    _share_c_friend = fields.StringField()
    coupon_amount = fields.StringField()
    logId = fields.StringField()
    _location_status = fields.StringField()
    _notice_status = fields.StringField()
    _contacts_status = fields.StringField()
    request_result = fields.StringField()
    password_show = fields.StringField()
    loan_amount = fields.StringField()
    loan_period = fields.StringField()
    loan_reason = fields.StringField()
    paytype = fields.StringField()
    h5_url = fields.StringField()
    status = fields.StringField()
    theday = fields.DateField()

    engines = engines.MergeTree('theday', ('g_uid','theday'),8192)

    # 表名
    @classmethod
    def table_name(cls):
        return 'yaf_yyy_android_global_all'

    # 项目代号
    @classmethod
    def project_num(cls):
        return 2

    # 客户端代号
    @classmethod
    def client_num(cls):
        return 1

    # 获取最大时间
    def getLastTime(self):
        lastTime = self.objects_in(self.yyyModel).filter(creat_time__between=['2000-01-01 00:00:00', '2100-12-31 23:59:59']).order_by('-creat_time').only('creat_time')
        if lastTime.count() == 0:
            return '2000-01-01 00:00:00'
        else:
            return lastTime[0].creat_time

    # 获取每小时的注册量
    def getRegisterNum(self, now, before):
        haveData = self.objects_in(self.yyyModel).filter(creat_time__gte=now).count()
        if haveData > 0:
            maxSize = self.objects_in(self.yyyModel).filter(creat_time__lte=now).only('g_uid').distinct().count()
            minSize = self.objects_in(self.yyyModel).filter(creat_time__lte=before).only('g_uid').distinct().count()
            size = maxSize - minSize
        else:
            size = -1
        return size

    # 获取页面的浏览量
    def getBrowseNum(self, now, before):
        haveData = self.objects_in(self.yyyModel).filter(creat_time__gte=now).count()
        if haveData <= 0:
            return []
        browseData = self.objects_in(self.yyyModel).filter(creat_time__between=[before, now],g_url__ne='empty').aggregate('g_url', sum='count()')
        if browseData.count() == 0:
            return [["Main", 0]]
        browseList = []
        for browse in browseData:
            browseList.append([browse.g_url, browse.sum])
        return browseList
