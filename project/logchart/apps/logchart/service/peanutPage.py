from apps.logchart.models.peanutPageName import peanutPageNameModel
from apps.logchart.models.peanutAndroidPageName import peanutAndroidPageNameModel
from apps.logchart.models.peanutIosPageName import peanutIosPageNameModel
from apps.logchart.models.peanutWeixinPageName import peanutWeixinPageNameModel
from apps.logchart.models.peanutWebPageName import peanutWebPageNameModel

class peanutPage():
    # 获取花生米富页面链接与页面名称的关联列表
    def getPageList(self, pageId, equipment):
        pageList = []
        nameList = peanutPageNameModel().getPageNameList()
        if equipment == peanutAndroidPageNameModel.clientNum():
            urlList = peanutAndroidPageNameModel().getUrlList(pageId)
        elif equipment == peanutIosPageNameModel.clientNum():
            urlList = peanutIosPageNameModel().getUrlList(pageId)
        elif equipment == peanutWeixinPageNameModel.clientNum():
            urlList = peanutWeixinPageNameModel().getUrlList(pageId)
        elif equipment == peanutWebPageNameModel.clientNum():
            urlList = peanutWebPageNameModel().getUrlList(pageId)
        else:
            return {"nameList": nameList, 'pageList': pageList}

        for eachUrl in urlList:
            pageValue = nameList[eachUrl['page_name_id']] if nameList[eachUrl['page_name_id']] != None else '未知页面'
            isRed = False if eachUrl['page_name_id'] != 0 else True
            pageList.append({'id': eachUrl['id'], 'pageValue': pageValue, 'pageName': eachUrl['page_name'], 'isRed': isRed})
        return {"nameList": nameList, 'pageList': pageList}

    def addPageName(self, pageName, equipment, pageId):
        valueId = peanutPageNameModel().savePageName(pageName)
        if valueId == 0:
            return {'code': 'add_error', 'msg': '添加页面名称失败'}
        return self.editPageName(valueId, equipment, pageId)

    def editPageName(self, valueId, equipment, pageId):
        if equipment == peanutAndroidPageNameModel.clientNum():
            setResult = peanutAndroidPageNameModel().setPageId(valueId, pageId)
        elif equipment == peanutIosPageNameModel.clientNum():
            setResult = peanutIosPageNameModel().setPageId(valueId, pageId)
        elif equipment == peanutWeixinPageNameModel.clientNum():
            setResult = peanutWeixinPageNameModel().setPageId(valueId, pageId)
        elif equipment == peanutWebPageNameModel.clientNum():
            setResult = peanutWebPageNameModel().setPageId(valueId, pageId)
        else:
            return {'code': 'params_error', 'msg': '参数错误'}
        if setResult:
            return {'code': 'success', 'msg': '成功'}
        else:
            return {'code': 'edit_error', 'msg': '页面名称映射修改失败'}