from apps.logchart.views.baseView import baseView
from apps.clickhouse.views.monitor import  clickhouseMonitor

class monitor():

    @baseView.checkLogin()
    def index(request):
        dataList = clickhouseMonitor().doMonitor()
        data = {"data": dataList}
        return baseView.urlRender(request, 'monitor.html', data, "monitor")
