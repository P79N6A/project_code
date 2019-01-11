# -*- coding: utf-8 -*-
import json
import os
import pandas as pd
from datetime import datetime, timedelta


from lib.logger import logger
from .base_command import BaseCommand
from model.open import OpenJxlStat
from model.antifraud import AfWsm
from module.yiyiyuan import YiUserRemitList
from module.yiyiyuan import YiFavoriteContact
from module.detail import Detail


class WsmCommand(BaseCommand):
    def __init__(self):
        super(WsmCommand, self).__init__()

    # 为什么
    def runwsm(self,start_time = None , end_time = None):
        last_day = datetime.now() + timedelta(days=-1)
        if start_time is None:
            start_time = last_day.strftime('%Y-%m-%d 00:00:00')

        if end_time is None:
            end_time = last_day.strftime('%Y-%m-%d 23:59:59')
        #getmobiles
        users_info = YiUserRemitList().getMobiles(start_time,end_time)
        if users_info is None:
            logger.error("users_info:no data to dealwith")
            return False

        #获取开放平台聚信立信息
        for user in users_info:
            try:
                # 1. get detail_datas
                Jxl_datas = OpenJxlStat().getByPhone(user.mobile)
                if Jxl_datas is None:
                    logger.error("mobile:%s  can't get Jxl_datas" % (user.mobile))
                    continue

                url = Jxl_datas.get('detail_url')
                detail_datas = OpenJxlStat().getDetail(url)
                if detail_datas is None:
                    logger.error("json_url:%s  can't get detail_data" % (url))
                    continue

                # 2. get contacts_mobile and relatives_phone
                mobile_phone = YiFavoriteContact().getByUserId(user.user_id)
                if mobile_phone is None:
                    logger.error("user_id:%s  can't get mobile_phone" % (user.user_id))
                    continue
                dict_contact_phone = {'mobile': mobile_phone.mobile, 'phone': mobile_phone.phone}

                # 3. analysis  detail_data
                analysis_datas = Detail(detail_datas).runWsm(dict_contact_phone)
                n = 0
                for key, value in analysis_datas.items():
                # 4. save analysis_data
                    add_res = AfWsm().addWsm(value,user)
            except Exception as e:
                logger.error("user.mobile:%s is fail: %s" % (user.mobile, e))
        return True