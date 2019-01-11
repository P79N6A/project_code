from django.core.management.base import BaseCommand, CommandError
from apps.clickhouse.views.monitor import clickhouseMonitor
from apps.logchart.models.monitoring import monitoring

import logging

logger = logging.getLogger("monitir")

class Command(BaseCommand):

    def handle(self, *args, **options):
        self.monitorTask()

    def monitorTask(self):
        dataList = clickhouseMonitor().doMonitor()
        monitorList = []
        for eachData in dataList:
            if eachData.get('flag'):
                monitorList.append(
                    monitoring(
                        type = eachData.get('type'),
                        table_name = eachData.get('tableName'),
                        table_time = eachData.get('tableTime'),
                        create_time = eachData.get('now')
                    )
                )
        if len(monitorList) > 0:
            monitoring.objects.bulk_create(monitorList)