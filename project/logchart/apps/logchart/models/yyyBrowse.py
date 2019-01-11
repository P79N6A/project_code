from django.db import models
from apps.helper.timeHelper import timeHelper

class yyyBrowseModel(models.Model):
    equipmentList = {1: "安卓", 2: "苹果", 3: "微信",}

    id = models.AutoField(primary_key=True)
    page_name = models.IntegerField(null=False)
    browse_num = models.IntegerField(null=False)
    equipment = models.IntegerField(null=False)
    data = models.DateTimeField(null=False)
    create_time = models.DateTimeField(null=True)

    indexes = [
        models.Index(fields=['id']),
        models.Index(fields=['create_time']),
        models.Index(fields=['equipment']),
        models.Index(fields=['page_name']),
    ]

    class Meta:
        db_table ="yyy_browse"

    # 获取所有设备的浏览量, timeRange为查询时间区间
    def getBrowseNum(self, timeRange=None):
        if not timeRange:
            timeRange = ['2000-01-01 00:00:00', '2100-12-31 23:59:59']
        data = yyyBrowseModel.objects.filter(data__range=timeRange).aggregate(total=models.Sum('browse_num'))
        return data.get('total') if data.get('total') != None else 0

    # 获取所有设备的浏览量, timeRange为查询时间区间, pageId为对应页面Id, equipment为对应客户端
    def getBrowseData(self, timeRange=None, pageId=-1, equipment=-1):
        if not timeRange:
            timeRange = ['2000-01-01 00:00:00', '2100-12-31 23:59:59']
        if pageId == -1 and equipment == -1:
            data = yyyBrowseModel.objects.filter(data__range=timeRange).order_by('-create_time').all()
        elif pageId != -1 and equipment == -1:
            data = yyyBrowseModel.objects.filter(data__range=timeRange, page_name=pageId).order_by('-create_time').all()
        elif pageId == -1 and equipment != -1:
            data = yyyBrowseModel.objects.filter(data__range=timeRange, equipment=equipment).order_by('-create_time').all()
        else:
            data = yyyBrowseModel.objects.filter(data__range=timeRange, equipment=equipment,page_name=pageId).order_by('-create_time').all()
        return data

    # 获取数据中最大时间与最大时间下一小时的时间
    def getMaxtime(self, equipment):
        lastRow = yyyBrowseModel.objects.filter(equipment=equipment).values('data').order_by('-data').first()
        lastTimestamp = timeHelper.datetime2Timestamp(lastRow['data'])
        nowTimestamp = lastTimestamp + 3600
        nowDateTime = timeHelper.getFormatDate('now', nowTimestamp)
        beforeDateTime = timeHelper.getFormatDate('now', lastTimestamp)
        return {'now': nowDateTime, 'before': beforeDateTime}

    # 保存数据
    def saveBrowse(self, page_name, browse_num, equipment, data):
        haveData = yyyBrowseModel.objects.filter(data=data, equipment=equipment, page_name=page_name, browse_num=browse_num).count()
        if haveData == 0:
            yyyBrowseModel(
                page_name=page_name,
                browse_num=browse_num,
                equipment=equipment,
                create_time=timeHelper.getFormatDate('now'),
                data=data,
            ).save()
