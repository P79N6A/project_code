# -*- coding: utf-8 -*-
from model.base_model import DictMerge
import json
import numpy as np


class AfCompluteRule(object):

    '''
    根据规则计划
    '''

    def __init__(self, dict_data):
        self.dict_data = dict_data
        self.__dict_compute = {}

    def __getFlatten(self):
        # 扁平化处理
        oDM = DictMerge()
        for k in self.dict_data:
            oDM.set(self.dict_data[k])
        dict_data = oDM.get()
        return dict_data

    def __getDecisionRule(self):
        # 决策变量, 命中即判断为欺诈
        set_rules = [
            ('contract_exists', '=', 0),
            ('com_valid_mobile', '<', 3),
            ('addr_count', '<', 10),
            ('vs_phone_match', '<', 10),
            ('report_use_time', '<', 5),
            ('report_aomen', '=', 1),
            ('report_court', '=', 1),
            ('report_fcblack', '=', 1),
            ('report_shutdown', '>', 30),
        ]
        return set_rules

    def __getExtraRule(self):
        # 仅用来显示不用于决策
        extra_rules = [
            'com_r_rank',
            'com_c_total',
            'addr_has_black',
            'report_night_percent',
            'report_loan_connect',
            'report_110',
            'report_120',
            'report_lawyer',
            'report_court',
            'com_hours_connect',
            'com_c_total_mavg',
            'com_r_total_mavg',
            'com_valid_all',
            'vs_valid_match',
        ]
        return extra_rules

    def __getContractExist(self, dict_data):
        '''
        亲属与常用联系人分析是否均不存在于通讯录和聚信立中
        1 亲属联系人与常用联系人是否都存在于通讯录中
        2 亲属联系人与常用联系人是否都存在于通话记录中
        '''
        com_r_total = dict_data.get('com_r_total', 0)
        addr_relative_count = dict_data.get('addr_relative_count', 0)
        addr_contacts_count = dict_data.get('addr_contacts_count', 0)
        com_c_total = dict_data.get('com_c_total', 0)

        if com_r_total > 0 or addr_relative_count > 0 or addr_contacts_count > 0 or com_c_total > 0:
            contract_exists = 1
        else:
            contract_exists = 0

        return contract_exists

    def __compare_value(self, v1, op, v2):
        ''' 数据比较 '''
        if op == '>=':
            return 1 if v1 >= v2 else 0
        elif op == '<=':
            return 1 if v1 <= v2 else 0
        elif op == '>':
            return 1 if v1 > v2 else 0
        elif op == '<':
            return 1 if v1 < v2 else 0
        else:
            return 1 if v1 == v2 else 0

    def __getDecision(self, dict_all):
        #1. 命中规则
        dict_dicision = {}
        hit = []
        dicision_rules = self.__getDecisionRule()
        for name, op, value2 in dicision_rules:
            # 设置默认值为0
            v1 = 0 if dict_all.get(name) is None else dict_all.get(name, 0)
            dict_dicision[name] = v1

            # 运行规则
            r = self.__compare_value(v1, op, value2)
            if r == 1:
                hit.append(name)

        #2 当通讯录为空时, 下面两条不做欺诈处理
        if dict_dicision.get('addr_count', 0) == 0:
            if 'vs_phone_match' in hit:
                hit.remove('vs_phone_match')
            if 'addr_count' in hit:
                hit.remove('addr_count')

        #3 报告全为0时, 定为无报告
        report_list = [
            'report_use_time', 
            'report_aomen', 
            'report_court',
            'report_fcblack',
            'report_shutdown',
        ]
        no_report = all(dict_dicision.get(report_name, 0) in (None,0)  for report_name in report_list)
        if no_report:
            for report_name in report_list:
                if  report_name in hit:
                    hit.remove(report_name)

        return dict_dicision, hit

    def __getExtra(self, dict_all):
        extra_rules = self.__getExtraRule()
        dict_extra = dict([(name, dict_all.get(name, 0)) for name in extra_rules])
        return dict_extra

    def __runRules(self, dict_all):
        # 1. 获取决策规则
        dict_dicision, hit = self.__getDecision(dict_all)

        # 2 是否欺诈, 1是 2不是
        result_status = 1 if len(hit) > 0 else 2

        # 3. 获取展示规则:
        dict_extra = self.__getExtra(dict_all)

        # 4 numpy int64 ->int 
        # print(dict_dicision)
        # dict_dicision = {int(value) for key,value in dict_dicision.items()}
        # print(dict_dicision)
        # dict_extra = {int(value2) for key1,value2 in dict_extra.items()}
        # 5 组合条件
        dict_res = {
            'dicision': dict_dicision,
            'hit': hit,
            'extra': dict_extra,
        }
        print(dict_res)
        # 5. 返回是否欺诈和参数
        return result_status, dict_res

    def compute(self):
        '''计算规则'''
        # 1. 扁平化
        dict_all = self.__getFlatten()
        dict_all['contract_exists'] = self.__getContractExist(dict_all)
        result_status, dict_res = self.__runRules(dict_all)
        json_res = json.dumps(dict_res,default=json_numpy_serializer)

        # 2. 整合成一个数组
        res = {}
        res['user_id'] = dict_all.get('user_id')
        res['request_id'] = dict_all.get('request_id')
        res['result_status'] = result_status
        res['result_subject'] = json_res
        return res


def json_numpy_serializer(o):
    """ Serialize numpy types for json
    """
    numpy_types = (
        np.bool_,
        # np.bytes_, -- python `bytes` class is not json serializable     
        # np.complex64,  -- python `complex` class is not json serializable  
        # np.complex128,  -- python `complex` class is not json serializable
        # np.complex256,  -- special handling below
        # np.datetime64,  -- python `datetime.datetime` class is not json serializable
        np.float16,
        np.float32,
        np.float64,
        # np.float128,  -- special handling below
        np.int8,
        np.int16,
        np.int32,
        np.int64,
        # np.object_  -- should already be evaluated as python native
        np.str_,
        np.timedelta64,
        np.uint8,
        np.uint16,
        np.uint32,
        np.uint64,
        np.void,
    )
    if isinstance(o, np.ndarray):
        return o.tolist()
    elif isinstance(o, numpy_types):        
        return o.item()
    elif isinstance(o, np.float):
        return o.astype(np.float).item()
    # elif isinstance(o, np.complex256): -- no python native for np.complex256
    #     return o.astype(np.complex128).item() -- python `complex` class is not json serializable 
    else:
        raise TypeError("{} of type {} is not JSON serializable".format(repr(o), type(o)))