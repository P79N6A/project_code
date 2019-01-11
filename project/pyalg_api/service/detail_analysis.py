'''
聚信立通话纪录详单
'''
# %pylab inline
import pandas as pd
import requests
import datetime
import json
from datetime import datetime,timedelta
import re
import traceback
from lib.logger import logger

class DetailAnalysis(object):

    def __init__(self, data):
        # 检查数据合法性
        self.detail = data

    def _analysis(self):
        try:
            calls = self.detail['raw_data']['members']['transactions'][0]['calls']
            pd_detail = pd.DataFrame(calls)
            # pd_detail = pd_detail.dropna()
            def get_right_moblie(phone):
                if len(phone) <= 5:
                    return None
                substr = '^1[2-9][0-9]\d{8}$'
                moblie = re.search(substr, phone)
                if moblie:
                    phone = moblie.group()
                return phone
            deal_detail = pd_detail.other_cell_phone.apply(get_right_moblie).dropna()
            pd_detail['deal_mobile'] = deal_detail
            grouped = pd_detail.groupby('deal_mobile')[['start_time', 'use_time']]
            detail_data = grouped.apply(self.get_max_min_time)
            if len(detail_data) > 0:
                return detail_data.to_dict()
            return None
        except Exception as e:
            logger.error("jxl_datas analysis fail %s" % e)

    # def _getReMobile(self):
    #     # 手机正则
    #     substr = '1[2-9][0-9]\d{8}'
    #     p = re.compile(substr, re.DOTALL)
    #     return p

    def get_max_min_time(self,group):
        return {'min_time': group['start_time'].min(), 'max_time': group['start_time'].max(), 'call_times': group['use_time'].count(),'use_time': group['use_time'].sum()}
