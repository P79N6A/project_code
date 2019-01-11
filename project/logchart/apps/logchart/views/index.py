from apps.logchart.views.baseView import baseView
from apps.logchart.service.peanut import peanut
from apps.logchart.service.yiyiyuan import yiyiyuan
# from apps.logchart.views import youkayouqian
# from apps.logchart.views import zrkey
from apps.logchart.service.clearData import clearData
from apps.helper.timeHelper import timeHelper

class index():

    @baseView.checkLogin()
    def index(request):
        # 获取今天的起止时间
        todayStart = timeHelper.getFormatDate('start')
        todayEnd = timeHelper.getFormatDate('end')
        # 获取昨天的起止时间
        yesterdayStart = timeHelper.getFormatDate('start', timeHelper.getTimestamp(-60*60*24))
        yesterdayEnd = timeHelper.getFormatDate('end', timeHelper.getTimestamp(-60*60*24))
        dayRange = {"today" : [todayStart, todayEnd], "yesterday" : [yesterdayStart, yesterdayEnd]}
        # 获取天维度的数据
        peanutDayData = peanut().getDayData(dayRange)
        yiyiyuanDayData = yiyiyuan().getDayData(dayRange)

        # 获取今天的是周几
        num = timeHelper.getWeekNum()
        # 获取这周的起止时间
        thisWeekStart = timeHelper.getFormatDate('start', timeHelper.getTimestamp(-60*60*24*num))
        thisWeekStartEnd = timeHelper.getFormatDate('end')
        # 获取上周的起止时间
        lastWeekStart = timeHelper.getFormatDate('start', timeHelper.getTimestamp(-60*60*24*(num+7)))
        lastWeekEend = timeHelper.getFormatDate('end', timeHelper.getTimestamp(-60*60*24*(num+1)))
        weekRange ={"thisWeek" : [thisWeekStart, thisWeekStartEnd] , "lastWeek" : [lastWeekStart, lastWeekEend]}
        # 获取周维度的数据
        peanutWeekData = peanut().getWeekData(weekRange)
        yiyiyuanWeekData = yiyiyuan().getWeekData(weekRange)

        data = {
            "peanutData":{
                'name': '花生米富',
                'dayData': peanutDayData,
                'weekData': peanutWeekData,
            },
            "yiyiyuanData":{
                'name': '先花一亿元',
                'dayData': yiyiyuanDayData,
                'weekData': yiyiyuanWeekData,
            },
        }
        # 跳转到展示页面
        return baseView.urlRender(request, 'index.html', data, "index")

    @baseView.checkLogin()
    def clearData(request):
        clearData.doClear()
        return baseView.successJson()
