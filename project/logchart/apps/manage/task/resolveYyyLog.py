from apps.clickhouse.models.yiyiyuan.yafAndroidGlobal import yafAndroidGlobal
from apps.clickhouse.models.yiyiyuan.yafIosGlobal import yafIosGlobal
from apps.clickhouse.models.yiyiyuan.yafWeixin import yafWeixin

from apps.logchart.models.yyyRegister import yyyRegisterModel
from apps.logchart.models.yyyBrowse import yyyBrowseModel

from apps.logchart.models.yyyAndroidPageName import yyyAndroidPageNameModel
from apps.logchart.models.yyyIosPageName import yyyIosPageNameModel
from apps.logchart.models.yyyWeixinPageName import yyyWeixinPageNameModel

class resolveYyyLog:
    yyyModelList = [yafAndroidGlobal(), yafIosGlobal(), yafWeixin()]

    def resolveAll(self):
        self.__resolveRegisterLog()
        self.__resolveBrowseLog()

    def __resolveRegisterLog(self):
        registerModel = yyyRegisterModel()
        for each in self.yyyModelList:
            timeDict = registerModel.getMaxtime(each.client_num())
            registerNum = each.getRegisterNum(timeDict['now'], timeDict['before'])
            if registerNum >= 0:
                registerModel.saveRegister(registerNum, each.client_num(), timeDict['now'])

    def __resolveBrowseLog(self):
        browseModel = yyyBrowseModel()
        for each in self.yyyModelList:
            timeDict = browseModel.getMaxtime(each.client_num())
            urlList = each.getBrowseNum(timeDict['now'], timeDict['before'])
            if len(urlList) > 0:
                browseList = self.__browseUrl2PageId(urlList, each.client_num())
                # 保存每个页面的浏览量,并获取投标数和订阅数
                for pageId,browseNum in browseList.items():
                    browseModel.saveBrowse(pageId, browseNum, each.client_num(), timeDict['now'])

    def __browseUrl2PageId(self, urlList, clientNum):
        urls = list(dict(urlList).keys())
        if clientNum == yafAndroidGlobal().client_num():
            pageIds = yyyAndroidPageNameModel().getPageIds(urls)
        elif clientNum == yafIosGlobal().client_num():
            pageIds = yyyIosPageNameModel().getPageIds(urls)
        elif clientNum == yafWeixin().client_num():
            pageIds = yyyWeixinPageNameModel().getPageIds(urls)

        browseList = {}
        for urlEach in urlList:
            url = urlEach[0]
            num = urlEach[1]
            pageId = pageIds[url]
            if pageId in browseList:
                browseList[pageId] += num
            else:
                browseList[pageId] = num
        return(browseList)