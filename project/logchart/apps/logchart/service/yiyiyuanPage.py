from apps.logchart.models.yyyPageName import  yyyPageNameModel
from apps.logchart.models.yyyAndroidPageName import yyyAndroidPageNameModel
from apps.logchart.models.yyyIosPageName import yyyIosPageNameModel
from apps.logchart.models.yyyWeixinPageName import yyyWeixinPageNameModel

class yiyiyuanPage():
    # 获取花生米富页面链接与页面名称的关联列表
    def getPageList(self, pageId, equipment):
        pageList = []
        nameList = yyyPageNameModel().getPageNameList()
        if equipment == yyyAndroidPageNameModel.clientNum():
            urlList = yyyAndroidPageNameModel().getUrlList(pageId)
        elif equipment == yyyIosPageNameModel.clientNum():
            urlList = yyyIosPageNameModel().getUrlList(pageId)
        elif equipment == yyyWeixinPageNameModel.clientNum():
            urlList = yyyWeixinPageNameModel().getUrlList(pageId)
        else:
            return {"nameList": nameList, 'pageList': pageList}

        for eachUrl in urlList:
            pageValue = nameList[eachUrl['page_name_id']] if nameList[eachUrl['page_name_id']] != None else '未知页面'
            isRed = False if eachUrl['page_name_id'] != 0 else True
            pageList.append({'id': eachUrl['id'], 'pageValue': pageValue, 'pageName': eachUrl['page_name'], 'isRed': isRed})
        return {"nameList": nameList, 'pageList': pageList}

    def addPageName(self, pageName, equipment, pageId):
        valueId = yyyPageNameModel().savePageName(pageName)
        if valueId == 0:
            return {'code': 'add_error', 'msg': '添加页面名称失败'}
        return self.editPageName(valueId, equipment, pageId)

    def editPageName(self, valueId, equipment, pageId):
        if equipment == yyyAndroidPageNameModel.clientNum():
            setResult = yyyAndroidPageNameModel().setPageId(valueId, pageId)
        elif equipment == yyyIosPageNameModel.clientNum():
            setResult = yyyIosPageNameModel().setPageId(valueId, pageId)
        elif equipment == yyyWeixinPageNameModel.clientNum():
            setResult = yyyWeixinPageNameModel().setPageId(valueId, pageId)
        else:
            return {'code': 'params_error', 'msg': '参数错误'}
        if setResult:
            return {'code': 'success', 'msg': '成功'}
        else:
            return {'code': 'edit_error', 'msg': '页面名称映射修改失败'}