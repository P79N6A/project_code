# -*- coding: utf-8 -*-
from util.restplus import api
from flask_restplus import fields
from util.http_util import render_ok, render_error
from service.operator_logic import OperatorLogic

from .base_controller import BaseController

operator = api.namespace('operator', description='运营商报告分析')

operatorModel = operator.model('operatorModel', {
    'request_id': fields.String(requried=True, description='请求处理id'),
    'credit_id': fields.String(requried=True, description='平测id'),
    'identity': fields.String(required=True, description='身份证'),
    'realname': fields.String(required=True, description='真实姓名'),
    'contact': fields.String(required=True, description='联系人'),
    'phone': fields.String(required=True, description='手机号'),
    'aid': fields.String(required=True, description='项目类型'),
    'contain': fields.String(required=True, description='包含：1 一亿元， 2 一个亿， 4 七天乐普通， 8 七天乐商城'),
    'sign': fields.String(required=True, description=''),
})


@operator.route("")
class OperatorController(BaseController):

    def post(self):
        """
        分析数据
        """
        validate_res, request_args = self.operator_sign()
        if not validate_res:
            return render_error(code=1, msg='验证签名失败')

        # 1. 分析后的数据
        obj = OperatorLogic(request_args)
        dict_data = obj.run()
        if dict_data is None:
            return render_error(code=1001, msg='写入请求表失败')

        data = {
            'credit_id': obj.credit_id,
            'base_id': obj.base_id,
            'aid': obj.aid,
            'data': dict_data
        }
        resp = render_ok(data)
        return resp
