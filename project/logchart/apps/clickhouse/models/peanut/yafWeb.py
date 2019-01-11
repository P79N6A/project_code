from infi.clickhouse_orm import fields, engines
from apps.clickhouse.models.clickhouseModel import clickhouseModel
import re

class yafWeb(clickhouseModel):
    creat_time = fields.StringField()
    mil_timestmp = fields.StringField()
    _aid = fields.StringField()
    activity = fields.StringField()
    channelid = fields.StringField()
    cookieId = fields.StringField()
    from_url = fields.StringField()
    g_uid = fields.StringField()
    height = fields.StringField()
    ip = fields.StringField()
    Is_login = fields.StringField()
    logId = fields.StringField()
    request_time = fields.StringField()
    sreen_height = fields.StringField()
    screen_width = fields.StringField()
    sessionId = fields.StringField()
    source = fields.StringField()
    url = fields.StringField()
    user_agent = fields.StringField()
    uuid = fields.StringField()
    width = fields.StringField()
    theday = fields.DateField()

    engines = engines.MergeTree('theday', ('g_uid', 'theday'), 8192)

    # 表名
    @classmethod
    def table_name(cls):
        return 'yaf_peanut_web_all'

    # 项目代号
    @classmethod
    def project_num(cls):
        return 1

    # 客户端代号
    @classmethod
    def client_num(cls):
        return 4

    # 获取最大时间
    def getLastTime(self):
        lastTime = self.objects_in(self.peanutModel).filter(creat_time__between=['2000-01-01 00:00:00', '2100-12-31 23:59:59']).order_by('-creat_time').only('creat_time')
        if lastTime.count() == 0:
            return '2000-01-01 00:00:00'
        else:
            return lastTime[0].creat_time

    # 获取每小时的注册量
    def getRegisterNum(self, now, before):
        haveData = self.objects_in(self.peanutModel).filter(creat_time__gte=now).count()
        if haveData > 0:
            maxSize = self.objects_in(self.peanutModel).filter(creat_time__lte=now).only('g_uid').distinct().count()
            minSize = self.objects_in(self.peanutModel).filter(creat_time__lte=before).only('g_uid').distinct().count()
            size = maxSize - minSize
        else:
            size = -1
        return size

    # 获取页面的浏览量
    def getBrowseNum(self, now, before):
        haveData = self.objects_in(self.peanutModel).filter(creat_time__gte=now).count()
        if haveData <= 0:
            return []
        browseData = self.objects_in(self.peanutModel).filter(creat_time__between=[before, now], url__ne='empty').only('url')
        if browseData.count() == 0:
            return [["https://www.yaoyuefu.com/", 0]]

        browseList = {}
        for browse in browseData:
            pattern = re.compile(r'\w*\:\/\/[\w*.]*[\w*\/]*\?type=\d*|\w*\:\/\/[\w*.]*[\w*\/]*\?id=|\w*\:\/\/[\w*.]*[\w*\/]*/?|\w*\:\/\/[\w*.]*[\w*\/]*')
            result = pattern.findall(browse.url)
            if len(result) > 0:
                if result[0] in browseList:
                    browseList[result[0]] += 1
                else:
                    browseList[result[0]] = 1
        if len(browseList) == 0:
            return [["https://www.yaoyuefu.com/", 0]]

        urlList = []
        for url,num in browseList.items():
            urlList.append([url,num])
        return urlList
