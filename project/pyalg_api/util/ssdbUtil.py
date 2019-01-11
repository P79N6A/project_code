# -*- coding:utf-8 -*-
'''
ssdb连接
'''

import pyssdb
from lib.ssdb_detail_config import SsdbDetailConfig
from lib.ssdb_config import SsdbConfig

class ssdbUtil(object):
    def __init__(self, config_name):
        self.ssdb_config = self.importConfig(config_name)

    '''
    配置文件
    '''
    def importConfig(self, config_name):
        if config_name == 'ssdb_detail_config':
            return SsdbDetailConfig()
        return SsdbConfig()

    def ssdbConnection(self):
        try:
            config = self.ssdb_config.getConfig()
            result = pyssdb.Client(config['ip'], int(config['port']))
            return result
        except TimeoutError as e:
            print(e)
            return False
        except TypeError as t:
            print(t)
            return False
