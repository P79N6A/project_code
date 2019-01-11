# -*- coding: utf-8 -*-

import json
import pandas as pd

from model.analysis import AddressList
from model.analysis import PhoneTagList
from module.address import Address
from model.antifraud.af_base import AfBase
from model.open import OpenJxlStat
from util.custom_function import getReportByUrl
from module.detail import Detail
from module.report import analysis_report
from model.base_model import DictMerge
from model.analysis.reverse_detail_list import ReverseDetailList
from model.open import OpenJxlStat
#from service import DetailAnalysis
from model.antifraud.af_tag_base import AfTagBase
from lib.logger import logger
from model.antifraud.af_address_tag import AfAddressTag
from model.antifraud.af_detail_tag import AfDetailTag

class NumberLabel(object):
    #通讯录API
    def mailApi(self, phone):
        if phone is None:
            return False
        mail_handler_api = self._mailListHandlerApi(phone)
        if mail_handler_api == False:
            return False
        #保存记录
        return self._saveData(mail_handler_api)
    #详单API
    def detailApi(self, phone, detail_phone):
        if phone is None or detail_phone is None:
            return False
        if type(detail_phone).__name__ != 'tuple':
            logger.info("number_label: detail_tuple:%s" % phone)
            return False
        detail_handler_api = self._detailHandlerApi(phone, detail_phone)
        if detail_handler_api == False:
            return False
        #保存存数据
        return self._saveDetailData(detail_handler_api)

    #通讯录
    def runMail(self, tag_base):
        return self._mailListHandler(tag_base)

    #通话详单
    def runDetail(self, tag_base):
        return self._detailHandler(tag_base)

    '''
    获取af_base和af_tag_baes表中的数据
    '''
    def _getTagAndBaseData(self, phone):
        if phone is None:
            return False
        # 查找af_tag_base表中的数据
        tag_base = AfTagBase().getDataByPhone(phone)
        if tag_base is None:
            logger.info("number_label: %s手机号不存在tag_base" % phone)
            return False
        # 查找af_base表中的数据
        base_info = AfBase().getBaseForbaseid(tag_base.base_id)
        if base_info is None:
            logger.info("number_label: %s手机号不存在base" % phone)
            return False
        dict = {}
        try:
            # user_id
            dict['user_id'] = tag_base.user_id
            dict['loan_id'] = base_info.loan_id
            dict['aid'] = base_info.aid
            dict['request_id'] = base_info.request_id
            return dict
        except Exception as err:
            print(err)
            return False

    '''
    通讯录API
    '''
    def _mailListHandlerApi(self, phone):
        try:
            #获取af_base和af_tag_baes表中的数据
            dict_info =self._getTagAndBaseData(phone)
            if dict_info == False:
                return False

            # 初始化通讯录
            address_list = AddressList().getByUserPhoneDict(phone)
            # 格式通讯录的手机号
            phone_list = self._getUserFordb(address_list)
            phone_list = phone_list.phone
            # 通讯录条数
            phone_list_num = phone_list.count()
            # 将手机号转成元组
            phone_list = self._getTupleList(phone_list)
            if not phone_list:
                logger.info("number_label: %s不存在通讯录" % phone)
                return False

            # 号码标签条数
            label_num = self._getNumberLableNum(phone_list)
            if not label_num:
                logger.info("number_label: %s通讯录号码标签解析失败" % phone)
                print("号码标签解析失败！")
                return False
            dict_info['label_num'] = label_num
            # 通讯录号码数
            dict_info['mail_list_num'] = len(phone_list)
            # 通讯录去重号码数
            dict_info['weight_loss_num'] = len(set(phone_list))
            return dict_info
        except Exception as err:
            print(err)
            return False

    '''
    通话详单API
    '''
    def _detailHandlerApi(self, phone, phone_detail):
        try:
            # 获取af_base和af_tag_baes表中的数据
            dict_info = self._getTagAndBaseData(phone)
            if dict_info == False:
                return False
            label_num = self._getNumberLableNum(phone_detail)
            if not label_num:
                logger.info("number_label: %s通话详单号码标签解析失败" % phone)
                print("号码标签解析失败！")
                return False
            dict_info["label_num"] = label_num
            # 详单通话次数
            dict_info["detail_num"] = len(phone_detail)
            # 详单通话号码数
            dict_info["weight_loss_detail_num"] = len(set(phone_detail))

            return dict_info
        except Exception as err:
            logger.error("number_label: detail_error:%s" % err)
            print(err)
            return False

    '''
    详单处理
    '''
    def _detailHandler(self,tag_base):
        check_phone = self._checkKeyBool("phone", tag_base)

        if not check_phone:
            return False
        phone = tag_base.phone
        if not phone or phone == 0:
            print("手机号不能为空")
            return False

        try:
            oAf_base = AfBase()
            base_info = oAf_base.getBaseForbaseid(tag_base.base_id)
            phone_detail = self._downDetail(base_info)
            if not phone_detail:
                print("暂无详单数据")
                return False
            phone_detail = self._analysisDetail(phone_detail)
            dict_info = {}
            #user_id
            dict_info['user_id'] = tag_base.user_id

            # loan_id
            loan_id = 0
            # request_id
            request_id = 0
            if base_info:
                loan_id = base_info.loan_id
                request_id = base_info.request_id

            dict_info['loan_id'] = loan_id
            dict_info['request_id'] = request_id

            # 详单通话次数
            dict_info['detail_num'] = len(phone_detail)

            # 详单通话号码数
            dict_info['weight_loss_detail_num'] = len(set(phone_detail))

            # 号码标签条数
            phone_detail = tuple(phone_detail)
            label_num = self._getNumberLableNum(phone_detail)
            if not label_num:
                print("号码标签解析失败！")
                return False
            dict_info['label_num'] = label_num

            return dict_info
        except AttributeError as error:
            print(error)
            return False

    '''
    通讯录处理
    '''
    def _mailListHandler(self, tag_base):
        check_key = self._checkKeyBool("phone", tag_base)
        if not check_key:
            return False
        phone = tag_base.phone
        if not phone or phone == 0:
            print("手机号不能为空")
            return False

        # 初始化通讯录
        address_list = AddressList().getByUserPhoneDict(phone)
        # 格式通讯录的手机号
        phone_list = self._getUserFordb(address_list)
        phone_list = phone_list.phone
        # 通讯录条数
        phone_list_num = phone_list.count()
        # 将手机号转成元组
        phone_list = self._getTupleList(phone_list)
        if not phone_list:
            return False

        try:
            dict_info = {}
            # 号码标签条数
            label_num = self._getNumberLableNum(phone_list)

            if not label_num:
                print("号码标签解析失败！")
                return False
            label_num['label_num'] = label_num

            #user_id
            label_num['user_id'] = tag_base.user_id
            #loan_id
            oAf_base = AfBase()
            base_info = oAf_base.getBaseForbaseid(tag_base.base_id)
            loan_id = 0
            if base_info:
                loan_id = base_info.loan_id
            label_num['loan_id'] = loan_id
            #通讯录号码数
            label_num['mail_list_num'] = len(phone_list)
            #通讯录去重号码数
            label_num['weight_loss_num'] = len(set(phone_list))

            return label_num

        except AttributeError as error:
            print(error)
            return False
        except Exception as err:
            print(err)
            return False

    '''
    获取号码标签
        返回条数
    '''
    def _getNumberLableNum(self, phone_list):
        try:
            # 获取号标的标签
            oPhoneTagList = PhoneTagList()
            get_all = oPhoneTagList.getLabelAll(list(set(phone_list)))

            #将号码标签取出的数据转换成list
            number_label = [[d.phone, d.tag_type, d.other_info] for d in get_all]
            number_label = pd.DataFrame(data=number_label, columns=["phone", "tag_type", "other_info"])
            #格式通讯录
            pd_phone_list = pd.Series(phone_list)
            label_dict = {}
            #广告推销电话
            advertisement_tel = self._getRecruitInfo(pd_phone_list, number_label, "广告推销电话")
            label_dict["advertisement_tel"] = advertisement_tel

            #快递送餐电话
            express_tel = self._getRecruitInfo(pd_phone_list, number_label, "快递送餐电话")
            label_dict["express_tel"] = express_tel

            #骚扰电话电话
            harass_tel = self._getRecruitInfo(pd_phone_list, number_label, "骚扰电话")
            label_dict["harass_tel"] = harass_tel

            #房产中介电话
            house_propert_tel = self._getRecruitInfo(pd_phone_list, number_label, "房产中介电话")
            label_dict["house_propert_tel"] = house_propert_tel

            #疑似欺诈电话
            cheat_tel = self._getRecruitInfo(pd_phone_list, number_label, "疑似欺诈电话")
            label_dict["cheat_tel"] = cheat_tel

            #企业电话
            enterprise_tel = self._getRecruitInfo(pd_phone_list, number_label, "企业电话")
            label_dict["enterprise_tel"] = enterprise_tel

            #招聘猎头电话
            recruit_tel = self._getRecruitInfo(pd_phone_list, number_label, "招聘猎头电话")
            label_dict["recruit_tel"] = recruit_tel

            #出租车电话
            lease_car_tel = self._getRecruitInfo(pd_phone_list, number_label, "出租车电话")
            label_dict["lease_car_tel"] = lease_car_tel

            #教育培训电话
            education_tel = self._getRecruitInfo(pd_phone_list, number_label, "教育培训电话")
            label_dict["education_tel"] = education_tel

            #保险理财电话
            insurance_tel = self._getRecruitInfo(pd_phone_list, number_label, "保险理财电话")
            label_dict["insurance_tel"] = insurance_tel

            #响一声电话
            sound_a_sound_tel = self._getRecruitInfo(pd_phone_list, number_label, "响一声电话")
            label_dict["sound_a_sound_tel"] = sound_a_sound_tel

            #客服电话
            customer_service_tel = self._getRecruitInfo(pd_phone_list, number_label, "客服电话")
            label_dict["customer_service_tel"] = customer_service_tel

            #违法犯罪电话
            illegality_tel = self._getRecruitInfo(pd_phone_list, number_label, "违法犯罪电话")
            label_dict["illegality_tel"] = illegality_tel

            return label_dict
        except Exception as err:
            logger.error("number_label: detail_ji:%s" % err)
            return False
    '''
       处理
    '''
    def _getRecruitInfo(self, source_data, lookup_data, matching_str):
        #数据
        source_len = len(source_data)
        tag_type_str = "^%s$" % (matching_str)
        label_phone = lookup_data[lookup_data.tag_type.str.contains(tag_type_str)].phone

        #招聘猎头号码数量数
        aeavy_number_lable = 0
        if len(label_phone) != 0:
            for phone in label_phone:
                phone_count = source_data[source_data.str.contains(r"^%s$"%phone)].count()
                aeavy_number_lable += phone_count
        #招聘猎头去重号码数量
        weight_loss_label = len(label_phone)

        #招聘猎头号码数量占比
        aeavy_number_proportion = "%.4f" % (aeavy_number_lable / source_len)
        #招聘猎头电话去重号码数占比
        weight_loss_proportion = "%.4f" % (weight_loss_label / len(set(source_data)))

        #按号码数量统计招聘猎头电话被标记次数
        aeavy_number_sign = 0
        try:
            if len(label_phone) != 0:
                for phone in label_phone:
                    phone_count = source_data[source_data.str.contains(r"^%s$"%phone)].count()
                    sign_info = lookup_data[lookup_data.phone.str.contains(r"^%s$"%phone)].other_info
                    if len(sign_info) == 0:
                        continue
                    sign_sum = 0
                    for sign in sign_info:
                        try:
                            sign = json.loads(sign)
                            sign_total = int(sign.get(matching_str,0))
                        except Exception as e:
                            # logger.info("json resolve error, %s" % e)
                            sign_total = 0
                        sign_sum += sign_total
                    aeavy_number_sign += sign_sum * phone_count
        except KeyError as error:
            logger.info("number_label: aeavy_number_sign:%s" % error)
            print(error)
        #按去重号码数统计招聘猎头电话被标记次数
        weight_loss_sign = 0
        try:
            if len(label_phone) != 0:
                for phone in label_phone:
                    sign_info = lookup_data[lookup_data.phone.str.contains(r"^%s$"%phone)].other_info
                    if len(sign_info) == 0:
                        continue
                    sign_sum = 0
                    for sign in sign_info:
                        try:
                            sign = json.loads(sign)
                            sign_total = int(sign.get(matching_str,0))
                        except Exception as e:
                            # logger.info("json resolve error, %s" % e)
                            sign_total = 0
                        sign_sum += sign_total
                    weight_loss_sign += sign_sum
        except KeyError as error:
            logger.info("number_label: weight_loss_sign_error:%s" % error)
            print(error)

        dict_data = {"aeavy_number_lable"      : str(aeavy_number_lable),
                     "weight_loss_label"       : str(weight_loss_label),
                     "aeavy_number_proportion" : str(aeavy_number_proportion),
                     "weight_loss_proportion"  : str(weight_loss_proportion),
                     "aeavy_number_sign"       : str(aeavy_number_sign),
                     "weight_loss_sign"        : str(weight_loss_sign)
                     }
        #logger.info("number_label: detail_dict_data:%s" % json.dumps(dict_data))
        return json.dumps(dict_data)

    '''
    格式返回手机号
    '''
    def _getUserFordb(self, address_list):
        data = [(d['phone']) for d in address_list]
        pd_address = pd.DataFrame(data=data, columns=['phone'])
        return pd_address

    '''
    将通讯录手机号转换成元组
    '''
    def _getTupleList(self, list_data):
        list_data_count = list_data.count()
        if list_data_count == 0:
            print("没有通讯录!")
            return False
        list_phone = []
        for phone in list_data:
            list_phone.append(phone)
        #list_phone.append('13716248789')  #用于测试
        #list_phone.append('18888820817')
        #list_phone.append('18911224494')
        #list_phone.append('13716248789')
        #list_phone.append('13716248789')
        list_phone = tuple(list_phone)
        return list_phone

    '''
    判断值是否在这个对象中
    '''
    def _checkKeyBool(self, value, list_data):
        is_exists = hasattr(list_data, value)
        if is_exists:
            return True

        print("\"%s\" 类不存在属性 \"%s\" " % (list_data, value))
        return False

    '''
    解析详单数据
    '''
    def _analysisDetail(self, detail_list):
        try:
            #detail_list = json.loads(detail_info)
            #detail_list = detail_list['phoneArr']
            detail_data = []
            if isinstance(detail_list, dict):
                for k,v in detail_list.items():
                    detail_data.append(v)
            if isinstance(detail_list, list):
                detail_data = detail_list
            return detail_data
        except KeyError as errorkey:
            print("KeyError:%s" % errorkey)
            return False
        except IndexError as error1:
            print(error1)
            return False
        except json.decoder.JSONDecodeError as error2:
            print("json error:%s" % error2)
            return False

    '''
    详单下载
    '''
    def _downDetail(self, base_info):
        if not base_info:
            return False
        try:
            jxl_record = OpenJxlStat().getById(base_info.jxlstat_id)
            if not jxl_record:
                print("暂无通讯详单数据")
                return False

            detail_url = jxl_record.get('detail_url')
            detail_data = OpenJxlStat().getDetail(detail_url)
            calls = detail_data['raw_data']['members']['transactions'][0]['calls']
            pd_detail = pd.DataFrame(calls)
            if len(pd_detail) == 0:
                print("暂无详单数据")
                return False
            detail_list = []
            #detail_list = ['13716248789', '18888820817', '18911224494', '13716248789']
            for phone in pd_detail.other_cell_phone:
                detail_list.append(phone)
            return detail_list
        except KeyError as error:
            print(error)
            return False
        except Exception as error:
            print(error)
            return False

    '''
    保存通记录数据
    '''
    def _saveData(self,  proportion):
        if proportion is None:
            return False

        # 保存记录
        save_data = AfAddressTag().saveResources(proportion)
        if save_data:
            return save_data
        return False

    '''
    保存详单
    '''
    def _saveDetailData(self, proportion):
        if proportion is None:
            return False

        # 保存记录
        save_data = AfDetailTag().saveResources(proportion)
        if save_data:
            return save_data
        return False