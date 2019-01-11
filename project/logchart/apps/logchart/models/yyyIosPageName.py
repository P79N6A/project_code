from django.db import models
from apps.helper.timeHelper import timeHelper

class yyyIosPageNameModel(models.Model):
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
        db_table ="yyy_ios_page_name"

    # 客户端代号
    @classmethod
    def clientNum(cls):
        return 2

    def getUrlList(self, pageId = -1):
        if pageId == -1:
            return yyyIosPageNameModel.objects.values('id', 'page_name_id', 'page_name').all()
        else:
            return yyyIosPageNameModel.objects.filter(page_name_id = pageId).values('id', 'page_name_id', 'page_name').all()

    def getPageIds(self, pageNames):
        pageIds = yyyIosPageNameModel.objects.filter(page_name__in=pageNames).values('page_name_id', 'page_name').all()
        pageList = {}
        pageNameList = []
        if len(pageIds) != 0:
            for pageId in pageIds:
                pageList[pageId['page_name']] = pageId['page_name_id']
                pageNameList.append(pageId['page_name'])

        saveList = list(set(pageNames).difference(set(pageNameList)))
        for pageName in saveList:
            self.pageNameSave(pageName)
            pageList[pageName] = 0
        return pageList

    def pageNameSave(self,page_name):
        now = timeHelper.getFormatDate('now')
        ob = yyyIosPageNameModel(page_name_id=0,page_name=page_name,create_time=now)
        ob.save()

    def setPageId(self, valueId, pageId):
        setResult = yyyIosPageNameModel.objects.filter(id = pageId).update(page_name_id = valueId)
        return True if setResult == 1 else False
