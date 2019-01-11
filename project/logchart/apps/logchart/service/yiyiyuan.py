from apps.logchart.models.yyyRegister import yyyRegisterModel
from apps.logchart.models.yyyBrowse import yyyBrowseModel
from apps.logchart.models.yyyPageName import  yyyPageNameModel
from django.core.paginator import Paginator
from apps.helper.pie import pie

class yiyiyuan():
    pageSize = 50

    # 获取今天与昨天的数据量
    def getDayData(self, dayDate):
        todayRange = dayDate.get('today')
        yesterdayRange = dayDate.get('yesterday')

        todayRegister = yyyRegisterModel().getRegisterNum(todayRange)
        yesterdayRegister = yyyRegisterModel().getRegisterNum(yesterdayRange)
        todayBrowse = yyyBrowseModel().getBrowseNum(todayRange)
        yesterdayBrowse = yyyBrowseModel().getBrowseNum(yesterdayRange)

        return {
            "todayRegister": todayRegister,
            "yesterdayRegister": yesterdayRegister,
            "todayBrowse": todayBrowse,
            "yesterdayBrowse": yesterdayBrowse,
        }

    # 获取当前周与上周的数据量
    def getWeekData(self, weekDate):
        thisWeekRange = weekDate.get('thisWeek')
        lastWeekRange = weekDate.get('lastWeek')

        thisWeekRegister = yyyRegisterModel().getRegisterNum(thisWeekRange)
        lastWeekRegister = yyyRegisterModel().getRegisterNum(lastWeekRange)
        thisWeekBrowse = yyyBrowseModel().getBrowseNum(thisWeekRange)
        lastWeekBrowse = yyyBrowseModel().getBrowseNum(lastWeekRange)

        return {
            "thisWeekRegister": thisWeekRegister,
            "lastWeekRegister": lastWeekRegister,
            "thisWeekBrowse": thisWeekBrowse,
            "lastWeekBrowse": lastWeekBrowse,
        }

    # 获取时间区间内的各客户端的注册量
    def getRegisterPieRangeData(self, timeRange):
        rangeRegisterTotal = yyyRegisterModel().getRegisterNum(timeRange)
        rangeEachEquipRegister = yyyRegisterModel().getEquipRegister(timeRange)
        registerPieData = pie("注册总数" + str(rangeRegisterTotal), "注册量").set_data(rangeEachEquipRegister)
        registerPieData.update({"pieId": 'register'})
        return registerPieData

    # 获取时间区间内的各页面的浏览量
    def getBrowseData(self, timeRange, pageId, equipment, page=1):
        pageNameList = yyyPageNameModel().getPageNameList()
        browseData = yyyBrowseModel().getBrowseData(timeRange, pageId, equipment)
        paginator = Paginator(browseData, self.pageSize)
        pageData = paginator.get_page(page)
        for rowData in pageData:
            rowData.pageName = pageNameList[rowData.page_name] if pageNameList[rowData.page_name] != None else '未知页面'
            rowData.client = yyyBrowseModel().equipmentList[rowData.equipment]
            rowData.browseNum = rowData.browse_num
        return {"pageNameList": pageNameList, "contacts": pageData,}
