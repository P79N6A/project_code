# -*- coding: utf-8 -*-
'''
@author: luchao
'''
import json
import hashlib
from flask_restplus import Resource,reqparse
from flask.json import jsonify

from lib.config import get_config
from util.custom_function import createSignByMd5

class BaseController(Resource):

    def validate_sign(self):
        self.reqparse = reqparse.RequestParser()
        self.reqparse.add_argument('request_id',type=str)
        self.reqparse.add_argument('user_id',type=str)
        self.reqparse.add_argument('loan_id',type=str)
        self.reqparse.add_argument('identity',type=str)
        self.reqparse.add_argument('phone',type=str)
        self.reqparse.add_argument('aid',type=str)
        self.reqparse.add_argument('operator',type=str)
        self.reqparse.add_argument('relation',type=str)
        self.reqparse.add_argument('address',type=str)
        self.reqparse.add_argument('sign',type=str)
        self.request_args = self.reqparse.parse_args()
        self.sign = self.request_args.pop('sign')
        generate_sign = createSignByMd5(self.request_args)
        # print(generate_sign)
        if(generate_sign != self.sign):
            return False,self.request_args
        return True,self.request_args
    
