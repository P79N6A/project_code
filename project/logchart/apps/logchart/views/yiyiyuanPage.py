from apps.logchart.views.baseView import baseView
from apps.logchart.service.yiyiyuanPage import yiyiyuanPage as yiyiyuanPageService

class yiyiyuanPage():
    # 返回一亿元页面链接与页面名称的关联列表
    @baseView.checkLogin()
    def pagelist(request):
        pageId = int(request.GET.get('pageId', -1))
        equipment = int(request.GET.get('equipment', 1))
        pagelist = yiyiyuanPageService().getPageList(pageId, equipment)
        data = {'nameList': pagelist['nameList'].items(), 'pageList': pagelist['pageList'], 'pageId': pageId, 'equipment': equipment}
        return baseView.urlRender(request, 'yiyiyuan_pagelist.html', data, "yiyiyuan")

    @baseView.checkLogin()
    def addPageName(request):
        pageName = request.POST.get('pageName', '')
        equipment = request.POST.get('equipment', '0')
        pageId = request.POST.get('pageId', '0')
        equipment = int(equipment)
        pageId = int(pageId)
        if pageName == '' or equipment not in [1, 2, 3] or pageId == 0:
            return baseView.errorJson('params_error', '参数错误')
        addResult = yiyiyuanPageService().addPageName(pageName, equipment, pageId)
        if addResult['code'] != 'success':
            return baseView.errorJson(addResult['code'], addResult['msg'])
        else:
            return baseView.successJson()

    @baseView.checkLogin()
    def editPageName(request):
        valueId = int(request.POST.get('valueId', '0'))
        equipment = int(request.POST.get('equipment', '0'))
        pageId = int(request.POST.get('pageId', '0'))
        if equipment not in [1, 2, 3] or pageId == 0:
            return baseView.errorJson('params_error', '参数错误')
        editResult = yiyiyuanPageService().editPageName(valueId, equipment, pageId)
        if editResult['code'] != 'success':
            return baseView.errorJson(editResult['code'], editResult['msg'])
        else:
            return baseView.successJson()
