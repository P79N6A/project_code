from apps.logchart.views.baseView import baseView
from apps.logchart.service.peanutPage import peanutPage as peanutPageService

class peanutPage():

    # 返回花生米富页面链接与页面名称的关联列表
    @baseView.checkLogin()
    def pagelist(request):
        pageId = int(request.GET.get('pageId', -1))
        equipment = int(request.GET.get('equipment', 1))
        pagelist = peanutPageService().getPageList(pageId, equipment)
        data = {'nameList': pagelist['nameList'].items(), 'pageList': pagelist['pageList'], 'pageId': pageId, 'equipment': equipment}
        return baseView.urlRender(request, 'peanut_pagelist.html', data, "peanut")

    @baseView.checkLogin()
    def addPageName(request):
        pageName = request.POST.get('pageName', '')
        equipment = int(request.POST.get('equipment', '0'))
        pageId = int(request.POST.get('pageId', '0'))
        if pageName == '' or equipment not in [1, 2, 3, 4] or pageId == 0:
            return baseView.errorJson('params_error', '参数错误')
        addResult = peanutPageService().addPageName(pageName, equipment, pageId)
        if addResult['code'] != 'success':
            return baseView.errorJson(addResult['code'], addResult['msg'])
        else:
            return baseView.successJson()

    @baseView.checkLogin()
    def editPageName(request):
        valueId = int(request.POST.get('valueId', '0'))
        equipment = int(request.POST.get('equipment', '0'))
        pageId = int(request.POST.get('pageId', '0'))
        if equipment not in [1, 2, 3, 4] or pageId == 0:
            return baseView.errorJson('params_error', '参数错误')
        editResult = peanutPageService().editPageName(valueId, equipment, pageId)
        if editResult['code'] != 'success':
            return baseView.errorJson(editResult['code'], editResult['msg'])
        else:
            return baseView.successJson()
