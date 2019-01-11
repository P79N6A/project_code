# -*- coding: utf-8 -*-
'''
@author: sunrui
'''
from util.restplus import api
from flask_restplus import fields
from .base_controller import BaseController
from service import APPLabel
from lib.logger import logger
from util.http_util import render_ok, render_error

applabel = api.namespace('applabel', description='App分类获取')

applabelModel = applabel.model('applabel', {
    'mobile': fields.String(required=True, description=''),
    'applist': fields.String(required=True, description=''),
    'time': fields.String(required=True, description=''),
    'sign': fields.String(required=True, description=''),
})
@applabel.route('')
class applabelController(BaseController):

    @applabel.expect(applabelModel)
    def post(self):
        """
        分析数据
        """
        validate_res,request_args = self.validate_applabel_sign()
        if not validate_res :
            return render_error(code=1, msg='验证签名失败')

        obj = APPLabel(request_args)
        result = obj.run()
        resCode = result.get('code',-100)
        resMsg = result.get('msg', '系统错误')
        if resCode == 0:
            return render_ok()
        return render_error(resCode, resMsg)
