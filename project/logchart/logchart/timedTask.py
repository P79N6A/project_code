from apscheduler.schedulers.background import  BackgroundScheduler

from apps.manage.task.resolvePeaLog import resolvePeaLog
from apps.manage.task.resolveYyyLog import resolveYyyLog

from apps.manage.management.commands.monitor import Command as monitor
from apps.manage.management.commands.importYyyUser import Command as importYyyUser
from apps.manage.management.commands.importPeanutUser import Command as importPeanutUser
from apps.helper.timeHelper import timeHelper

import logging
scheduler =  BackgroundScheduler()
scheduler._logger = logging.getLogger("apscheduler")

def resolveClickhouseLog():
    resolvePeaLog().resolveAll()
    resolveYyyLog().resolveAll()

def monitorTask():
    monitor().monitorTask()

def yiUsertask():
    yesterday = timeHelper.getFormatDate("none",timeHelper.getTimestamp(-86400))
    importYyyUser().doImport(str(yesterday), True)

def peanurUsertask():
    yesterday = timeHelper.getFormatDate("none", timeHelper.getTimestamp(-86400))
    importPeanutUser().doImport(str(yesterday), True)

# 线上用
scheduler.add_job(resolveClickhouseLog, 'cron', minute='*/10', id='test_job1', misfire_grace_time=3600)
scheduler.add_job(monitorTask, 'cron', hour = '*/3', id='test_job2')
scheduler.add_job(yiUsertask,'cron', hour = '02', minute = '02', second = '02', id='test_job3')
scheduler.add_job(peanurUsertask,'cron', hour = '01', minute = '01', second = '01', id='test_job4')

scheduler.start()