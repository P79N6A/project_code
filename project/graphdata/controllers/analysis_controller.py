# -*- coding: utf-8 -*-
'''
@author: luchao
'''
from flask import request
from util.restplus import api
from flask_restplus import reqparse,fields

from .base_controller import BaseController
from service import PodloanAnalysis
from lib.logger import logger
from model.antifraud import AfDbAgent
from util.http_util import render_ok, render_error

ns = api.namespace('analysis', description='数据分析')

analysisModel = ns.model('analysis', {
    'request_id': fields.String(requried=True, description=''),
    'user_id': fields.String(required=True, description=''),
    'loan_id': fields.String(required=True, description=''),
    'identity': fields.String(required=True, description=''),
    'phone': fields.String(required=True, description=''),
    'aid': fields.String(required=True, description=''),
    'operator': fields.String(required=True, description=''),
    'relation': fields.String(required=True, description=''),
    'address': fields.String(required=True, description=''),
    'sign': fields.String(required=True, description=''),
})
@ns.route('')
class analysisController(BaseController):


    @ns.expect(analysisModel)
    def post(self):
        """
        分析数据
        """
        validate_res,request_args = self.validate_sign()
        if not validate_res :
            return render_error(code=1, msg='验证签名失败')
        # 1. 分析后的数据
        obj = PodloanAnalysis(request_args)
        dict_data = obj.run()
        
        # 2. 保存到数据库中
        oAfDbAgent = AfDbAgent()
        res = oAfDbAgent.import_db(dict_data)
        logger.info("request_id:%s aid:%s import db result %s" % (obj.request_id,obj.aid,res)) 
        data = {
            'request_id':obj.request_id,
            'base_id':obj.base_id,
            'aid':obj.aid,
        }
        resp = render_ok(data)
        return resp
        
