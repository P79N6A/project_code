# -*- coding: utf-8 -*-

from util.restplus import api
from util.http_util import render_ok, render_error

from .base_controller import BaseController

# 工具包
from sklearn.externals import joblib
import os
import pandas as pd

reloan = api.namespace('reloanxg', description='复贷xgboost')


@reloan.route("")
class ReloanXgController(BaseController):

    def post(self):
        validate_res, request_args = self.validate_reloanxg_sign()
        if not validate_res:
            return render_error(code=1, msg='验证签名失败')

        # 将字典的值转换成整数
        new_request_args = {}
        for key, value in request_args.items():
            new_request_args[key] = float(value)
        # logger.info("aa---\n%s" % new_request_args)

        # 读取model文件进行预测
        path_file = os.getcwd() + "/model/xgboost_fd_ending6.model"
        # logger.info("path_file---\n%s" % path_file)
        pmml_model = joblib.load(path_file)

        # 将字典转换成数据框
        x_test = pd.DataFrame.from_dict(new_request_args, orient='index').T
        # logger.info("aa---\n%s" % x_test)

        classes = pmml_model.predict_proba(x_test)
        # 将numpy.ndarray转list
        classes = classes.tolist()
        # logger.info("bbbbbb---\n%s", classes)

        resp = render_ok(classes)
        return resp
