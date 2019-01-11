# -*- coding: utf-8 -*-
import json

from .af_address import AfAddress
from .af_contact import AfContact
from .af_detail import AfDetail
from .af_detailother import AfDetailOther
from .af_report import AfReport
from .af_ss_report import AfSsReport
from .af_addrloan import AfAddrloan
from .af_multi_match import AfMultiMatch
from .af_jcard_match import AfJcardMatch
from .af_relation_match import AfRelationMatch
from lib.application import db
from model.base_model import row2dict, DictMerge

class AfDbAgent(object):

    def __init__(self):
        self.dict_data = {}

    def import_db(self, dict_data):
        ''' 导入数据到各db表中'''
        dict_result = {}

        # 1 通讯录数据
        address_data = dict_data.get('address')
        res = AfAddress().addData(address_data)
        dict_result['address'] = res

        # 1.1 通讯录vsloan
        addr_loan = dict_data.get('addr_loan')
        res = AfAddrloan().addData(addr_loan)
        dict_result['addr_loan'] = res

        # 2 报告数据
        report_data = dict_data.get('report')
        res = AfReport().addData(report_data)
        dict_result['report'] = res

        # 2.1 上树报告数据
        ss_report_data = dict_data.get('ss_report')
        res = AfSsReport().addData(ss_report_data)
        dict_result['ss_report'] = res

        # 3 详情数据
        detail_data = dict_data.get('detail')
        res = AfDetail().addData(detail_data)
        dict_result['detail'] = res

        # 3.1 详情数据2
        detail_other_data = dict_data.get('detail_other')
        res = AfDetailOther().addData(detail_other_data)
        dict_result['detail_other'] = res

        # 4 详情vs联系人
        detail_vscontact = dict_data.get('contact')
        res = AfContact().addData(detail_vscontact)
        dict_result['contact'] = res

        self.dict_data = dict_data
        return dict_result

    def import_match_db(self,dict_data):
         # 7 二级关系
        multi_match_data = dict_data.get('multi_match')
        res = AfMultiMatch().addData(multi_match_data)
        return res

    def import_jcard_db(self,dict_data):
         # 间接关系(jaccard)
        jcard_match_data = dict_data.get('jcard_match')
        res = AfJcardMatch().addData(jcard_match_data)
        return res

    def import_relation_db(self,dict_data):
         # relation关系系数(relation)
        res = AfRelationMatch().addData(dict_data)
        return res
