from django.db import models
from apps.helper.timeHelper import timeHelper

class peanutBidModel(models.Model):
    bidValue = 1 # 投标数据的bid_type = 1
    subscribeValue = 2 # 订阅数据的bid_type = 2

    id = models.AutoField(primary_key=True)
    bid_num = models.IntegerField(null=False)
    bid_type = models.IntegerField(null=False)
    equipment = models.IntegerField(null=False)
    data = models.DateTimeField(null=False)
    create_time = models.DateTimeField(null=True)

    indexes = [
        models.Index(fields=['id']),
        models.Index(fields=['bid_type']),
        models.Index(fields=['create_time']),
        models.Index(fields=['equipment']),
    ]

    class Meta:
        db_table = "pea_bid"

    # 获取所有设备的投标量, timeRange为查询时间区间
    def getBidNum(self, timeRange=None):
        if not timeRange:
            timeRange = ['2000-01-01 00:00:00', '2100-12-31 23:59:59']

        data = peanutBidModel.objects.filter(data__range=timeRange, bid_type=self.bidValue).aggregate(total=models.Sum('bid_num'))
        return data.get('total') if data.get('total') != None else 0

    # 获取各种设备的注册量, timeRange为查询时间区间
    def getEquipBid(self, timeRange=None):
        if not timeRange:
            timeRange = ['2000-01-01 00:00:00', '2100-12-31 23:59:59']
        data = peanutBidModel.objects.values('equipment').filter(data__range=timeRange, bid_type=self.bidValue).annotate(total=models.Sum('bid_num'))
        return data

    # 获取所有设备的订阅量, timeRange为查询时间区间
    def getSubscribeNum(self, timeRange=None):
        if not timeRange:
            timeRange = ['2000-01-01 00:00:00', '2100-12-31 23:59:59']

        data = peanutBidModel.objects.filter(data__range=timeRange, bid_type=self.subscribeValue).aggregate(total=models.Sum('bid_num'))
        return data.get('total') if data.get('total') != None else 0

    # 获取各种设备的订阅量, timeRange为查询时间区间
    def getEquipSubscribe(self, timeRange=None):
        if not timeRange:
            timeRange = ['2000-01-01 00:00:00', '2100-12-31 23:59:59']
        data = peanutBidModel.objects.values('equipment').filter(data__range=timeRange, bid_type=self.subscribeValue).annotate(total=models.Sum('bid_num'))
        return data

    # 保存数据
    def saveBid(self, bidNum, bidType, equipment, data):
        haveData = peanutBidModel.objects.filter(data=data, equipment=equipment, bid_type=bidType, bid_num=bidNum).count()
        if haveData == 0:
            peanutBidModel(
                bid_num = bidNum,
                bid_type = bidType,
                equipment=equipment,
                create_time=timeHelper.getFormatDate('now'),
                data=data,
            ).save()