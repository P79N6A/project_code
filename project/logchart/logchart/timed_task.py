from apps.peanut.peanut_task.yaf_peanut_android_global_task import yafPeanutAndroidGlobalTask
from apps.peanut.peanut_task.yaf_peanut_ios_global_task import yafPeanutIosGlobalTask
from apps.peanut.peanut_task.yaf_peanut_web_task import yafPeanutWebAllTask
from apps.peanut.peanut_task.yaf_peanut_weixin_task import yafPeanutWeixinAllTask
from apps.yiyiyuan.yiyiyuan_task.yaf_yyy_android_global_task import yafYyyAndroidGlobalTask
from apps.yiyiyuan.yiyiyuan_task.yaf_yyy_ios_global_task import yafYyyIosGlobalTask
from apps.yiyiyuan.yiyiyuan_task.yaf_yyy_weixin_task import yafYyyWeixinAllTask
from apps.index.index_task.monitoring_task import monitoringTask
from apps.yiyiyuan.views.yi_user import yiUser
from django.db import connections

from apscheduler.schedulers.background import  BackgroundScheduler
import logging
logging.basicConfig(level=logging.INFO,
                   format='%(asctime)s %(filename)s[line:%(lineno)d] %(levelname)s %(message)s',
                   datefmt='%Y-%m-%d %H:%M:%S',
                   filename='D:\www\logchart\log\\apscheduler.log',
                    filemode='a')

class task():
    # hours = 1 seconds=1
        connections.close_all()

        scheduler =  BackgroundScheduler()

        def yafAndroidGlobal():
            yafPeanutAndroidGlobalTask().yafPeanutAndroidGlobaltest()
            yafYyyAndroidGlobalTask().yafYyyAndroidGlobaltest()

        def yafIosGlobal():
            yafPeanutIosGlobalTask().yafPeanutIosGlobaltest()
            yafYyyIosGlobalTask().yafYyyIosGlobaltest()

        def yafWeb():
            yafPeanutWebAllTask().yafPeanutWebAlltest()

        def yafWeixin():
            yafPeanutWeixinAllTask().yafPeanutWeixinAlltest()
            yafYyyWeixinAllTask().yafYyyWeixinAlltest()

        def indextask():
            monitoringTask().monitoringtask()

        def yiusertask():
            yiUser().transfer_yiUser()

        # 测试用
        # hour = '*/1'second='*/5'minute='*/10'
        # scheduler.add_job(yafAndroidGlobal, 'cron',second='*/5', id='test_job1',misfire_grace_time=3600)
        # scheduler.add_job(yafIosGlobal, 'cron', second='*/5', id='test_job2',misfire_grace_time=3600)
        # scheduler.add_job(yafWeb, 'cron', second='*/5', id='test_job3',misfire_grace_time=3600)
        scheduler.add_job(yafWeixin, 'cron',second='*/5', id='test_job4',misfire_grace_time=3600)
        # 线上用

        #scheduler.add_job(yafAndroidGlobal, 'cron', minute='*/5', id='test_job1', misfire_grace_time=3600)
        #scheduler.add_job(yafIosGlobal, 'cron', minute='*/5', id='test_job2', misfire_grace_time=3600)
        #scheduler.add_job(yafWeb, 'cron', minute='*/5', id='test_job3', misfire_grace_time=3600)
        #scheduler.add_job(yafWeixin, 'cron', minute='*/5', id='test_job4', misfire_grace_time=3600)
        #scheduler.add_job(indextask, 'cron', hour = '*/3', id='test_job5', misfire_grace_time=3600)
        #scheduler.add_job(yiusertask, 'cron', hour='*/1', id='test_job6', misfire_grace_time=3600)

        scheduler._logger = logging
        scheduler.start()

