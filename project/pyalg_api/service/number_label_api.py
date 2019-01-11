# -*- coding: utf-8 -*-

import json
import pandas as pd

from model.analysis import PhoneTagList
from lib.logger import logger

class NumberLabelApi(object):
    '''
    通讯录API
    '''

    def getAddressTag(self, phone, addrList):
        address_tag = {}
        if not phone:
            return address_tag
        if len(addrList) == 0:
            return address_tag
        addr_phone_list = [d['phone'] for d in addrList]
        addr_phone_uniq = list(set(addr_phone_list))
        label_num = self._getNumberLableNum(addr_phone_list)
        if not label_num:
            logger.info("number_label: %s通讯录号码标签解析失败" % phone)
            print("号码标签解析失败！")
            return address_tag
        address_tag['ads_num'] = len(addr_phone_list)
        address_tag['ads_num_uniq'] = len(addr_phone_uniq)
        address_tag['advertis'] = label_num.get('advertisement_tel')
        address_tag['express'] = label_num.get('express_tel')
        address_tag['harass'] = label_num.get('harass_tel')
        address_tag['house_agent'] = label_num.get('house_propert_tel')
        address_tag['cheat'] = label_num.get('cheat_tel')
        address_tag['company_tel'] = label_num.get('enterprise_tel')
        address_tag['invite'] = label_num.get('recruit_tel')
        address_tag['taxi'] = label_num.get('lease_car_tel')
        address_tag['education'] = label_num.get('education_tel')
        address_tag['insurance'] = label_num.get('insurance_tel')
        address_tag['ring'] = label_num.get('sound_a_sound_tel')
        address_tag['service_tel'] = label_num.get('customer_service_tel')
        address_tag['delinquency'] = label_num.get('illegality_tel')
        return address_tag

    '''
    通话详单API
    '''

    def getDetailTag(self, phone, phone_detail):
        detail_tag = {}
        if not phone:
            return detail_tag
        if len(phone_detail) == 0:
            return detail_tag
        phone_detail_uniq = list(set(phone_detail))
        label_num = self._getNumberLableNum(phone_detail)
        if not label_num:
            logger.info("number_label: %s通讯录号码标签解析失败" % phone)
            print("号码标签解析失败！")
            return detail_tag
        detail_tag['detail_saynum'] = len(phone_detail)
        detail_tag['detail_telnum'] = len(phone_detail_uniq)
        detail_tag['advertis'] = label_num.get('advertisement_tel')
        detail_tag['express'] = label_num.get('express_tel')
        detail_tag['harass'] = label_num.get('harass_tel')
        detail_tag['house_agent'] = label_num.get('house_propert_tel')
        detail_tag['cheat'] = label_num.get('cheat_tel')
        detail_tag['company_tel'] = label_num.get('enterprise_tel')
        detail_tag['invite'] = label_num.get('recruit_tel')
        detail_tag['taxi'] = label_num.get('lease_car_tel')
        detail_tag['education'] = label_num.get('education_tel')
        detail_tag['insurance'] = label_num.get('insurance_tel')
        detail_tag['ring'] = label_num.get('sound_a_sound_tel')
        detail_tag['service_tel'] = label_num.get('customer_service_tel')
        detail_tag['delinquency'] = label_num.get('illegality_tel')
        return detail_tag

    '''
    获取号码标签
        返回条数
    '''

    def _getNumberLableNum(self, phone_list):
        try:
            # 获取号标的标签
            oPhoneTagList = PhoneTagList()
            get_all = oPhoneTagList.getLabelAll(list(set(phone_list)))

            # 将号码标签取出的数据转换成list
            number_label = [[d.phone, d.tag_type, d.other_info] for d in get_all]
            number_label = pd.DataFrame(data=number_label, columns=["phone", "tag_type", "other_info"])
            # 格式通讯录
            pd_phone_list = pd.Series(phone_list)
            label_dict = {}
            # 广告推销电话
            advertisement_tel = self._getRecruitInfo(pd_phone_list, number_label, "广告推销电话")
            label_dict["advertisement_tel"] = advertisement_tel

            # 快递送餐电话
            express_tel = self._getRecruitInfo(pd_phone_list, number_label, "快递送餐电话")
            label_dict["express_tel"] = express_tel

            # 骚扰电话电话
            harass_tel = self._getRecruitInfo(pd_phone_list, number_label, "骚扰电话")
            label_dict["harass_tel"] = harass_tel

            # 房产中介电话
            house_propert_tel = self._getRecruitInfo(pd_phone_list, number_label, "房产中介电话")
            label_dict["house_propert_tel"] = house_propert_tel

            # 疑似欺诈电话
            cheat_tel = self._getRecruitInfo(pd_phone_list, number_label, "疑似欺诈电话")
            label_dict["cheat_tel"] = cheat_tel

            # 企业电话
            enterprise_tel = self._getRecruitInfo(pd_phone_list, number_label, "企业电话")
            label_dict["enterprise_tel"] = enterprise_tel

            # 招聘猎头电话
            recruit_tel = self._getRecruitInfo(pd_phone_list, number_label, "招聘猎头电话")
            label_dict["recruit_tel"] = recruit_tel

            # 出租车电话
            lease_car_tel = self._getRecruitInfo(pd_phone_list, number_label, "出租车电话")
            label_dict["lease_car_tel"] = lease_car_tel

            # 教育培训电话
            education_tel = self._getRecruitInfo(pd_phone_list, number_label, "教育培训电话")
            label_dict["education_tel"] = education_tel

            # 保险理财电话
            insurance_tel = self._getRecruitInfo(pd_phone_list, number_label, "保险理财电话")
            label_dict["insurance_tel"] = insurance_tel

            # 响一声电话
            sound_a_sound_tel = self._getRecruitInfo(pd_phone_list, number_label, "响一声电话")
            label_dict["sound_a_sound_tel"] = sound_a_sound_tel

            # 客服电话
            customer_service_tel = self._getRecruitInfo(pd_phone_list, number_label, "客服电话")
            label_dict["customer_service_tel"] = customer_service_tel

            # 违法犯罪电话
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

        dict_data = {"aeavy_number_lable": 111,
                     "weight_loss_label": 111,
                     "aeavy_number_proportion": 111,
                     "weight_loss_proportion": 111,
                     "aeavy_number_sign": 111,
                     "weight_loss_sign": 111
                     }
        # 数据
        source_len = len(source_data)
        tag_type_str = "^%s$" % (matching_str)
        label_phone = lookup_data[lookup_data.tag_type.str.contains(tag_type_str)].phone

        # 招聘猎头号码数量数
        aeavy_number_lable = 0
        if len(label_phone) != 0:
            for phone in label_phone:
                phone_count = source_data[source_data.str.contains(r"^%s$" % phone)].count()
                aeavy_number_lable += phone_count
        # 招聘猎头去重号码数量
        weight_loss_label = len(label_phone)

        # 招聘猎头号码数量占比
        aeavy_number_proportion = "%.4f" % (aeavy_number_lable / source_len)
        # 招聘猎头电话去重号码数占比
        weight_loss_proportion = "%.4f" % (weight_loss_label / len(set(source_data)))

        # 按号码数量统计招聘猎头电话被标记次数
        aeavy_number_sign = 0
        try:
            if len(label_phone) != 0:
                for phone in label_phone:
                    phone_count = source_data[source_data.str.contains(r"^%s$" % phone)].count()
                    sign_info = lookup_data[lookup_data.phone.str.contains(r"^%s$" % phone)].other_info
                    if len(sign_info) == 0:
                        continue
                    sign_sum = 0
                    for sign in sign_info:
                        try:
                            sign = json.loads(sign)
                            sign_total = int(sign.get(matching_str, 0))
                        except Exception as e:
                            # logger.info("json resolve error, %s" % e)
                            sign_total = 0
                        sign_sum += sign_total
                    aeavy_number_sign += sign_sum * phone_count
        except KeyError as error:
            logger.info("number_label: aeavy_number_sign:%s" % error)
            print(error)
        # 按去重号码数统计招聘猎头电话被标记次数
        weight_loss_sign = 0
        try:
            if len(label_phone) != 0:
                for phone in label_phone:
                    sign_info = lookup_data[lookup_data.phone.str.contains(r"^%s$" % phone)].other_info
                    if len(sign_info) == 0:
                        continue
                    sign_sum = 0
                    for sign in sign_info:
                        try:
                            sign = json.loads(sign)
                            sign_total = int(sign.get(matching_str, 0))
                        except Exception as e:
                            # logger.info("json resolve error, %s" % e)
                            sign_total = 0
                        sign_sum += sign_total
                    weight_loss_sign += sign_sum
        except KeyError as error:
            logger.info("number_label: weight_loss_sign_error:%s" % error)
            print(error)
        dict_data["aeavy_number_lable"] = str(aeavy_number_lable)
        dict_data["weight_loss_label"] = str(weight_loss_label)
        dict_data["aeavy_number_proportion"] = str(aeavy_number_proportion)
        dict_data["weight_loss_proportion"] = str(weight_loss_proportion)
        dict_data["aeavy_number_sign"] = str(aeavy_number_sign)
        dict_data["weight_loss_sign"] = str(weight_loss_sign)
        # logger.info("number_label: detail_dict_data:%s" % json.dumps(dict_data))
        return json.dumps(dict_data)


