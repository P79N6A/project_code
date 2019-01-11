# -*- coding:utf-8 -*-

from .base_command import BaseCommand
from util.ssdbUtil import ssdbUtil

class Neo4jcsvCommand(BaseCommand):

    def runData(self, start_time, end_time):
        # 连接ssdb
        ssdb = ssdbUtil('ssdb_detail_config')
        ssdb_resources = ssdb.ssdbConnection()
        ssdb_resources.hset("a", "12", "sdfds")
        print(ssdb_resources.hget("a"))
