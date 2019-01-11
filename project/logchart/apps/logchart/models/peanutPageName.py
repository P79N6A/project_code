from django.db import models
from apps.helper.timeHelper import timeHelper

class peanutPageNameModel(models.Model):
    id = models.AutoField(primary_key=True)
    page_name_id = models.IntegerField(null=False)
    page_name = models.CharField(null=True,max_length=128)
    create_time = models.DateTimeField(null=True)

    indexes = [
        models.Index(fields=['id']),
        models.Index(fields=['page_name_id']),
        models.Index(fields=['page_name']),
    ]

    class Meta:
        db_table ="pea_page_name"

    def getPageNameList(self, pageIds = None):
        if pageIds == None:
            selectPage = peanutPageNameModel.objects.only('page_name_id', 'page_name').all()
        else:
            selectPage = peanutPageNameModel.objects.filter(page_name_id__in = pageIds).only('page_name_id', 'page_name').all()
        pageNameList = {}
        for eachPage in selectPage:
            pageNameList[eachPage.page_name_id] = eachPage.page_name
        return pageNameList

    def savePageName(self, pageName):
        data = peanutPageNameModel.objects.all().aggregate(valueId = models.Max('page_name_id'))
        valueId = data.get('valueId') if data.get('valueId') != None else 0
        valueId += 1
        now = timeHelper.getFormatDate('now')
        ob = peanutPageNameModel(page_name_id= valueId, page_name= pageName, create_time= now)
        ob.save()
        if ob.id != None:
            return ob.page_name_id
        else:
            return 0
