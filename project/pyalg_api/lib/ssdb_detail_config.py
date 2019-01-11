# -*- coding:utf-8 -*-

'''
通话详单配置文件
'''

class SsdbDetailConfig(object):

    '''
    测试
    '''
    def devServer(self):
        ip_data = {
            'ip'    : '47.93.121.71',
            'port'  : '8888'
        }
        return ip_data

    '''
    生产
    '''
    def proServer(self):
        ip_data = {
            'ip'    : '10.253.124.246',
            'port'  : '8808'
        }
        return ip_data

    def getConfig(self):
        # return self.proServer()
        return self.devServer()