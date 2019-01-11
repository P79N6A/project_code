# -*- coding: utf-8 -*-
import json

from .api_address import ApiAddress
from .api_contact import ApiContact
from .api_detail import ApiDetail
from .api_detailother import ApiDetailOther
from .api_report import ApiReport
from .api_ss_report import ApiSsReport
from .api_mh_report import ApiMhReport
from .api_loan import ApiLoan
from .api_detail_tag import ApiDetailTag
from .api_address_tag import ApiAddressTag
from .api_other_data import ApiOtherData
from .api_summary_loan import ApiSummaryLoan
from lib.application import db
from model.base_model import row2dict, DictMerge

class ApiDbAgent(object):

    def __init__(self):
        self.dict_data = {}

    def api_import_db(self, dict_data):
        ''' 导入数据到各db表中'''
        dict_result = {}

        # 1 通讯录数据
        address_data = dict_data.get('address')
        res = ApiAddress().addData(address_data)
        dict_result['address'] = res

        # 1.1 通讯录vsloan
        loan = dict_data.get('loan')
        if loan is not None:
            loan_res = []
            for key, value in loan.items():
                res = ApiLoan().addData(value)
                loan_res.append(res)
            dict_result['loan'] = loan_res

        # 1.2 通讯录vsloan => summaryData
        summary_data = dict_data.get('summary_data')
        res = ApiSummaryLoan().addData(summary_data)
        dict_result['summary_data'] = res

        # 2 报告数据
        report_data = dict_data.get('report')
        res = ApiReport().addData(report_data)
        dict_result['report'] = res

        # 2.1 上树报告数据
        ss_report_data = dict_data.get('ss_report')
        res = ApiSsReport().addData(ss_report_data)
        dict_result['ss_report'] = res

         # 2.2 魔盒报告数据
        mh_report_data = dict_data.get('mh_report')
        res = ApiMhReport().addData(mh_report_data)
        dict_result['mh_report'] = res

        # 3 详情数据
        detail_data = dict_data.get('detail')
        res = ApiDetail().addData(detail_data)
        dict_result['detail'] = res

        # 3.1 详情数据2
        detail_other_data = dict_data.get('detail_other')
        res = ApiDetailOther().addData(detail_other_data)
        dict_result['detail_other'] = res

        # 4 详情vs联系人
        detail_vscontact = dict_data.get('contact')
        res = ApiContact().addData(detail_vscontact)
        dict_result['contact'] = res

        # 4 详情vs联系人
        address_tag = dict_data.get('address_tag')
        res = ApiAddressTag().addData(address_tag)
        dict_result['address_tag'] = res

        # 4 详情vs联系人
        detail_tag = dict_data.get('detail_tag')
        res = ApiDetailTag().addData(detail_tag)
        dict_result['detail_tag'] = res

        # 5 学信社保公积金银行流水信息
        other_data = dict_data.get('other_data')
        res = ApiOtherData().addData(other_data)
        dict_result['other_data'] = res

        self.dict_data = dict_data
        return dict_result
