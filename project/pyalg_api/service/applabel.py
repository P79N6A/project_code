# -*- coding: utf-8 -*-
'''
采集App数据保存及详情获取
'''
import json
import os
from flask_restplus.reqparse import ParseResult
from datetime import datetime
from lib.logger import logger
from .app_label_yyb import YybAppDefine
from .app_label_wdj import WdjAppDefine
from model.analysis import AppInfo, AppList, UserAppSnapshot, AppBlackRoom

class APPLabel(object):
    # App采集服务类
    APP_LABEL_INFO = {1: YybAppDefine(), 2: WdjAppDefine()}

    def __init__(self, data):
        if isinstance(data, ParseResult):
            self.mobile = data.mobile
            self.applist = self.__load_json(data.applist)
            self.time = data.time
        else:
            self.mobile = data.get('mobile', None)
            self.applist = self.__load_json(data.get('applist', None))
            self.time = data.get('time', None)

    def run(self):
        try:
            if len(self.mobile) == 0:
                return {'code': -101, 'msg': '手机号为空'}

            if len(self.time) == 0:
                return {'code': -102, 'msg': '时间信息为空'}

            if len(self.applist) == 0:
                return {'code': -103, 'msg': '采集到的App列表为空'}

            # 获取采集的包名列表
            newPkgList = []
            for eachApp in self.applist:
                pkgName = eachApp.get('app_package', None)
                if pkgName is not None:
                    newPkgList.append(pkgName)
            if len(newPkgList) == 0:
                return {'code': -104, 'msg': '采集到的App列表中的包名为空'}

            # 获取采集的 appId=>pkgName 字典
            appList = self.__getAppIdList(newPkgList)
            # 保存采集的 AppId 列表
            saveSnapshot = self.__saveSnapshot(list(appList.keys()))
            if saveSnapshot == False:
                return {'code': -105, 'msg': '采集数据保存失败'}

            # 获取采集的App的详细信息
            self.__saveAppInfo(appList)
            return {'code': 0, 'msg': '采集数据保存成功'}
        except Exception as e:
            logger.error("__APPLabel error is %s",e)
            return {'code': -100, 'msg': '系统错误'}

    def __getAppIdList(self, newPkgList):
        # 获取AppList表中的 appId => pkgName 字典
        appList = AppList().getAppByPkgList(newPkgList)
        oldPkgList = list(appList.values())
        # 获取AppList表中没有的pkgList
        diffPkgList = list(set(newPkgList).difference(set(oldPkgList)))
        if len(diffPkgList) == 0:
            return appList
        # 生成批量保存数据
        insertApp = []
        for eachApp in self.applist:
            appName = eachApp.get('app_name', None)
            pkgName = eachApp.get('app_package', None)
            if pkgName not in diffPkgList:
                continue
            if len(pkgName) >= 100:
                diffPkgList.remove(pkgName)
                continue
            dataDict = {}
            dataDict['app_name'] = appName
            dataDict['app_package'] = pkgName
            dataDict['create_time'] = datetime.now()
            insertApp.append(dataDict)
        # 执行批量保存
        insertAppList = AppList().batchInsert(insertApp,diffPkgList)
        # 合并AppList
        appList.update(insertAppList)
        return appList

    def __saveSnapshot(self, appIdList):
        oldAppList = UserAppSnapshot().getAppList(self.mobile)
        # 判断是否存在旧数据, 存在则取去重后的并集
        if oldAppList is not None:
            oldAppList = self.__load_json(oldAppList.app_list)
            appIdList = list(set(oldAppList).union(set(appIdList)))
        # 将列表数据转为json存储
        appIdList = json.dumps(appIdList)
        return UserAppSnapshot().saveAppList(self.mobile, self.time, appIdList)

    def __saveAppInfo(self,appList):
        # 获取AppInfo表中 pkgName => downFrom 字典, downFrom为列表[1, 2]
        appInfos = AppInfo().getAppByPkgList(list(appList.values()))
        # 生成批量保存数据
        insertApp = []
        for eadhId, eachPkg in appList.items():
            # 获取每个包的下载来源列表
            downFrom = appInfos.get(eachPkg, [])
            # 遍历App采集服务类
            for down_from,classObj in self.APP_LABEL_INFO.items():
                # 如果是已采集的数据则跳过
                if down_from in downFrom:
                    continue
                # 如果appPkgName在小黑屋里则跳过
                blackRoom = AppBlackRoom().getBlackRoom(eachPkg, down_from)
                if blackRoom is not None:
                    # logger.info("__APPLabel: %s,%d in BlackRoom the Data will continue", eachPkg, down_from)
                    continue
                # 调用App采集服务类获取采集数据
                appInfo = classObj.getappdatabypkgname(eachPkg)
                # 如果采集的数据为空则放进小黑屋并跳过
                if appInfo is None or len(appInfo) == 0:
                    AppBlackRoom().putBlackRoom(eachPkg, down_from)
                    # logger.info("__APPLabel: %s,%d info is None so put in BlackRoom", eachPkg, down_from)
                    continue
                appInfo['app_id'] = eadhId
                appInfo['create_time'] = datetime.now()
                insertApp.append(appInfo)
        if len(insertApp) == 0:
            return 0
        # 执行批量保存
        AppInfo().batchInsert(insertApp)
        logger.info("__APPLabel: %d add in AppInfo", len(insertApp))
        return len(insertApp)

    def __load_json(self,json_data):
        json_obj = {}
        try:
            json_obj = json.loads(json_data)
        except Exception as e:
            logger.error('load json is fail : %s' % e)
        return json_obj