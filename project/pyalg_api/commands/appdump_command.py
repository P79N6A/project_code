# coding=utf-8
import os
import csv
from lib.logger import logger
from .base_command import BaseCommand
from service.appdump import APPDump

class AppdumpCommand(BaseCommand):
    def __init__(self):
        super(AppdumpCommand, self).__init__()

    def runAppDump(self, name):
        data = {}
        try:
            with open('./' + name, "r", encoding='utf-8') as csvFile:
                readerObj = csv.reader(csvFile)  # 读取csv文件，返回的是迭代类型
                for row in readerObj:
                    data[row[1]] = row[2]
            csvFile.close()
        except Exception as e:
            logger.error("openFileError filename:%s error:%s" % (name, e))

        obj = APPDump(data, name)
        result = obj.run()
        print(result)
