from apps.logchart.views.baseView import baseView
from apps.logchart.service.yiyiyuan import yiyiyuan as yiyiyuanService
from apps.helper.timeHelper import timeHelper

class yiyiyuan():

    # 返回一亿元概览页面
    @baseView.checkLogin()
    def index(request):
        startDate = request.GET.get('startDate', '')
        endDate = request.GET.get('endDate', '')
        if not startDate:
            startDate = timeHelper.getFormatDate('start')
        if not endDate:
            endDate = timeHelper.getFormatDate('end')
        registerPieData = yiyiyuanService().getRegisterPieRangeData([startDate, endDate])
        data = {"data": [registerPieData,], "startDate": startDate, "endDate": endDate}
        return baseView.urlRender(request, 'yiyiyuan.html', data, "yiyiyuan")

    # 返回一亿元注册饼图页
    @baseView.checkLogin()
    def register(request):
        startDate = request.GET.get('startDate', '')
        endDate = request.GET.get('endDate', '')
        if not startDate:
            startDate = timeHelper.getFormatDate('start')
        if not endDate:
            endDate = timeHelper.getFormatDate('end')
        registerPieData = yiyiyuanService().getRegisterPieRangeData([startDate, endDate])
        data = {"data": [registerPieData], "startDate": startDate, "endDate": endDate}
        return baseView.urlRender(request, 'yiyiyuan_register.html', data, "yiyiyuan")

    # 返回一亿元浏览表格页
    @baseView.checkLogin()
    def browse(request):
        startDate = request.GET.get('startDate')
        endDate = request.GET.get('endDate')
        if not startDate:
            startDate = timeHelper.getFormatDate('start')
        if not endDate:
            endDate = timeHelper.getFormatDate('end')
        pageId = int(request.GET.get('pageId', -1))
        equipment = int(request.GET.get('equipment', -1))
        timeRange = [startDate, endDate]
        page = request.GET.get('page', 1)
        getBrowseData = yiyiyuanService().getBrowseData(timeRange, pageId, equipment, page)
        data = {
            "pageNameList": getBrowseData['pageNameList'].items(),
            "contacts": getBrowseData['contacts'],
            "pageId": pageId,
            "equipment": equipment,
            "startDate": startDate,
            "endDate": endDate
        }
        return baseView.urlRender(request, 'yiyiyuan_browse.html', data, "yiyiyuan")
