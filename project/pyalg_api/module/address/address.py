# -*- coding: utf-8 -*-
'''
通讯录分析报告
'''
import re
import pandas as pd


class Address(object):

    def __init__(self, db_address):
        self.pd_address = None
        if len(db_address) == 0:
            # logger here
            raise Exception(1000, "address can't analysis")

        self.pd_address = self.db2pandas(db_address)

    def db2pandas(self, db_address):
        # 切换为pandas格式
        data = [(d['name'], d['phone']) for d in db_address]
        pd_address = pd.DataFrame(data=data, columns=['name', 'phone'])
        return pd_address

    def run(self):
        addr_names = self.pd_address.name
        # 20 通讯录总量
        addr_count = addr_names.count()

        addr_phones = self.pd_address.phone

        # 23 通讯录中电话号码重复
        # 去重后手机号数量
        addr_phones_nodups = addr_phones.drop_duplicates().count()
        # 重复的数量
        addr_phones_dups = addr_count - addr_phones_nodups

        # 24 催收字样相关联系人过多;
        addr_collection_count = getMatchTotal(addr_names, '催收')

        # 25 贷款字样相关联系人过多;
        addr_loan_count = getMatchTotal(addr_names, '贷款')

        # 26 赌博字样相关号码数量过多
        addr_gamble_count = getMatchTotal(addr_names, '赌博')

        # 28 通讯录没有“爸”字样
        # 29 通讯录有“爸”字样次数过高
        addr_father_count = addr_names[addr_names.isin(['爸爸', '老爸', '爸'])].count()

        # 30通讯录没有“妈”字样
        # 31通讯录有“妈”字样次数过高
        addr_mother_count = addr_names[addr_names.isin(['妈妈', '老妈', '妈'])].count()

        # 21 通讯录中多个命名为妈，爸等亲属联系方式
        addr_parents_count = addr_father_count + addr_mother_count

        # 32 通讯录中无“同事”字样
        addr_colleague_count = getMatchTotal(addr_names, '同事')
        # 33 通讯录中无“公司”字样
        addr_company_count = getMatchTotal(addr_names, '公司')

        # 42 通讯录中固定电话个数过低
        is_tel = '^0\d{2,3}\d{7,8}$|^\d{7,8}$|^400'
        addr_tel_count = getMatchTotal(addr_phones, is_tel)

        return {
            # 20 通讯录总量
            'addr_count': int(addr_count),

            # 21 通讯录中多个命名为妈，爸等亲属联系方式
            'addr_parents_count': int(addr_parents_count),

            # 去重后手机号数量
            'addr_phones_nodups': int(addr_phones_nodups),

            # 23 通讯录中电话号码重复
            'addr_phones_dups': int(addr_phones_dups),

            # 27 通讯录去重数与通讯录个数占比
            #'addr_dups_percent': addr_dups_percent,

            # 24 催收字样相关联系人过多;
            'addr_collection_count': int(addr_collection_count),

            # 25 贷款字样相关联系人过多;
            'addr_loan_count': int(addr_loan_count),

            # 26 赌博字样相关号码数量过多
            'addr_gamble_count': int(addr_gamble_count),

            # 28 通讯录没有“爸”字样
            # 29 通讯录有“爸”字样次数过高
            'addr_father_count': int(addr_father_count),

            # 30通讯录没有“妈”字样
            # 31通讯录有“妈”字样次数过高
            'addr_mother_count': int(addr_mother_count),

            # 32 通讯录中无“同事”字样
            'addr_colleague_count': int(addr_colleague_count),

            # 33 通讯录中无“公司”字样
            'addr_company_count': int(addr_company_count),

            # 42 通讯录中固定电话个数过低
            'addr_tel_count': int(addr_tel_count),
        }

    def vsContact(self, contact):
        # 22 通讯录中亲属联系方式与系统中重复
        addr_relative_count = addr_contacts_count = 0
        if contact is not None:
            if 'phone' in contact.keys():
                addr_relative_count = self.pd_address.phone[self.pd_address.phone == contact['phone']].count()
            if 'mobile' in contact.keys():
                addr_contacts_count = self.pd_address.phone[self.pd_address.phone == contact['mobile']].count()

        return {
            # 亲属
            'addr_relative_count': int(addr_relative_count),
            # 常见
            'addr_contacts_count': int(addr_contacts_count),
        }

    def vsUser(self, user_phone):
        # 37 本人手机号出现在通讯录中
        ''' 我的手机号 '''
        addr_myphone_count = 0
        if user_phone is not None:
            addr_myphone_count = self.pd_address.phone[self.pd_address.phone == user_phone].count()
        return {
            'addr_myphone_count': int(addr_myphone_count),
        }

    def _getReMobile(self):
        # 手机正则
        substr = '^1[2-9][0-9]\d{8}$'
        p = re.compile(substr, re.DOTALL)
        return p

    def getDistinctPhone(self):
        pd_address = self.pd_address
        phone_match = pd_address.phone.drop_duplicates()
        mobile_pattern = self._getReMobile()
        phone = phone_match.str[-11:]
        is_mobile = phone.str.contains(mobile_pattern)
        distinct_phone = phone[is_mobile]
        return list(distinct_phone)


def getMatchTotal(series_data, substr):
    # 计算序列中的子字符串匹配数量
    p = re.compile(substr, re.DOTALL)
    matches = series_data.str.contains(p)
    return matches[matches].count()
