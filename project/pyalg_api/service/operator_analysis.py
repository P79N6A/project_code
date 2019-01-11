# -*- coding: utf-8 -*-
'''
运营商数据
'''
import time
from lib.logger import logger
from model.open import OpenJxlStat
from model.base_model import DictMerge

from module.address import Address
from module.detail import Detail
from module.report import analysis_report
from util.custom_function import getReportByUrl
from .number_label import NumberLabel
import pandas as pd

class OperatorAnalysis(object):

    
    def __init__(self):
        self.detail_phone = None
        self.detail_url_log = None
        self.oDetail = None
        self.oAddress = None

    def __analysis_address(self,address_list):
        address_data = {}
        try:
            self.oAddress = Address(address_list)
            address_data = self.oAddress.run()
        except Exception as e:
            logger.error("analysis address is fail: %s" % (e))
        return address_data
    
    def __analysis_detail(self,detail_url):
        detail_data = {}
        detail_other_data = {}
        try:
            json_data = getReportByUrl(detail_url)
            if json_data:
                self.detail_phone = json_data
                self.oDetail = Detail(json_data)
                detail_data,detail_other_data = self.oDetail.run()
        except Exception as e:
            logger.error("detail_url:%s detail is fail: %s" % (detail_url, e))
        return detail_data,detail_other_data

    def getOperatorData(self,address_list,record):
        # 1 运营商数据
        source = 0
        detail_data = detail_other_data = None
        if record is not None:
            source = record.get('source')
            report_url = record.get('report_url')
            detail_url = record.get('detail_url')
            self.detail_url_log = detail_url
            report_data = analysis_report(source,report_url)
            detail_data,detail_other_data =self.__analysis_detail(detail_url)
        try:
            #通话详单标签追加
            self._addNumberLabel()
        except Exception as e:
            logger.info("number_label: 通话详单错误:%s" % e)

        # 2 报告数据,仅聚信立有效
        dict_data = {}
        dict_data['ss_report'] = {}
        dict_data['report'] = {}
        if source in [1, 2]:
            dict_data['report'] = report_data
        elif source == 4:
            dict_data['ss_report'] = report_data

        # 3 通讯录
        address_data = self.__analysis_address(address_list)
        address_analysis = DictMerge()
        address_analysis.set(address_data)
        dict_data['address'] = address_analysis.get()
        

        # 4  详情数据
        detail_analysis = DictMerge()
        detail_analysis.set(detail_data)
        dict_data['detail'] = detail_analysis.get()

        # 5 详情其它表字段
        detail_other_analysis = DictMerge()
        detail_other_analysis.set(detail_other_data)

        repInfo = {}
        if source in [1, 2] and report_data.get('report_use_time'):
            repInfo['phone_register_month'] = report_data.get('report_use_time')
        elif source in [4, 5, 6] and report_data.get('phone_register_month'):
            repInfo['phone_register_month'] = report_data.get('phone_register_month')

        detail_other_analysis.set(repInfo)
        dict_data['detail_other'] = detail_other_analysis.get()

        return dict_data

    '''
    通话详单标签追加
    '''
    def _addNumberLabel(self):
        if self.detail_phone == None:
            logger.info("number_label: 通话详单错误error_url:%s" % self.detail_url_log)
            logger.error("number_label:没有通话详单")
            return False
        calls = self.detail_phone['raw_data']['members']['transactions'][0]['calls']
        pd_detail = pd.DataFrame(calls)
        number_label = NumberLabel()
        phone = list(set(pd_detail['cell_phone']))
        other_cell_phone = tuple(pd_detail['other_cell_phone'])
        detail_stat = number_label.detailApi(phone[0], other_cell_phone)
        if detail_stat == True:
            return True
        return False
