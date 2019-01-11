# -*- coding: utf-8 -*-
'''
学信、社保、公积金需求
'''
import re
import pandas as pd
from util.custom_function import getReportByUrl
from lib.logger import logger
from model.open.gather_result import GatherResult

class thirdPartyApi(object):

    def __init__(self, userInfo):
        self.aid = userInfo.get("aid", 0)
        self.user_id = userInfo.get("user_id", 0)
        self.identity_code = userInfo.get("identity_code", None)
        self.realname = userInfo.get("realname", None)
        self.other_info = {
            'learning_letter': 0,
            'learning_letter_contrast': '',
            'ocial_security': 0,
            'ocial_security_contrast': '',
            'accumulation_fund': 0,
            'accumulation_fund_contrast': '',
            'max_account_detail_balance': '-111',
        }

    def run(self):
        if self.user_id == 0 or self.identity_code is None or self.realname is None:
            return self.other_info
        self.__setOtherInfo()
        return self.other_info

    def __setOtherInfo(self):
        try:
            gather_result = GatherResult()
            # 1[学信]
            learning_letter_url = gather_result.getOtherDataUrl(self.aid, self.user_id, 1)
            self.__setLearningLetterInfo(learning_letter_url)

            # 2[社保]
            ocial_security_url = gather_result.getOtherDataUrl(self.aid, self.user_id, 2)
            self.__setOcialSecurity(ocial_security_url)

            # 3[公积金]
            accumulation_fund_url = gather_result.getOtherDataUrl(self.aid, self.user_id, 3)
            self.__setAccumulationFund(accumulation_fund_url)

            # 7[银行流水]
            business_water_url = gather_result.getOtherDataUrl(self.aid, self.user_id, 7)
            self.__setBusinessWater(business_water_url)

            return True
        except Exception as e:
            logger.error('第三方信息问题：%s' % e)
            return False

    # 读取学信信息
    def __setLearningLetterInfo(self, url):
        if url is None:
            return False

        self.other_info['learning_letter'] = 1
        data = self.__getJsonDataByUrl(url)
        if data is None:
            return False

        try:
            data_info = {"identity_code": "", "realname": ""}
            data_info['identity_code'] = data.get("data").get("task_data").get("school_info")[0].get("card_id") #身份证
            data_info['realname'] = data.get('data').get('task_data').get('school_info')[0].get('realname') #姓名
            self.other_info['learning_letter_contrast'] = self.__verificationIdentity(data_info)
            return True
        except Exception as e:
            logger.info("学信信息获取失败: %s" % e)
            return False

    # 读取社保信息
    def __setOcialSecurity(self, url):
        if url is None:
            return False

        self.other_info['ocial_security'] = 1
        data = self.__getJsonDataByUrl(url)
        if data is None:
            return False

        try:
            data_info = {"identity_code": "", "realname":""}
            data_info['identity_code'] = data.get("data").get("task_data").get("user_info").get("certificate_number")  # 身份证
            data_info['realname'] = data.get('data').get('task_data').get('user_info').get('name')  # 姓名
            self.other_info['ocial_security_contrast'] = self.__verificationIdentity(data_info)
            return True
        except Exception as e:
            logger.info("社保信息获取失败: %s" % e)
            return False

    # 读取公积金信息
    def __setAccumulationFund(self, url):
        if url is None:
            return False

        self.other_info['accumulation_fund'] = 1
        data = self.__getJsonDataByUrl(url)
        if data is None:
            return False

        try:
            data_info = {"identity_code": "", "realname": ""}
            data_info["identity_code"] = data.get("data").get("task_data").get("base_info").get("cert_no")  # 身份证
            data_info["realname"] = data.get("data").get("task_data").get("base_info").get("name")  # 姓名
            self.other_info['accumulation_fund_contrast'] = self.__verificationIdentity(data_info)
            return True
        except Exception as e:
            logger.info("公积金信息错误:%s" % e)
            return False

    # 读取银行流水信息
    def __setBusinessWater(self, url):
        if url is None:
            return self.other_info['max_account_detail_balance']

        self.other_info['max_account_detail_balance'] = "-999"
        data = self.__getJsonDataByUrl(url)
        if data is None:
            return self.other_info['max_account_detail_balance']

        try:
            card_list = data.get("data").get("task_data").get("debit_card_accounts")
            if len(card_list) == 0:
                return self.other_info['max_account_detail_balance']
        except Exception as e:
            logger.info("银行卡列表数据异常:%s" % e)
            return self.other_info['max_account_detail_balance']

        account_total_list = []
        for card_info in card_list:
            try:
                account_detail_list = card_info.get("sub_accounts")[0].get("account_detail")
                if len(account_detail_list) == 0:
                    continue
                account_total_list += account_detail_list
            except Exception as e:
                logger.info("该银行卡中交易数据异常:%s" % e)
                continue

        if len(account_total_list) == 0:
            return self.other_info['max_account_detail_balance']
        try:
            account_detail_pd = pd.DataFrame(account_total_list, columns = ['balance', 'currency', 'trade_date'])
            account_detail_pd = account_detail_pd[(-account_detail_pd.trade_date.isin(['未知']))]
            account_detail_pd.loc[account_detail_pd.currency.isin(["港币", "美元", "印尼盾"]), 'balance'] = "0"
            account_detail_pd[['balance']] = account_detail_pd[['balance']].apply(pd.to_numeric)
            account_detail_pd = account_detail_pd.dropna(subset=["trade_date", "balance"])
            account_detail_pd.sort_values(by=['trade_date','balance'],ascending=[0,0],inplace=True)
            self.other_info['max_account_detail_balance'] = str(account_detail_pd.iloc[0]['balance'])
            return self.other_info['max_account_detail_balance']
        except Exception as e:
            logger.info("pandas操作数据异常:%s" % e)
            return self.other_info['max_account_detail_balance']

    # 比对用户身份信息和报告数据信息
    def __verificationIdentity(self, data_info):
        if not data_info:
            return "nodata"
        # 第三方数据身份证信息
        other_identity = data_info.get("identity_code")
        other_realname = data_info.get("realname")
        #如果是NoneType就设置为空
        if other_identity is None:
            other_identity = ""
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
            elif other_realname == self.realname:
                sign_gjj = "idnull_namesame"
            else:
                sign_gjj = "idnull_namedifferent"
        # 如果身份证存在*号
        elif re.search(r'\*', other_identity):
            if (other_realname is None) or (other_realname == '') or re.search(r'未知', other_realname):
                sign_gjj = "id*_namenull"
            elif re.search(r'\*', other_realname):
                sign_gjj = "id*_name*"
            elif other_realname == self.realname:
                sign_gjj = "id*_namesame"
            else:
                sign_gjj = "id*_namedifferent"
        # 身份证相等
        elif other_identity == self.identity_code:
            sign_gjj = "idsame_"
        #身份证不相等
        elif other_identity != self.identity_code:
            sign_gjj = "iddifferent_"
        else:
            sign_gjj = "nodata"
        return sign_gjj

    def __getJsonDataByUrl(self, url):
        if url is None:
            return None
        try:
            data = getReportByUrl(url)
            # logger.error(json.dumps(data))
        except Exception as e:
            logger.info("json数据获取失败: %s" % e)
            return None
        return data
