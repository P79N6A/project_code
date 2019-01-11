from apps.logchart.views.baseView import baseView
from apps.logchart.service.peanut import peanut as peanutService
from apps.helper.timeHelper import timeHelper

class peanut():

    # 返回花生米富页面
    @baseView.checkLogin()
    def index(request):
        startDate = request.GET.get('startDate', '')
        endDate = request.GET.get('endDate', '')
        if not startDate:
            startDate = timeHelper.getFormatDate('start')
        if not endDate:
            endDate = timeHelper.getFormatDate('end')
        registerPieData = peanutService().getRegisterPieRangeData([startDate, endDate])
        bidPieData = peanutService().getBidPieRangeData([startDate, endDate])
        subscribePieData = peanutService().getSubscribePieRangeData([startDate, endDate])
        data = {"data": [registerPieData, bidPieData, subscribePieData], "startDate": startDate, "endDate": endDate}
        return baseView.urlRender(request, 'peanut.html', data, "peanut")

    # 返回花生米富注册饼图页
    @baseView.checkLogin()
    def register(request):
        startDate = request.GET.get('startDate', '')
        endDate = request.GET.get('endDate', '')
        if not startDate:
            startDate = timeHelper.getFormatDate('start')
        if not endDate:
            endDate = timeHelper.getFormatDate('end')
        registerPieData = peanutService().getRegisterPieRangeData([startDate, endDate])
        data = {"data": [registerPieData], "startDate": startDate, "endDate": endDate}
        return baseView.urlRender(request, 'peanut_register.html', data, "peanut")

    # 返回花生米富投标饼图页
    @baseView.checkLogin()
    def bid(request):
        startDate = request.GET.get('startDate', '')
        endDate = request.GET.get('endDate', '')
        if not startDate:
            startDate = timeHelper.getFormatDate('start')
        if not endDate:
            endDate = timeHelper.getFormatDate('end')
        bidPieData = peanutService().getBidPieRangeData([startDate, endDate])
        data = {"data": [bidPieData], "startDate": startDate, "endDate": endDate}
        return baseView.urlRender(request, 'peanut_bid.html', data, "peanut")

    # 返回花生米富订阅饼图页
    @baseView.checkLogin()
    def subscribe(request):
        startDate = request.GET.get('startDate', '')
        endDate = request.GET.get('endDate', '')
        if not startDate:
            startDate = timeHelper.getFormatDate('start')
        if not endDate:
            endDate = timeHelper.getFormatDate('end')
        subscribePieData = peanutService().getSubscribePieRangeData([startDate, endDate])
        data = {"data": [subscribePieData], "startDate": startDate, "endDate": endDate}
        return baseView.urlRender(request, 'peanut_subscribe.html', data, "peanut")

    # 返回花生米富浏览表格页
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
        getBrowseData = peanutService().getBrowseData(timeRange, pageId, equipment, page)
        print(equipment)
        data = {
            "pageNameList": getBrowseData['pageNameList'].items(),
            "contacts": getBrowseData['contacts'],
            "pageId": pageId,
            "equipment": equipment,
            "startDate": startDate,
            "endDate": endDate
        }
        return baseView.urlRender(request, 'peanut_browse.html', data, "peanut")
