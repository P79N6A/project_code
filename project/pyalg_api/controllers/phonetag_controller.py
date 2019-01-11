# -*- coding: utf-8 -*-


from util.restplus import api
from lib.logger import logger
from .base_controller import BaseController
from util.http_util import render_ok, render_error

#工具包
from sklearn.externals import joblib
import os
import pandas as pd

phonetag = api.namespace('phonetag', description='号码标签模型')

@phonetag.route('')
class phonetagController(BaseController):
    
    def post(self):
        try:
            validate_res, request_args = self.validate_phonetag_sign()
            if not validate_res:
                return render_error(code=1, msg='验证签名失败')
        except Exception as e:
            logger.error('phonetag request is fail : %s' % e)
            return render_error(code=1, msg='验证签名失败')

        try:
            # 将字典转换成数据帧
            x_test = pd.DataFrame.from_dict(request_args, orient='index').T
            # 读取model文件进行过滤
            path_file_imp = os.getcwd() + "/model/phonetag_imp18122417.model"
            imp_model = joblib.load(path_file_imp)
            imp_data = imp_model.transform(x_test)
            # 读取model文件进行预测
            path_file_clf = os.getcwd() + "/model/phonetag_clf18122417.model"
            clf_model = joblib.load(path_file_clf)
            clf_data = clf_model.predict_proba(imp_data)
            # 将numpy.ndarray转list
            score = clf_data.tolist()
            return render_ok(score)
        except Exception as e:
            logger.error('phonetag request is fail : %s' % e)
            return render_error(code=2, msg='数据格式错误')
