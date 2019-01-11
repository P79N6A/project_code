from infi.clickhouse_orm import fields, engines
from apps.clickhouse.models.clickhouseModel import clickhouseModel

class yafAndroidLbs(clickhouseModel):
    creat_time = fields.StringField()
    mil_timestmp = fields.StringField()
    itime = fields.StringField()
    user_id = fields.StringField()
    uuid = fields.StringField()
    platform = fields.StringField()
    generation = fields.StringField()
    wifi = fields.StringField()
    ssid = fields.StringField()
    ip = fields.StringField()
    cell_id = fields.StringField()
    location_area_code = fields.StringField()
    mobile_country_code = fields.StringField()
    mobile_network_code = fields.StringField()
    radio_type = fields.StringField()
    type = fields.StringField()
    network_type = fields.StringField()
    coordinate_source = fields.StringField()
    coordinate = fields.StringField()
    network_speed = fields.StringField()
    wifi_lists = fields.StringField()
    bluetooth_lists = fields.StringField()
    mHasSpeed = fields.StringField()
    mSpeed = fields.StringField()
    mHasRadius = fields.StringField()
    mRadius = fields.StringField()
    netWorkLocationType = fields.StringField()
    locationID = fields.StringField()
    theday = fields.DateField()

    engines = engines.MergeTree('theday', ('user_id','theday'),8192)

    # 表名
    @classmethod
    def table_name(cls):
        return 'yaf_yyy_android_lbs_all'

    # 项目代号
    @classmethod
    def project_num(cls):
        return 2

    # 获取最大时间
    def getLastTime(self):
        lastTime = self.objects_in(self.yyyModel).filter(creat_time__between=['2000-01-01 00:00:00', '2100-12-31 23:59:59']).order_by('-creat_time').only('creat_time')
        if lastTime.count() == 0:
            return '2000-01-01 00:00:00'
        else:
            return lastTime[0].creat_time
