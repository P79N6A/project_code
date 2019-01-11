import datetime

from apps.clickhouse.models.peanut.android import android as peanutAndroid
from apps.clickhouse.models.peanut.ios import ios as peanutIos
from apps.clickhouse.models.peanut.web import web as peanutWeb
from apps.clickhouse.models.peanut.weixin import weixin as peanutWeixin
from apps.clickhouse.models.peanut.wap import wap as peanutWap
from apps.clickhouse.models.peanut.yafAndroidGlobal import yafAndroidGlobal as peanutYafAndroidGlobal
from apps.clickhouse.models.peanut.yafAndroidStartup import yafAndroidStartup as peanutYafAndroidStartup
from apps.clickhouse.models.peanut.yafIosGlobal import yafIosGlobal as peanutYafIosGlobal
from apps.clickhouse.models.peanut.yafIosStartup import yafIosStartup as peanutYafIosStartup
from apps.clickhouse.models.peanut.yafWeb import yafWeb as peanutYafWeb
from apps.clickhouse.models.peanut.yafWeixin import yafWeixin as peanutYafWeixin

from apps.clickhouse.models.yiyiyuan.android import android as yiyiyuanAndroid
from apps.clickhouse.models.yiyiyuan.ios import ios as yiyiyuanIos
from apps.clickhouse.models.yiyiyuan.weixin import weixin as yiyiyuanWeixin
from apps.clickhouse.models.yiyiyuan.yafAndroidGlobal import yafAndroidGlobal as yiyiyuanYafAndroidGlobal
from apps.clickhouse.models.yiyiyuan.yafAndroidStartup import yafAndroidStartup as yiyiyuanYafAndroidStartup
from apps.clickhouse.models.yiyiyuan.yafIosGlobal import yafIosGlobal as yiyiyuanYafIosGlobal
from apps.clickhouse.models.yiyiyuan.yafIosStartup import yafIosStartup as yiyiyuanYafIosStartup
from apps.clickhouse.models.yiyiyuan.yafAndroidLbs import yafAndroidLbs as yiyiyuanYafAndroidLbs
from apps.clickhouse.models.yiyiyuan.yafIosLbs import yafIosLbs as yiyiyuanYafIosLbs
from apps.clickhouse.models.yiyiyuan.yafWeixin import yafWeixin as yiyiyuanYafWeixin

from apps.clickhouse.models.zrkey.android import android as zrkeyAndroid
from apps.clickhouse.models.zrkey.ios import ios as zrkeyIos
from apps.clickhouse.models.zrkey.web import web as zrkeyWeb
from apps.clickhouse.models.zrkey.yafAndroidGlobal import yafAndroidGlobal as zrkeyYafAndroidGlobal
from apps.clickhouse.models.zrkey.yafAndroidStartup import yafAndroidStartup as zrkeyYafAndroidStartup
from apps.clickhouse.models.zrkey.yafIosGlobal import yafIosGlobal as zrkeyYafIosGlobal
from apps.clickhouse.models.zrkey.yafIosStartup import yafIosStartup as zrkeyYafIosStartup
from apps.clickhouse.models.zrkey.yafWeb import yafWeb as zrkeyYafWeb

from apps.clickhouse.models.ykyq.weixin import weixin as ykyqWeixin
from apps.clickhouse.models.ykyq.yafWeixin import yafWeixin as ykyqYafWeixin

# clickhouse数据库监控类
class clickhouseMonitor():
    # 报警时间阈值
    monitoriTime = 3*60*60
    # 需要监控的数据库对象
    monitorList = [peanutAndroid(), peanutIos(), peanutWeb(), peanutWeixin(), peanutWap()] + \
        [peanutYafAndroidGlobal(), peanutYafAndroidStartup(), peanutYafIosGlobal(), peanutYafIosStartup()] + \
        [peanutYafWeb(), peanutYafWeixin()] + \
        [yiyiyuanAndroid(), yiyiyuanIos(), yiyiyuanWeixin()] + \
        [yiyiyuanYafAndroidGlobal(), yiyiyuanYafAndroidStartup(), yiyiyuanYafIosGlobal(), yiyiyuanYafIosStartup()] + \
        [yiyiyuanYafAndroidLbs(), yiyiyuanYafIosLbs(), yiyiyuanYafWeixin()] + \
        [zrkeyAndroid(), zrkeyIos(), zrkeyWeb()] + \
        [zrkeyYafAndroidGlobal(), zrkeyYafAndroidStartup(), zrkeyYafIosGlobal(), zrkeyYafIosStartup(), zrkeyYafWeb()] + \
        [ykyqWeixin(), ykyqYafWeixin()]

    # 监控程序代码
    def doMonitor(self):
        now = datetime.datetime.now()
        monitorData = []
        for each in self.monitorList:
            lastTime = each.getLastTime()
            try:
                lastTime = datetime.datetime.strptime(lastTime, '%Y-%m-%d %H:%M:%S')
                if (now - lastTime).total_seconds() > self.monitoriTime:
                    monitorData.append({"type": each.project_num(), "tableName": each.table_name(), "tableTime": lastTime, "now": now, "flag": True})
                else:
                    monitorData.append({"type": each.project_num(), "tableName": each.table_name(), "tableTime": lastTime, "now": now, "flag": False})
            except:
                monitorData.append({"type": each.project_num(), "tableName": each.table_name(), "tableTime": '时间错误请检查数据格式', "now": now,"flag": True})
        return monitorData