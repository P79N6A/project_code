# -*- coding: utf-8 -*-
'''
数据分析 swap
'''
import requests
import json
from datetime import datetime

from lib.logger import logger
from util.custom_function import createSignByMd5
from .operator_analysis import OperatorAnalysis
from module.yiyiyuan import relatives_is_overdue, Addrloan
from model.antifraud import AfBase
from model.open import OpenJxlStat
from model.base_model import DictMerge


class PodloanAnalysis(object):

    def __init__(self, data):
        self.request_id = data.request_id
        self.aid = data.aid
        self.user_id = data.user_id
        self.loan_id = data.loan_id
        self.user_phone = data.phone

        self.jxl_record = {}
        self.address_info = {}
        self.contact_due_data = {}
        self.relatives = self.__relation(data.relation)
        self.operator = self.__load_json(data.operator)
        self.address_list = self.__load_json(data.address)

        self.base_id = 0
        self.jxlstat_id = 0
        self.source = 0
        self.__init_obj()
        self.__init_dict()

    def __init_obj(self):
        # analysis类
        self.oAddress = None
        self.oDetail = None

    def __init_dict(self):
        # 字典各项分析结果
        self.address_vscontact = {}
        self.address_vsuser = {}
        self.address_vsloan = {}
        self.detail_vscontact = {}
        self.detail_vsaddress = {}

    def run(self):
        # 分析,整合, 保存
        operator_data = self.__analysis()
        dict_data = self.__getResult(operator_data)
        return dict_data

    def __getResult(self,operator_data):
        # 整合结果集
        # 1 通讯录数据
        dict_data = {}
        address_data = DictMerge()
        address_data.set(operator_data.get('address'))
        address_data.set(self.address_vscontact)
        address_data.set(self.address_vsuser)
        dict_data['address'] = address_data.get()
        dict_data['addr_loan'] = self.address_vsloan

        ss_report_data = DictMerge()
        ss_report_data.set(operator_data.get('ss_report'))
        dict_data['ss_report'] = ss_report_data.get()

        report_data = DictMerge()
        report_data.set(operator_data.get('report'))
        dict_data['report'] = report_data.get()

        detail_data = DictMerge()
        detail_data.set(operator_data.get('detail'))
        detail_data.set(self.detail_vsaddress)
        dict_data['detail'] = detail_data.get()

        detail_other_data = DictMerge()
        detail_other_data.set(operator_data.get('detail_other'))
        dict_data['detail_other'] = detail_other_data.get()

        contact_data = DictMerge()
        contact_data.set(self.detail_vscontact)
        contact_data.set(self.contact_due_data)
        dict_data['contact'] = contact_data.get()

        # 5 整合结果
        dict_data = self.__mergeData(self.user_id, self.request_id, self.aid, dict_data)
        return dict_data

    def __mergeData(self, user_id, request_id, aid, dict_data):
        def mergeUser(dict_data):
            ''' 合并 user_id '''
            if dict_data is None:
                return {}
            if len(dict_data) == 0:
                return {}
            dict_data['user_id'] = user_id
            dict_data['request_id'] = request_id
            dict_data['aid'] = aid
            dict_data['create_time'] = datetime.now()
            return dict_data
        for k in dict_data:
            dict_data[k] = mergeUser(dict_data[k])
        return dict_data

    def __analysis(self):
        # 1.写入请求base
        self.__saveBase()
        
        # 2. 获取通讯录数据
        self.address_record = self.__get_address_record()

        # 3. 调用OperatorAnalysis
        operatorObj = OperatorAnalysis()
        operator_data = operatorObj.getOperatorData(self.address_record,self.jxl_record)
        
        if operatorObj is not None:
            self.oDetail = operatorObj.oDetail
            self.oAddress = operatorObj.oAddress
        #4. 跟业务模型数据交叉合并
        # 亲属联系人与是否逾期
        self.contact_due_data = relatives_is_overdue(self.relatives)

        if self.oAddress is not None :
            self.address_vscontact = self.oAddress.vsContact(self.relatives)
            self.address_vsuser = self.oAddress.vsUser(self.user_phone)
            self.address_vsloan = Addrloan(self.oAddress.pd_address).run()

        # 4. 与detail联合匹配
        if self.oDetail is not None:
            self.__detailVs()
        return operator_data

    def __saveBase(self):
        if int(self.operator.get('type')) == 1:
            req_id = 0 if self.operator.get('data') is None else self.operator.get('data')
            self.jxl_record = OpenJxlStat().getByRequestId(req_id)
            self.jxlstat_id = self.jxl_record.get('jxlstat_id') if self.jxl_record is not None else 0
        afBase = AfBase()
        afBase.request_id = self.request_id
        afBase.aid = self.aid
        afBase.user_id = self.user_id
        afBase.loan_id = self.loan_id
        afBase.jxlstat_id = self.jxlstat_id
        afBase.match_status = 0
        afBase.create_time = datetime.now()
        afBase.modify_time = datetime.now()
        afBase.save()
        self.base_id = afBase.id

    def __get_address_record(self):
        '''
        通讯录数据获取
        '''
        address_info = {}
        try:
            if(int(self.address_list.get('type')) == 2):
                sign = createSignByMd5({'user_id': self.user_id})
                data = {'user_id': self.user_id, 'sign': sign}
                r = requests.post(self.address_list.get('data'), data=data)
                if r.status_code != 200:
                    logger.error("request_id:%s aid:%s get address is fail" % (self.request_id, self.aid))
                resp_info = json.loads(r.text)
                if resp_info.get('rsp_code') != '0000':
                    logger.error("request_id:%s aid:%s get address is fail %s" % (self.request_id, self.aid, resp_info.get('rsp_msg')))
                address_info = resp_info.get('phone_book')  # 通讯录数据
        except Exception as e:
            logger.error("url:%s get address is fail" % e)
        return address_info

    def __detailVs(self):
        # 1 vs 亲属
        self.detail_vscontact = self.__detailVsContact(self.relatives)
        # 2 vs address
        if self.oAddress is not None:
            self.detail_vsaddress = self.oDetail.vsAddress(self.oAddress.pd_address)
            return self.detail_vsaddress

    def __detailVsContact(self,relatives):
        # 2 vs 亲属,常用联系人
        if relatives is None:
            return None

        detail_vscontact = self.oDetail.vsContact(relatives)
        return detail_vscontact

    def __relation(self, relation_json):
        res = {}
        try:
            relations = json.loads(relation_json)
            for relation in relations:
                if relation.get('phone') is None and relation.get('relation', 0) in [1, 2, 5, 6]:
                    res['phone'] = relation.get('mobile')
                else:
                    res['mobile'] = relation.get('mobile')
        except Exception as e:
            logger.error('load relation_json is fail : %s' % e)
        return res

    def __load_json(self,json_data):
        json_obj = {}
        try:
            json_obj = json.loads(json_data)
        except Exception as e:
            logger.error('load json is fail : %s' % e)
        return json_obj