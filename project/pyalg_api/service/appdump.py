# -*- coding: utf-8 -*-
'''
获取App列表数据详情
'''
import json
import csv
import os
import pandas as pd
from lib.logger import logger
from model.analysis import AppList, AppInfo, UserAppSnapshot


class APPDump(object):
    TOTALAPPDICT = {}
    DUMP_FIELD = ['first_label', 'second_lable']
    DOWN_FROM = {1: 'yyb_', 2: 'wdj_'}
    SERIES_MAP = ['app_id', 'app_name', 'app_package']

    def __init__(self, data, name):
        self.data = data
        self.filename = name.replace('.csv', '_output.csv')
        for field in self.DUMP_FIELD:
            for down_from in list(self.DOWN_FROM.values()):
                self.SERIES_MAP.append(down_from + field)

        # 生成CSV header信息
        header = self.SERIES_MAP[:]
        header.insert(0,'mobile')
        header.remove('app_id')
        header.remove('yyb_second_lable')
        with open('./'+self.filename, 'a', newline='', encoding='utf-8') as csvFile:
            csvWriter = csv.writer(csvFile)
            csvWriter.writerow(header)
        csvFile.close()

    def run(self):
        try:
            if len(self.data) == 0:
                return '手机号列表为空'

            # 遍历每个手机号和时间组合
            for mobile, time in self.data.items():
                # 根据手机号列表获取每人在某一时刻之前最新的数据
                appList = UserAppSnapshot().getAppListWithTime(mobile, time)
                if len(appList) == 0:
                    continue
                appList = self.__load_json(appList)

                outputDict = {}
                # 遍历判断是否是已获取过信息的App
                for appId in appList:
                    appInfo = self.TOTALAPPDICT.get(appId, None)
                    if appInfo is not None:
                        outputDict.update({appId: appInfo})
                        appList.remove(appId)

                # 用获取到的AppId列表获取AppInfo
                if len(appList) > 0:
                    appInfoDict = self.__getAppInfo(appList)
                    outputDict.update(appInfoDict)

                self.__writeCsv(mobile, outputDict)
            return self.filename
        except Exception as e:
            logger.error("__APPDump error is %s",e)
            return '系统错误'

    def __getAppInfo(self, appList):
        # 获取app名称和包名列表
        appNameList = AppList().getAppByAppid(appList)
        # 获取app详情列表
        appInfoList = AppInfo().getAppByAppid(appList)
        # 获取需要导出的字段pandas
        appInfoPd = pd.DataFrame(list(appNameList.values()), columns=self.SERIES_MAP, index= list(appNameList.keys()))
        for appInfo in appInfoList:
            appid = appInfo.get('app_id', None)
            down_from = appInfo.get('down_from', None)
            for field in self.DUMP_FIELD:
                appInfoPd.loc[appid, self.DOWN_FROM.get(down_from) + field] = appInfo.get(field, None)
        # 删除一亿元二级分类
        del appInfoPd['yyb_second_lable']
        appInfoPd.fillna("", inplace=True)
        # 将appInfo转成list格式的dict
        appInfoDict = appInfoPd.set_index('app_id').T.to_dict('list')
        # 将appInfo更新到获取过的app信息字典中
        self.TOTALAPPDICT.update(appInfoDict)
        return appInfoDict

    def __writeCsv(self, mobile, appDict):
        with open('./'+self.filename, 'a', newline='', encoding='utf-8') as csvFile:
            csvWriter = csv.writer(csvFile)
            for appInfo in appDict.values():
                csvWriter.writerow([mobile] + appInfo)
        csvFile.close()

    def __load_json(self,json_data):
        json_obj = {}
        try:
            json_obj = json.loads(json_data)
        except Exception as e:
            logger.error('load json is fail : %s' % e)
        return json_obj
