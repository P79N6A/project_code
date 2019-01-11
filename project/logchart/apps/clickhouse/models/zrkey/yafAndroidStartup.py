from infi.clickhouse_orm import fields, engines
from apps.clickhouse.models.clickhouseModel import clickhouseModel

class yafAndroidStartup(clickhouseModel):
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
    request_result = fields.StringField()
    password_show = fields.StringField()
    payType = fields.StringField()
    _models = fields.StringField()
    _screen = fields.StringField()
    _startup_time = fields.StringField()
    _resolution = fields.StringField()
    _systemversion = fields.StringField()
    _mem = fields.StringField()
    _storage = fields.StringField()
    _cpu = fields.StringField()
    _charge = fields.StringField()
    _rem_charge = fields.StringField()
    _bluetooth = fields.StringField()
    _bluetooth_status = fields.StringField()
    _language = fields.StringField()
    _operator = fields.StringField()
    _light = fields.StringField()
    _imei = fields.StringField()
    _mac = fields.StringField()
    _gps = fields.StringField()
    _net = fields.StringField()
    _wifi = fields.StringField()
    _bssid = fields.StringField()
    _gyro = fields.StringField()
    _gyro_info = fields.StringField()
    _app_version = fields.StringField()
    _is_login = fields.StringField()
    _last_start_time = fields.StringField()
    _last_end_time = fields.StringField()
    theday = fields.DateField()

    engines = engines.MergeTree('theday', ('g_uid','theday'),8192)

    # 表名
    @classmethod
    def table_name(cls):
        return 'yaf_zrkey_android_startup_all'

    # 项目代号
    @classmethod
    def project_num(cls):
        return 3

    # 获取最大时间
    def getLastTime(self):
        lastTime = self.objects_in(self.zrkeyModel).filter(creat_time__between=['2000-01-01 00:00:00', '2100-12-31 23:59:59']).order_by('-creat_time').only('creat_time')
        if lastTime.count() == 0:
            return '2000-01-01 00:00:00'
        else:
            return lastTime[0].creat_time
