from apps.clickhouse.models.peanut.yafAndroidGlobal import yafAndroidGlobal
from apps.clickhouse.models.peanut.yafIosGlobal import yafIosGlobal
from apps.clickhouse.models.peanut.yafWeixin import yafWeixin
from apps.clickhouse.models.peanut.yafWeb import yafWeb

from apps.logchart.models.peanutRegister import peanutRegisterModel
from apps.logchart.models.peanutBrowse import peanutBrowseModel
from apps.logchart.models.peanutBid import peanutBidModel

from apps.logchart.models.peanutAndroidPageName import peanutAndroidPageNameModel
from apps.logchart.models.peanutIosPageName import peanutIosPageNameModel
from apps.logchart.models.peanutWeixinPageName import peanutWeixinPageNameModel
from apps.logchart.models.peanutWebPageName import peanutWebPageNameModel

class resolvePeaLog:
    peanutModelList = [yafAndroidGlobal(), yafIosGlobal(), yafWeixin(), yafWeb()]

    def resolveAll(self):
        self.__resolveRegisterLog()
        self.__resolveBrowseLog()

    def __resolveRegisterLog(self):
        registerModel = peanutRegisterModel()
        for each in self.peanutModelList:
            timeDict = registerModel.getMaxtime(each.client_num())
            registerNum = each.getRegisterNum(timeDict['now'], timeDict['before'])
            if registerNum >= 0:
                registerModel.saveRegister(registerNum, each.client_num(), timeDict['now'])

    def __resolveBrowseLog(self):
        browseModel = peanutBrowseModel()
        bidModel = peanutBidModel()
        for each in self.peanutModelList:
            timeDict = browseModel.getMaxtime(each.client_num())
            urlList = each.getBrowseNum(timeDict['now'], timeDict['before'])
            if len(urlList) > 0:
                browseList = self.__browseUrl2PageId(urlList, each.client_num())
                # 保存每个页面的浏览量,并获取投标数和订阅数
                bidNum = subscribeNum = 0
                for pageId,browseNum in browseList.items():
                    browseModel.saveBrowse(pageId, browseNum, each.client_num(), timeDict['now'])
                    if int(pageId) == 37 or int(pageId) == 118:
                        bidNum +=1
                    if int(pageId) == 48:
                        subscribeNum +=1
                bidModel.saveBid(bidNum, 1, each.client_num(), timeDict['now'])
                bidModel.saveBid(subscribeNum, 2, each.client_num(), timeDict['now'])

    def __browseUrl2PageId(self, urlList, clientNum):
        urls = list(dict(urlList).keys())
        if clientNum == yafAndroidGlobal().client_num():
            pageIds = peanutAndroidPageNameModel().getPageIds(urls)
        elif clientNum == yafIosGlobal().client_num():
            pageIds = peanutIosPageNameModel().getPageIds(urls)
        elif clientNum == yafWeixin().client_num():
            pageIds = peanutWeixinPageNameModel().getPageIds(urls)
        elif clientNum == yafWeb().client_num():
            pageIds = peanutWebPageNameModel().getPageIds(urls)

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