from django.db import models
from apps.helper.timeHelper import timeHelper

class peanutRegisterModel(models.Model):

    id = models.AutoField(primary_key=True)
    register_num = models.IntegerField(null=False)
    equipment = models.IntegerField(null=False)
    data = models.DateTimeField(null=False)
    create_time = models.DateTimeField(null=True)

    class Meta:
        db_table = "pea_register"
        indexes = [
            models.Index(fields=['id']),
            models.Index(fields=['create_time']),
            models.Index(fields=['equipment']),
        ]

    # 获取所有设备的注册量, timeRange为查询时间区间
    def getRegisterNum(self, timeRange = None):
        if not timeRange:
            timeRange = ['2000-01-01 00:00:00', '2100-12-31 23:59:59']
        data = peanutRegisterModel.objects.filter(data__range=timeRange).aggregate(total=models.Sum('register_num'))
        return data.get('total') if  data.get('total') != None else 0

    # 获取各种设备的注册量, timeRange为查询时间区间
    def getEquipRegister(self, timeRange=None):
        if not timeRange:
            timeRange = ['2000-01-01 00:00:00', '2100-12-31 23:59:59']
        data = peanutRegisterModel.objects.values('equipment').filter(data__range=timeRange).annotate(total=models.Sum('register_num'))
        return data

    # 获取最近的插入时间
    def getMaxtime(self, equipment):
        lastRow = peanutRegisterModel.objects.filter(equipment=equipment).values('data').order_by('-data').first()
        lastTimestamp = timeHelper.datetime2Timestamp(lastRow['data'])
        nowTimestamp = lastTimestamp+3600
        nowDateTime = timeHelper.getFormatDate('now', nowTimestamp)
        beforeDateTime = timeHelper.getFormatDate('now', lastTimestamp)
        return {'now': nowDateTime, 'before': beforeDateTime}

    # 保存数据
    def saveRegister(self, register_num, equipment, data):
        haveData = peanutRegisterModel.objects.filter(data=data, equipment=equipment, register_num=register_num).count()
        if haveData == 0:
            peanutRegisterModel(
                register_num = register_num,
                equipment = equipment,
                create_time = timeHelper.getFormatDate('now'),
                data = data,
            ).save()
