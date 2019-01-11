# -*- coding:utf-8 -*-

import json
import numpy as np
import copy
from datetime import datetime

from lib.logger import logger
from .number_label_api import NumberLabelApi
from .third_party_api import thirdPartyApi
from .loan_logic import LoanLogic
from .operator_analysis_api import OperatorAnalysisApi
from model.analysis import AddressList
from model.open import OpenJxlStat
from model.base_model import DictMerge
from model.antifraudapi import ApiBase
# from module.yiyiyuan import relatives_is_overdue
from model.antifraudapi import ApiDbAgent
from model.antifraud import AfTagBase
import pandas as pd

class OperatorLogic():
    def __init__(self, data):
        self.credit_id = data.credit_id
        self.aid = data.aid
        self.contain = data.contain
        self.realname = data.realname
        self.user_phone = data.phone
        self.identity = data.identity
        self.contact = data.contact
        self.__init_obj()
        self.__init_dict()
        self.base_id = 0
        self.jxlstat_id = 0
        self.detail_phone = None

    def __init_obj(self):
        # analysis类
        self.oAddress = None
        self.oDetail = None

    def __init_dict(self):
        # 字典各项分析结果
        self.report_type = {}
        self.loan = {}
        self.address_vscontact = {}
        self.address_vsuser = {}
        self.detail_vsaddress = {}
        self.detail_vscontact = {}
        self.loan_total_all = {}
        self.address_tag = {}
        self.detail_tag = {}
        self.other_data = {}
        
    def run(self):
        #通过手机号获取jxl_stat
        self.jxl_record = OpenJxlStat().getByPhone(self.user_phone)
        if self.jxl_record is None:
            self.jxlstat_id = 0
            self.report_type = {"report_type": 0}
        else:
            self.jxlstat_id = self.jxl_record.get("jxlstat_id")
            self.report_type = {"report_type": self.jxl_record.get("source")}
        # 插入请求AfTagBase
        self.__saveAfTagBase()
        #插入请求表
        self.base_id,oBase = self.__saveApiBase()
        if self.base_id == 0 :
            return None
        try:
            # 1.分析
            # 运营商报告数据
            operator_data = self.__analysis()
            # 入库数据
            dict_data = self.__getResult(operator_data)
            # 整合结果
            dict_data_param = copy.deepcopy(dict_data)
            db_dict_data = self.__mergeData(dict_data_param)
            # 2.入库
            oApiDbAgent = ApiDbAgent()
            res = oApiDbAgent.api_import_db(db_dict_data)
            # 3.修改请求表状态
            oBase.changeStatus(2)
            # 4.拼接返回数据
            return_data = self.__formatData(dict_data)
            return return_data
        except Exception as e:
            logger.error("代码异常, StrategyId:%d , 异常信息: %s" % (self.credit_id, e))
            oBase.changeFail()
            return None

    def __analysis(self):
        # 1.初始化通讯录
        addrList = AddressList().getByUserPhoneDict(self.user_phone)
        # 2.通讯录号码标签
        number_label = NumberLabelApi()
        if len(addrList) > 0 :
            self.address_tag = number_label.getAddressTag(self.user_phone,addrList)

        user_id = self.__getUserId()
        # 3.学信、社保、公积金维度的数据信息验证
        third_party_dict = {
            "aid": self.aid,
            "user_id": user_id,
            "identity_code": self.identity,
            "realname": self.realname
        }
        self.other_data = thirdPartyApi(third_party_dict).run()

        # 4.调用OperatorAnalysis
        operatorObj = OperatorAnalysisApi()
        operator_data = operatorObj.getOperatorData(addrList, self.jxl_record)
        if operatorObj is not None:
            self.oDetail = operatorObj.oDetail
            self.oAddress = operatorObj.oAddress
            self.detail_phone = operatorObj.detail_phone
        '''
        #=============不需要返回start==========
        # 4. 跟业务模型数据交叉合并
        # 亲属联系人与是否逾期(需要与一亿元交互)  返回字段是{'com_r_overdue': 0, 'com_c_overdue': 0}
        relatives = {}
        if self.contact != '':
            relatives = json.loads(self.contact)
            relatives['phone'] = relatives.get("phone")
            relatives['mobile'] = relatives.get("mobile")
        self.contact_due_data = relatives_is_overdue(relatives)
        logger.info("bbbbb=%s" % self.contact_due_data)
        # self.dbContact = YiFavoriteContact().getByUserId(self.user_id)
        # relatives = {}
        # if self.dbContact is not None:
        #     relatives['phone'] = self.dbContact.phone
        #     relatives['mobile'] = self.dbContact.mobile
        #
        # self.contact_due_data = relatives_is_overdue(relatives)
        # =============不需要返回end==========
        '''

        relatives = {}
        if self.contact != '':
            relatives = json.loads(self.contact)
            relatives['phone'] = relatives.get("phone")
            relatives['mobile'] = relatives.get("mobile")
        pd_address = None
        if self.oAddress is not None:
            self.address_vscontact = self.oAddress.vsContact(relatives) 
            self.address_vsuser = self.oAddress.vsUser(self.user_phone) 
            pd_address = self.oAddress.pd_address
        self.loan,self.loan_total_all = LoanLogic(pd_address,self.user_phone,self.contact, self.contain).run()

        # 6.详单tag
        if self.detail_phone :
            try:
                calls = self.detail_phone['raw_data']['members']['transactions'][0]['calls']
                pd_detail = pd.DataFrame(calls)
                other_cell_phone = list(pd_detail['other_cell_phone'])
                self.detail_tag = number_label.getDetailTag(self.user_phone,other_cell_phone)
            except Exception as e:
                logger.error("号码标签vs详单异常: %s" % (e))
        # 7.与detail联合匹配
        if self.oDetail is not None:
            self.__detailVs()

        return operator_data

    def __getResult(self, operator_data):
        # 整合结果集
        # 1 通讯录数据
        dict_data = {}
        address_data = DictMerge()
        address_data.set(operator_data.get('address'))
        address_data.set(self.address_vscontact)
        address_data.set(self.address_vsuser)
        dict_data['address'] = address_data.get()

        loan_data = DictMerge()
        loan_data.set(self.loan)
        dict_data['loan'] = loan_data.get()

        summary_data = DictMerge()
        summary_data.set(self.loan_total_all)
        dict_data['summary_data'] = summary_data.get()

        detail_tag_data = DictMerge()
        detail_tag_data.set(self.detail_tag)
        dict_data['detail_tag'] = detail_tag_data.get()

        address_tag_data = DictMerge()
        address_tag_data.set(self.address_tag)
        dict_data['address_tag'] = address_tag_data.get()
        
        ss_report_data = DictMerge()
        ss_report_data.set(operator_data.get('ss_report'))
        dict_data['ss_report'] = ss_report_data.get()

        mh_report_data = DictMerge()
        mh_report_data.set(operator_data.get('mh_report'))
        dict_data['mh_report'] = mh_report_data.get()

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
        #contact_data.set(self.contact_due_data)
        dict_data['contact'] = contact_data.get()

        other_data = DictMerge()
        other_data.set(self.other_data)
        dict_data['other_data'] = other_data.get()

        return dict_data

    def __mergeData(self, dict_data):
        def mergeBase(dict_data):
            ''' 合并 base_id '''
            if dict_data is None:
                return {}
            if len(dict_data) == 0:
                return {}
            dict_data['base_id'] = self.base_id
            dict_data['create_time'] = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
            return dict_data
        for key,value in dict_data.items():
            if key == "loan":
                for k,v in value.items():
                    value[k] = mergeBase(value[k])
            else:
                dict_data[key] = mergeBase(dict_data[key])
        return dict_data

    def __detailVs(self):
        # 1 vs 亲属
        self.detail_vscontact = self.__detailVsContact(self.oDetail)
        # 2 vs address
        if self.oAddress is not None:
            #返回{'vs_phone_match': 0, 'vs_connect_match': 0, 'vs_duration_match': 0, 'vs_valid_match': 0}
            self.detail_vsaddress = self.oDetail.vsAddressApi(self.oAddress.pd_address)

    def __detailVsContact(self, oDetail):
        # 2 vs 亲属,常用联系人
        dict_contact = {}
        if self.contact != '':
            relatives = json.loads(self.contact)
            dict_contact['phone'] = relatives.get("phone")
            dict_contact['mobile'] = relatives.get("mobile")
        detail_vscontact = oDetail.vsContact(dict_contact)
        return detail_vscontact

    # 保存请求数据
    def __saveApiBase(self):
        oBase = ApiBase()
        oBase.credit_id = self.credit_id 
        oBase.aid = self.aid 
        oBase.contain = self.contain 
        oBase.realname = self.realname 
        oBase.mobile = self.user_phone 
        oBase.identity = self.identity 
        oBase.contact = self.contact 
        oBase.jxlstat_id = self.jxlstat_id
        oBase.report_type = self.report_type.get('report_type',0)
        oBase.status = 0
        oBase.create_time = datetime.now()
        oBase.modify_time = datetime.now()
        base_id = oBase.save()
        return base_id,oBase

    # 保存标签表数据
    def __saveAfTagBase(self):
        try:
            tagData = {
                'aid': self.aid,
                'base_id': self.credit_id,
                'user_id': 0,
                'phone': self.user_phone,
                'tag_status': 0,
                'create_time': datetime.now(),
                'modify_time': datetime.now()
            }
            return AfTagBase().addData(tagData)
        except Exception as e:
            logger.error("credit_id:%d save tag_base is fail: %s" % (self.credit_id, e))

    def __formatData(self,dict_data):
        data = {}
        data.update(dict_data.get("address"))
        data.update(dict_data.get("ss_report"))
        data.update(dict_data.get("mh_report"))
        data.update(dict_data.get("report"))
        data.update(dict_data.get("detail"))
        data.update(dict_data.get("detail_other"))
        data.update(dict_data.get("contact"))
        data.update(dict_data.get("detail_tag"))
        data.update(dict_data.get("other_data"))
        data.update(self.report_type)
        data['loan'] = self.loan
        data['summary_data'] = self.loan_total_all
        return data

    def __getUserId(self):
        aid = int(self.aid)
        if aid == 1:
            from module.yiyiyuan.model.yi_user import YiUser as User
        elif aid == 16:
            from module.huakashop.model.shop_loan_user import ShopLoanUser as User
        elif aid == 17:
            from module.yigeyinew.model.shop_loan_user import ShopLoanUser as User
        else:
            return 0
        oUser = User().getByMobile(self.user_phone)
        if oUser is None:
            return 0
        return oUser.user_id
