# -*- coding: utf-8 -*-


from util.restplus import api

from .base_controller import BaseController
from util.http_util import render_ok, render_error

#工具包
from sklearn.externals import joblib
import os
import pandas as pd

bs = api.namespace('xgboost', description=' xgboost模型')

@bs.route('')
class xgboostController(BaseController):
    def post(self):
        # x_test = {
        #     'PROME_V4_SCORE':1,
        #     'multi_p2p_p_class_7':1,
        #     'loan_all':1,
        #     'history_bad_status':1,
        #     'addr_phones_nodups':1,
        #     'addr_collection_count':1,
        #     'addr_tel_count':1,
        #     'com_r_duration_mavg':1,
        #     'com_c_total_mavg':1,
        #     'com_use_time':1,
        #     'com_count':1,
        #     'com_month_answer_duration':1,
        #     'com_mobile_people':1,
        #     'com_night_duration_mavg':1,
        #     'com_max_tel_connect':1,
        #     'vs_duration_match':1,
        #     'same_phone_num':1,
        #     'shutdown_max_days':1,
        #     'advertis_weight_loss_p':1,
        #     'express_aeavy_number_p':1,
        #     'harass_weight_loss_p':1,
        #     'house_agent_aeavy_number_lable':1,
        #     'cheat_aeavy_number_sign':1,
        #     'taxi_aeavy_number_sign':1,
        #     'ring_weight_loss_sign':1
        # }
        validate_res, request_args = self.validate_xgboostr_sign()
        if not validate_res:
            return render_error(code=1, msg='验证签名失败')

        #将字典的值转换成整数
        new_request_args = {}
        for key,value in request_args.items():
            new_request_args[key] = float(value)
        #logger.info("aa---\n%s" % new_request_args)

        #读取model文件进行预测
        #path_file = os.getcwd()+"\\model\\xgboost_v_0_1_n.model"
        #path_file = os.getcwd() + "/model/xgboost_v_0_1_ok.model"
        path_file = os.getcwd() + "/model/xgboost_v_0_1_t7.model"
        #logger.info("path_file---\n%s" % path_file)
        pmml_model = joblib.load(path_file)

        #将字典转换成数据框
        x_test = pd.DataFrame.from_dict(new_request_args, orient='index').T
        #logger.info("aa---\n%s" % x_test)

        classes = pmml_model.predict_proba(x_test)
        #将numpy.ndarray转list
        classes = classes.tolist()
        #logger.info("aaa---\n%s", classes)
        
        resp = render_ok(classes)
        return resp