from django.core.management.base import BaseCommand, CommandError
import datetime
import time
import math
from apps.yiyiyuan.models.yi_user import yi_user
from apps.clickhouse.models.risk.yiUser import yiUser

import logging
logger = logging.getLogger("importYyyUser")

class Command(BaseCommand):
    limit = 1000

    def add_arguments(self, parser):
        # Positional arguments
        parser.add_argument('selectDays', nargs='+', type=str)

    def handle(self, *args, **options):
        # 获取选中的时间参数
        selectDays = options['selectDays']
        for eachDay in selectDays:
            # 判断每个时间格式是否正确
            try:
                dateTime = datetime.datetime.strptime(eachDay,'%Y-%m-%d')
                selectDay = datetime.date(dateTime.year,dateTime.month,dateTime.day)
            except:
                logger.error('参数 ' + eachDay + ' 格式错误')
                continue

            if self.doImport(str(selectDay), False):
                time.sleep(2)
            
        return

    def doImport(self, selectDay, isNew = False):
        selectDay = str(selectDay)
        logger.info('开始同步 ' + selectDay + ' 的数据')
        startTime = selectDay + ' 00:00:00'
        endTime = selectDay + ' 23:59:59'
        # 获取每天数据条数及需要的执行次数
        selectNum = yi_user().get_time_rang_count(startTime, endTime)
        if selectNum == 0:
            logger.warning(selectDay + ' 暂无需要同步的数据,执行结束')
            return False
        runTime = math.ceil(selectNum / self.limit)
        # 循环获取每页的数据
        for page in range(0,runTime):
            offset = page * self.limit
            limit = (page + 1) * self.limit
            selectData = yi_user().get_time_rang(startTime, endTime, offset, limit)
            logger.info('已获取第 ' + str(offset) + ' 条到第 ' + str(limit) + ' 条数据')
            # 将获取到的数据保存到 insertLiset 列表中
            insertLiset = []
            methodModel = yiUser()
            for insertData in selectData:
                user_id = str(insertData.get('user_id'))
                logger.info('开始同步数据: user_id=>' + user_id)
                if (not isNew) and methodModel.count(user_id) != 0:
                    logger.error('数据同步失败,user_id=>' + user_id + '已存在')
                    continue
                yiUserAllModelOb = yiUser(
                    user_id = user_id,
                    mobile = str(insertData.get('mobile')),
                    status = str(insertData.get('status')),
                    realname = str(insertData.get('realname')),
                    identity = str(insertData.get('identity')),
                    come_from = str(insertData.get('come_from')),
                    down_from = str(insertData.get('down_from')),
                    create_time = str(insertData.get('create_time')),
                    birth_year = str(insertData.get('birth_year')),
                    last_login_time = str(insertData.get('last_login_time')),
                    verify_time = str(insertData.get('verify_time')),
                    theday = selectDay
                )
                insertLiset.append(yiUserAllModelOb)
                logger.info('user_id=>' + user_id + '已加入 insertLiset 中,等待批量导入')

            # 将 insertLiset 列表中的数据保存到 clickhouse 中
            if len(insertLiset) > 0: 
                if methodModel.batchSave(insertLiset):
                    logger.info('第 ' + str(offset) + ' 条到第 ' + str(limit) + ' 条数据同步成功')
                else:
                    logger.info('第 ' + str(offset) + ' 条到第 ' + str(limit) + ' 条数据同步失败')
                time.sleep(1)
            else:
                continue

        logger.info(selectDay + ' 的数据同步结束')
        return True
        
