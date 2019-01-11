# -*- coding: utf-8 -*-
'''
Created on 2016-5-5
数据分析
@author: jin
'''
from datetime import datetime

from lib.logger import logger
from .operator_analysis import OperatorAnalysis
from module.yiyiyuan import relatives_is_overdue, Addrloan
from module.yiyiyuan import YiAddressList
from module.yiyiyuan import YiFavoriteContact
from module.yiyiyuan import YiFriend
from module.yiyiyuan import YiUserInvest
from module.yiyiyuan import YiUser
from model.antifraud import AfBase
from model.open import OpenJxlStat
from model.base_model import DictMerge


class YyyAnalysis(object):

    def __init__(self, afbase):
        self.afbase = afbase
        self.aid = afbase.aid
        self.user_id = afbase.user_id
        self.loan_id = afbase.loan_id
        self.request_id = afbase.request_id
        self.jxlstat_id = afbase.jxlstat_id

        self.jxl_record = {}
        self.contact_due_data = {}

        self.__init_obj()
        self.__init_dict()

    def __init_obj(self):
        # db类
        self.dbUser = None
        self.dbContact = None
        self.dbYiAddressList = None

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
        self.detail_vsauth = {}
        self.detail_vsinvest = {}

        self.black_data = {}

    def run(self):
        # 分析,整合, 保存
        operator_data = self.__analysis()
        dict_data = self.__getResult(operator_data)
        return dict_data

    def __getResult(self, operator_data):
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

        # 5 db数据
        vsdb = DictMerge()
        vsdb.set(self.detail_vsauth)
        vsdb.set(self.detail_vsinvest)
        dict_data['vsdb'] = vsdb.get()

        # 6 黑名单数据
        dict_data['black'] = self.black_data

         # 7 整合结果
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
        # 分析数据
        # 1. 用户, 亲属 , 好友认证
        self.dbUser = YiUser().get(self.user_id)
        if self.dbUser is None:
            logger.error("user_id:%s db中没有纪录" % self.user_id)
            raise Exception(1002, "没有纪录")

        self.dbYiFriends = YiFriend().getByUserId(self.user_id)

        # 2. 初始化通讯录和报告
        self.dbYiAddressList = YiAddressList().getByUserid(self.user_id)

        self.jxl_record = OpenJxlStat().getById(self.jxlstat_id)

        # 3. 调用OperatorAnalysis
        operatorObj = OperatorAnalysis()
        operator_data = operatorObj.getOperatorData(self.dbYiAddressList,self.jxl_record)

        if operatorObj is not None:
            self.oDetail = operatorObj.oDetail
            self.oAddress = operatorObj.oAddress

        # 4. 跟业务模型数据交叉合并
        # 亲属联系人与是否逾期
        self.dbContact = YiFavoriteContact().getByUserId(self.user_id)
        relatives = {}
        if self.dbContact is not None:
            relatives['phone'] = self.dbContact.phone
            relatives['mobile'] = self.dbContact.mobile

        self.contact_due_data = relatives_is_overdue(relatives)

        if self.oAddress is not None:
            self.address_vscontact = self.oAddress.vsContact(relatives)
            self.address_vsuser = self.oAddress.vsUser(self.dbUser.mobile)
            self.address_vsloan = Addrloan(self.oAddress.pd_address).run()

        # 5. 与detail联合匹配
        if self.oDetail is not None:
            self.__detailVs()

        # 6. 黑名单
        self.black_data = self.__blackNum()
        return operator_data

    def __detailVs(self):
        # 1 vs 亲属
        self.detail_vscontact = self.__detailVsContact(self.oDetail, self.dbContact)

        # 2 vs address
        if self.oAddress is not None:
            self.detail_vsaddress = self.oDetail.vsAddress(self.oAddress.pd_address)

        # 3 vs auth
        self.detail_vsauth = self.oDetail.vsAuth(self.dbYiFriends)

        # 4 vs_invest
        self.detail_vsinvest = self.__detailVsInvest(self.oDetail, self.user_id)

    def __detailVsContact(self, oDetail, dbContact):
        # 2 vs 亲属,常用联系人
        if dbContact is None:
            return None

        dict_contact = {}
        dict_contact['phone'] = dbContact.phone
        dict_contact['mobile'] = dbContact.mobile
        detail_vscontact = oDetail.vsContact(dict_contact)
        return detail_vscontact

    def __detailVsInvest(self, oDetail, user_id):
        # vs 投资人
        oInvest = YiUserInvest()
        db_invest_me = oInvest.getInvestMe(user_id)
        db_my_invest = oInvest.getMyInvest(user_id)
        detail_vsinvest = oDetail.vsInvest(db_invest_me, db_my_invest)
        return detail_vsinvest

    def __blackNum(self):
        # 黑名单用户
        db_friend = YiFriend().blackNum(self.user_id)
        db_address = YiAddressList().blackNum(self.user_id)

        dict_data = {}
        if db_friend > 0:
            dict_data['db_auth_has_black'] = db_friend

        if db_address > 0:
            dict_data['addr_has_black'] = db_address

        return dict_data
