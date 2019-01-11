# coding=utf-8
import os
import time
import json
from datetime import datetime

from lib.config import get_config
from lib.logger import logger
from model.analysis import SyncIdList
from module.yiyiyuan.model import YiUser,YiApplicationList
from service import APPLabel
from .base_command import BaseCommand

class SyncappCommand(BaseCommand):
    # class attr
    ALLRUN = 50
    SYNC_TYPE = SyncIdList.SYNC_APP

    def __init__(self):
        super(SyncappCommand, self).__init__()
        self.type = 2
        self.oSyncId = None


    """
    同步APP信息列表到analysis数据库
    """
    def runSyncApp(self):
        start = time.time()
        # get sync_id
        sync_id = self.__get_sync_id()
        if sync_id is None:
            logger.error("there is no sync_ids")
            return False

        startId = sync_id.get('start_id')
        endId = sync_id.get('end_id')
        # 1. get new app
        app_date_list = self.__getAppDate(startId, endId)
        if app_date_list is None:
            logger.error("there is no app_date_list ,start_id:%s --- end_id:%s" % (startId, endId))
            return False
        # 2. set app in ssdb
        save_res = self.__setAppList(app_date_list)
        logger.info("runSyncApp sync_res is %d ,start_id:%s --- end_id:%s" % (save_res, startId, endId))
        # lock finished`
        self.oSyncId.lockStatus(SyncIdList.STATUS_SUCCESS)
        # set last id
        set_last_id = self.__setLastId(sync_id)
        end = time.time()
        print(end - start)
        return True

    # 获取同步ID
    def __get_sync_id(self):
        # get detail sync_id
        self.oSyncId = SyncIdList().getInitSyncId(self.SYNC_TYPE)
        if self.oSyncId is None:
            return None
        # lock status
        if not get_config().TESTING:
            self.oSyncId.lockStatus(SyncIdList.STATUS_DOING)
        # set sync id
        return {'start_id': self.oSyncId.start_id, 'end_id': self.oSyncId.end_id}

    def __getAppDate(self,start_id,end_id):
        if not end_id or not start_id:
            return None
        appDate = YiApplicationList().getDateByUserIds(start_id, end_id, self.type)
        return appDate

    def __setAppList(self,app_list):
        if len(app_list) == 0:
            return 0

        n = 0
        for appInfo in app_list:
            try:
                userId = str(appInfo[0])
                userInfo = YiUser().getByUserId(userId)
                if userInfo is None:
                    return 0

                data = {}
                data['mobile'] = userInfo.mobile
                data['applist'] = appInfo[1]
                data['time'] = appInfo[2].strftime('%Y-%m-%d %H:%M:%S')
                obj = APPLabel(data)
                result = obj.run()
                resCode = result.get('code', -100)
                if resCode == 0:
                    n += 1
                else:
                    logger.error("%s setAppList error, code is %s", userId,resCode)
            except Exception as e:
                logger.error("__setAppList error is %s",e)
        return n


    def __setLastId(self,sync_id):
        # start_id
        start_id = sync_id.get('end_id')
        end_id = start_id + self.ALLRUN
        # check max_id
        max_id = YiApplicationList().get_maxid()
        if end_id > max_id:
            end_id = max_id

        # save sync_id_list
        syncIdinfo = {
            'start_id': start_id,
            'end_id': end_id,
            'sync_status': SyncIdList.STATUS_INIT,
            'sync_type': SyncIdList.SYNC_APP,
            'create_time': datetime.now(),
            'modify_time': datetime.now()
        }
        syncidlist = SyncIdList().addData(syncIdinfo)
        return syncidlist
