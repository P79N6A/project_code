from django.db import models

class yyyPageNameModel(models.Model):
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
        db_table ="yyy_page_name"

    def getPageNameList(self,pageIds = None):
        if pageIds == None:
            selectPage = yyyPageNameModel.objects.only('page_name_id', 'page_name').all()
        else:
            selectPage = yyyPageNameModel.objects.filter(page_name_id__in = pageIds).only('page_name_id', 'page_name').all()
        pageNameList = {}
        for eachPage in selectPage:
            pageNameList[eachPage.page_name_id] = eachPage.page_name
        return pageNameList
