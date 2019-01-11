from apps.logchart.models.peanutRegister import peanutRegisterModel
from apps.logchart.models.peanutBrowse import peanutBrowseModel
from apps.logchart.models.peanutBid import peanutBidModel
from apps.logchart.models.peanutPageName import peanutPageNameModel
from django.core.paginator import Paginator
from apps.helper.pie import pie

class peanut():
    pageSize = 50

    # 获取今天与昨天的数据量
    def getDayData(self, dayRange):
        todayRange = dayRange.get('today')
        yesterdayRange = dayRange.get('yesterday')

        todayRegister = peanutRegisterModel().getRegisterNum(todayRange)
        yesterdayRegister = peanutRegisterModel().getRegisterNum(yesterdayRange)

        todayBrowse = peanutBrowseModel().getBrowseNum(todayRange)
        yesterdayBrowse = peanutBrowseModel().getBrowseNum(yesterdayRange)

        todayBid = peanutBidModel().getBidNum(todayRange)
        yesterdayBid = peanutBidModel().getBidNum(yesterdayRange)

        todaySubscribe = peanutBidModel().getSubscribeNum(todayRange)
        yesterdaySubscribe = peanutBidModel().getSubscribeNum(yesterdayRange)

        return {
            "todayRegister" : todayRegister,
            "yesterdayRegister": yesterdayRegister,
            "todayBrowse": todayBrowse,
            "yesterdayBrowse": yesterdayBrowse,
            "todayBid": todayBid,
            "yesterdayBid": yesterdayBid,
            "todaySubscribe": todaySubscribe,
            "yesterdaySubscribe": yesterdaySubscribe,
        }

    # 获取当前周与上周的数据量
    def getWeekData(self, weekRange):
        thisWeekRange = weekRange.get('thisWeek')
        lastWeekRange = weekRange.get('lastWeek')

        thisWeekRegister = peanutRegisterModel().getRegisterNum(thisWeekRange)
        lastWeekRegister = peanutRegisterModel().getRegisterNum(lastWeekRange)

        thisWeekBrowse = peanutBrowseModel().getBrowseNum(thisWeekRange)
        lastWeekBrowse = peanutBrowseModel().getBrowseNum(lastWeekRange)

        thisWeekBid = peanutBidModel().getBidNum(thisWeekRange)
        lastWeekBid = peanutBidModel().getBidNum(lastWeekRange)

        thisWeekSubscribe = peanutBidModel().getSubscribeNum(thisWeekRange)
        lastWeekSubscribe = peanutBidModel().getSubscribeNum(lastWeekRange)

        return {
            "thisWeekRegister": thisWeekRegister,
            "lastWeekRegister": lastWeekRegister,
            "thisWeekBrowse": thisWeekBrowse,
            "lastWeekBrowse": lastWeekBrowse,
            "thisWeekBid": thisWeekBid,
            "lastWeekBid": lastWeekBid,
            "thisWeekSubscribe": thisWeekSubscribe,
            "lastWeekSubscribe": lastWeekSubscribe,
        }

    # 获取时间区间内的各客户端的注册量
    def getRegisterPieRangeData(self, timeRange):
        rangeRegisterTotal = peanutRegisterModel().getRegisterNum(timeRange)
        rangeEachEquipRegister = peanutRegisterModel().getEquipRegister(timeRange)
        registerPieData = pie("注册总数" + str(rangeRegisterTotal), "注册量").set_data(rangeEachEquipRegister)
        registerPieData.update({"pieId": 'register'})
        return registerPieData

    # 获取时间区间内的各客户端的投标量
    def getBidPieRangeData(self, timeRange):
        rangeBidTotal = peanutBidModel().getBidNum(timeRange)
        rangeEachEquipBid = peanutBidModel().getEquipBid(timeRange)
        bidPieData = pie("投标总数" + str(rangeBidTotal), "投标量").set_data(rangeEachEquipBid)
        bidPieData.update({"pieId": 'bid'})
        return bidPieData

    # 获取时间区间内的各客户端的订阅量
    def getSubscribePieRangeData(self, timeRange):
        rangeSubscribeTotal = peanutBidModel().getSubscribeNum(timeRange)
        rangeEachEquipSubscribe = peanutBidModel().getEquipSubscribe(timeRange)
        subscribePieData = pie("订阅总数" + str(rangeSubscribeTotal), "订阅量").set_data(rangeEachEquipSubscribe)
        subscribePieData.update({"pieId": 'subscribe'})
        return subscribePieData

    # 获取时间区间内的各页面的浏览量
    def getBrowseData(self, timeRange, pageId, equipment, page = 1):
        pageNameList = peanutPageNameModel().getPageNameList()
        browseData = peanutBrowseModel().getBrowseData(timeRange, pageId, equipment)
        paginator = Paginator(browseData, self.pageSize)
        pageData = paginator.get_page(page)
        for rowData in pageData:
            rowData.pageName = pageNameList[rowData.page_name] if pageNameList[rowData.page_name] != None else '未知页面'
            rowData.client = peanutBrowseModel().equipmentList[rowData.equipment]
            rowData.browseNum = rowData.browse_num
        return {"pageNameList": pageNameList, "contacts": pageData,}
