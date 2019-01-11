from apps.logchart.models.peanutIosPageName import peanutIosPageNameModel
from apps.logchart.models.yyyIosPageName import yyyIosPageNameModel
from apps.logchart.models.peanutWebPageName import peanutWebPageNameModel
from apps.logchart.models.yyyPageName import  yyyPageNameModel
from apps.helper.timeHelper import timeHelper

class clearData():
    @classmethod
    def doClear(cls):
        peanutIosPageNameModel.objects.filter(page_name = '').delete()
        yyyIosPageNameModel.objects.filter(page_name = '').delete()
        peanutWebPageNameModel.objects.filter(page_name_id = 404).delete()
        now = timeHelper.getFormatDate('now')
        yyyPageNameModel(page_name_id = 0, page_name = '未知页面', create_time=now).save()
