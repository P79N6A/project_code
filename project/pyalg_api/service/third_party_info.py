# -*- coding: utf-8 -*-
'''
学信、社保、公积金需求
'''
import json
import re
import socket
import urllib.error
import urllib.parse
import urllib.request

from lib.logger import logger
from model.antifraud.af_other_data import AfOtherData
from model.open.gather_result import GatherResult
from datetime import datetime

class thirdPartyInfo(object):

    def runData(self,userInfo):
        result = self.getOtherInfo(userInfo)
        if result:
            # 保存数据
            self._save_data(userInfo, result)


    def getOtherInfo(self, userInfo):
        try:
            if userInfo is None:
                return False
            user_id = userInfo.user_id #用户信息
            #user_id = "79770325"
            other_info = {}
            gather_result = GatherResult()
            # 1[学信]
            learning_letter = gather_result.getOtherData(user_id, 1)
            learning_letter_identity = self.getLearningLetter(learning_letter)
            learning_letter_field = self.infoCcontrast(learning_letter, userInfo, learning_letter_identity)
            other_info["learning_letter_field"] = learning_letter_field

            # 2[社保]
            social_security = gather_result.getOtherData(user_id, 2)
            social_security_identity = self.getSocialSecurity(social_security)
            social_security_field = self.infoCcontrast(social_security, userInfo, social_security_identity)
            other_info["social_security_field"] = social_security_field

            # 3[公积金]
            accumulation_fund = gather_result.getOtherData(user_id, 3)
            accumulation_fund_identity = self.getAccumulationFund(accumulation_fund)
            accumulation_fund_field = self.infoCcontrast(accumulation_fund, userInfo, accumulation_fund_identity)
            other_info["accumulation_fund_field"] = accumulation_fund_field
            return other_info
        except Exception as e:
            logger.error('第三方信息问题：%s' % e)
            return False


    '''
    信息对比
    '''
    def infoCcontrast(self, send_data, user_info, data_info):
        # 提交：返回1。没有提交 返回0
        return_data_info = {}
        if not send_data:
            return_data_info["submission"] = 0
            return_data_info["contrast"] = "null"
        else:
            return_data_info["submission"] = 1
            return_data_info["contrast"]= self.verificationIdentity(user_info, data_info)
        return return_data_info

    def verificationIdentity(self,user_info, data_info):
        if not data_info:
            return "nodata"
        # 第三方数据身份证信息
        other_identity = data_info.get("identity_code")
        other_realname = data_info.get("realname")
        #如果是NoneType就设置为空
        if other_identity is None:
            other_identity = ""
        # 用户身份证信息
        user_identity = user_info.identity
        user_realname = user_info.realname
        # 判断身份证的长度
        identity_len = len(other_identity)
        # 合法身份证长度
        legitimate_len = (15, 18)
        if identity_len not in legitimate_len:
            other_identity = ""
        # 如果身份证为空或是不合法
        if (other_identity == "") or (re.search(r'未知', other_identity)) :
            if (other_realname is None) or (other_realname == '') or re.search(r'未知', other_realname):
                sign_gjj = "idnull_namenull"
            elif re.search(r'\*', other_realname):
                sign_gjj = "idnull_name*"
            elif user_realname == other_realname:
                sign_gjj = "idnull_namesame"
            else:
                sign_gjj = "idnull_namedifferent"
        # 如果身份证存在*号
        elif re.search(r'\*', other_identity):
            if (other_realname is None) or (other_realname == '') or re.search(r'未知', other_realname):
                sign_gjj = "id*_namenull"
            elif re.search(r'\*', other_realname):
                sign_gjj = "id*_name*"
            elif user_realname == other_realname:
                sign_gjj = "id*_namesame"
            else:
                sign_gjj = "id*_namedifferent"
        # 身份证相等
        elif other_identity == user_identity:
            sign_gjj = "idsame_"
        #身份证不相等
        elif other_identity != user_identity:
            sign_gjj = "iddifferent_"
        else:
            sign_gjj = "nodata"
        return sign_gjj

    '''
    公积金信息
    '''
    def getAccumulationFund(self, data_info):
        if data_info is None:
            return False

        domain = self._getDomain(data_info.create_time)
        report_url = domain + data_info.data_url
        #report_url = "http://open.xianhuahua.com//ofiles/openapi/sjmh/201808/27/572008.json"
        accumulation_fund = self._getByUrl2(report_url)
        if accumulation_fund is None:
            logger.error('公积金获取json失败-地址%s' % report_url)
            return False
        data = json.loads(accumulation_fund)
        data_info = {}
        try:
            identity_code = data.get("data").get("task_data").get("base_info").get("cert_no")  # 身份证
            data_info["identity_code"] = identity_code

            realname = data.get("data").get("task_data").get("base_info").get("name")  # 姓名
            data_info["realname"] = realname
            return data_info
        except Exception as e:
            logger.info("公积金信息错误:%s" % e)
            return data_info

    '''
    社保信息
    '''
    def getSocialSecurity(self, data_info):
        if data_info is None:
            return False

        domain = self._getDomain(data_info.create_time)
        report_url = domain + data_info.data_url
        #report_url = "http://open.xianhuahua.com/ofiles/openapi/sjmh/201808/27/571997.json"
        social_security = self._getByUrl2(report_url)
        if social_security is None:
            logger.error('社保获取json失败-地址%s' % report_url)
            return False
        data = json.loads(social_security)
        data_info = {}
        try:
            identity_code = data.get("data").get("task_data").get("user_info").get("certificate_number")  # 身份证
            data_info["identity_code"] = identity_code

            realname = data.get('data').get('task_data').get('user_info').get('name')  # 姓名
            data_info["realname"] = realname
            return data_info
        except Exception as e:
            logger.info("社保信息错误:%s" % e)
            return data_info


    '''
    获取学信信息
    '''
    def getLearningLetter(self, data_info):
        if data_info is None:
            return False
        domain = self._getDomain(data_info.create_time)
        report_url = domain + data_info.data_url
        #report_url = "http://open.xianhuahua.com/ofiles/openapi/sjmh/201808/27/572007.json"
        learning_letter_data = self._getByUrl2(report_url)
        if learning_letter_data is None:
            logger.error('学信网获取json失败-地址%s' % report_url)
            return False
        data = json.loads(learning_letter_data)
        data_info = {}
        try:
            identity_code = data.get("data").get("task_data").get("school_info")[0].get("card_id") #身份证
            data_info["identity_code"] = identity_code
            #user_mobile = data.get('data').get('user_mobile')#手机号
            realname = data.get('data').get('task_data').get('school_info')[0].get('realname') #姓名
            data_info["realname"] = realname
            return data_info
        except Exception as e:
            logger.info("学信信息错误:%s" % e)
            return data_info


    '''
    保存记录
    '''
    def _save_data(self, user_info, other_data):
        if other_data is None:
            return False
        user_id = user_info.user_id
        save_data = {"user_id":user_id, "other_data":other_data}
        #计算xhh_open.xhh_gather_result表中有多少条记录
        ogather_result = GatherResult()
        ogaher_list = []
        # 1[学信]条数
        learning_count = ogather_result.getCount(user_id,1)
        ogaher_list.append(learning_count)
        # 2[社保]条数
        social_count = ogather_result.getCount(user_id,2)
        ogaher_list.append(social_count)
        # 3[公积金]条数
        accumulation_count = ogather_result.getCount(user_id,3)
        ogaher_list.append(accumulation_count)
        # 算出只又的一条
        #print(ogaher_list)
        max_gather_value = max(ogaher_list)
        #print(max_gather_value)

        #计算xhh_antifraud.af_other_data个中有多少条记录
        oaf_other_data = AfOtherData()
        other_count = oaf_other_data.getCount(user_id)
        if max_gather_value > other_count:
            state = oaf_other_data.saveResources(save_data)
        else:
            # 判断是否存在
            get_info = oaf_other_data.getDataForUserId(user_id)
            if get_info:
                state = oaf_other_data.updateResources(get_info, save_data)
            else:
                state = oaf_other_data.saveResources(save_data)
        return state

    '''
    返回url地址
    '''
    def _getDomain(self, create_time):
        '''
        获取域名
        @param  datetime $create_time 时间格式
        @return str 域名
        '''
        #@todo
        from lib.config import get_config
        cf = get_config()
        if cf.TESTING:
            return 'http://182.92.80.211:8091'

        now = datetime.now()
        ta = now - create_time
        if ta.days > 1:
            #domain = "http://124.193.149.180:8100"
            #domain = "http://123.207.141.180"
            #domain = "http://10.139.36.194"
            domain = "http://openapi.xianhuahua.com"
        else:
            domain = "http://openapi.xianhuahua.com"
        return domain

    '''
    下载数据
    '''
    def _getByUrl2(self, url):
        socket.setdefaulttimeout(25)
        try:
            response = urllib.request.urlopen(url)
            html = response.read()
        except Exception as e:
            logger.error('第三方数据：url get fail %s' % e)
            html = None
        return html