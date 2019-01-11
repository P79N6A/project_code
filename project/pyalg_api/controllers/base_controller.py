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

from lib.logger import logger
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

    def validate_xgboostr_sign(self):
        self.reqparse = reqparse.RequestParser()
        self.reqparse.add_argument('PROME_V4_SCORE')
        self.reqparse.add_argument('multi_p2p_p_class_7')
        self.reqparse.add_argument('loan_all')
        self.reqparse.add_argument('history_bad_status')
        self.reqparse.add_argument('addr_phones_nodups')
        self.reqparse.add_argument('addr_collection_count')
        self.reqparse.add_argument('addr_tel_count')
        self.reqparse.add_argument('com_r_duration_mavg')
        self.reqparse.add_argument('com_c_total_mavg')
        self.reqparse.add_argument('com_use_time')
        self.reqparse.add_argument('com_count')
        self.reqparse.add_argument('com_month_answer_duration')
        self.reqparse.add_argument('com_mobile_people')
        self.reqparse.add_argument('com_night_duration_mavg')
        self.reqparse.add_argument('com_max_tel_connect')
        self.reqparse.add_argument('vs_duration_match')
        self.reqparse.add_argument('same_phone_num')
        self.reqparse.add_argument('shutdown_max_days')
        self.reqparse.add_argument('advertis_weight_loss_p')
        self.reqparse.add_argument('express_aeavy_number_p')
        self.reqparse.add_argument('harass_weight_loss_p')
        self.reqparse.add_argument('house_agent_aeavy_number_lable')
        self.reqparse.add_argument('cheat_aeavy_number_sign')
        self.reqparse.add_argument('taxi_aeavy_number_sign')
        self.reqparse.add_argument('ring_weight_loss_sign')
        self.reqparse.add_argument('sign')
        self.request_args = self.reqparse.parse_args()
        self.sign = self.request_args.pop('sign')
        generate_sign = createSignByMd5(self.request_args)
        #logger.info("aa---\n%s" % generate_sign)
        #print(generate_sign)
        if (generate_sign != self.sign):
            return False, self.request_args
        return True, self.request_args

    def operator_sign(self):
        self.reqparse = reqparse.RequestParser()
        self.reqparse.add_argument('credit_id', type=str)
        self.reqparse.add_argument('aid',type=str)
        self.reqparse.add_argument('contain', type=str)
        self.reqparse.add_argument('realname',type=str)
        self.reqparse.add_argument('phone',type=str)
        self.reqparse.add_argument('identity',type=str)
        self.reqparse.add_argument('contact', type=str)
        self.reqparse.add_argument('sign',type=str)
        self.request_args = self.reqparse.parse_args()
        self.sign = self.request_args.pop('sign')
        generate_sign = createSignByMd5(self.request_args)
        if(generate_sign != self.sign):
            return False,self.request_args
        return True,self.request_args


    def validate_reloanxg_sign(self):
        self.reqparse = reqparse.RequestParser()
        self.reqparse.add_argument('success_num')
        self.reqparse.add_argument('wst_dlq_sts')
        self.reqparse.add_argument('PROME_V4_SCORE')
        self.reqparse.add_argument('multi_all_p_class_30')
        self.reqparse.add_argument('multi_p2p_p_class_30')
        self.reqparse.add_argument('multi_small_p_class_30')
        self.reqparse.add_argument('user_total')
        self.reqparse.add_argument('realadl_tot_freject_num')
        self.reqparse.add_argument('addr_count')
        self.reqparse.add_argument('addr_tel_count')
        self.reqparse.add_argument('com_c_rank')
        self.reqparse.add_argument('com_month_num')
        self.reqparse.add_argument('com_call_duration')
        self.reqparse.add_argument('com_month_people')
        self.reqparse.add_argument('com_days_call')
        self.reqparse.add_argument('com_hours_answer_davg')
        self.reqparse.add_argument('com_offen_connect')
        self.reqparse.add_argument('com_valid_mobile')
        self.reqparse.add_argument('vs_duration_match')
        self.reqparse.add_argument('last3_answer')
        self.reqparse.add_argument('same_phone_num')
        self.reqparse.add_argument('phone_register_month')
        self.reqparse.add_argument('total_duration')
        self.reqparse.add_argument('tot_phone_num')
        self.reqparse.add_argument('shutdown_duration_count')
        self.reqparse.add_argument('shutdown_max_days')
        self.reqparse.add_argument('advertis_aeavy_number_p')
        self.reqparse.add_argument('advertis_weight_loss_label')
        self.reqparse.add_argument('express_weight_loss_label')
        self.reqparse.add_argument('express_weight_loss_p')
        self.reqparse.add_argument('express_weight_loss_sign')
        self.reqparse.add_argument('harass_aeavy_number_p')
        self.reqparse.add_argument('harass_weight_loss_label')
        self.reqparse.add_argument('harass_weight_loss_p')
        self.reqparse.add_argument('house_agent_weight_loss_p')
        self.reqparse.add_argument('cheat_aeavy_number_p')
        self.reqparse.add_argument('cheat_weight_loss_sign')
        self.reqparse.add_argument('company_tel_aeavy_number_p')
        self.reqparse.add_argument('taxi_weight_loss_label')
        self.reqparse.add_argument('taxi_weight_loss_p')
        self.reqparse.add_argument('insurance_aeavy_number_lable')
        self.reqparse.add_argument('insurance_aeavy_number_p')
        self.reqparse.add_argument('ring_aeavy_number_p')
        self.reqparse.add_argument('ring_weight_loss_sign')
        self.reqparse.add_argument('sign')
        self.request_args = self.reqparse.parse_args()
        self.sign = self.request_args.pop('sign')
        #logger.info("aaaaaa---\n%s" % self.request_args)
        generate_sign = createSignByMd5(self.request_args)
        # logger.info("aa---\n%s" % generate_sign)
        # print(generate_sign)
        if (generate_sign != self.sign):
            return False, self.request_args
        return True, self.request_args

    def validate_applabel_sign(self):
        self.reqparse = reqparse.RequestParser()
        self.reqparse.add_argument('mobile', type=str)
        self.reqparse.add_argument('applist', type=str)
        self.reqparse.add_argument('time', type=str)
        self.reqparse.add_argument('sign', type=str)
        self.request_args = self.reqparse.parse_args()
        self.sign = self.request_args.pop('sign')
        # logger.info("aaaaaa---\n%s" % self.request_args)
        generate_sign = createSignByMd5(self.request_args)
        # logger.info("aa---\n%s" % generate_sign)
        if (generate_sign != self.sign):
            return False, self.request_args
        return True, self.request_args

    def validate_phonetag_sign(self):
        self.reqparse = reqparse.RequestParser()
        self.reqparse.add_argument("taxi_weight_loss_sign")
        self.reqparse.add_argument("taxi_weight_loss_proportion")
        self.reqparse.add_argument("taxi_weight_loss_label")
        self.reqparse.add_argument("taxi_aeavy_number_sign")
        self.reqparse.add_argument("taxi_aeavy_number_proportion")
        self.reqparse.add_argument("taxi_aeavy_number_lable")
        self.reqparse.add_argument("ring_weight_loss_sign")
        self.reqparse.add_argument("ring_weight_loss_proportion")
        self.reqparse.add_argument("ring_weight_loss_label")
        self.reqparse.add_argument("ring_aeavy_number_sign")
        self.reqparse.add_argument("ring_aeavy_number_proportion")
        self.reqparse.add_argument("ring_aeavy_number_lable")
        self.reqparse.add_argument("house_agent_weight_loss_proportion")
        self.reqparse.add_argument("harass_weight_loss_proportion")
        self.reqparse.add_argument("harass_weight_loss_label")
        self.reqparse.add_argument("harass_aeavy_number_sign")
        self.reqparse.add_argument("harass_aeavy_number_proportion")
        self.reqparse.add_argument("harass_aeavy_number_lable")
        self.reqparse.add_argument("express_weight_loss_sign")
        self.reqparse.add_argument("express_weight_loss_proportion")
        self.reqparse.add_argument("express_weight_loss_label")
        self.reqparse.add_argument("express_aeavy_number_sign")
        self.reqparse.add_argument("express_aeavy_number_lable")
        self.reqparse.add_argument("cheat_weight_loss_proportion")
        self.reqparse.add_argument("cheat_weight_loss_label")
        self.reqparse.add_argument("cheat_aeavy_number_proportion")
        self.reqparse.add_argument("cheat_aeavy_number_lable")
        self.reqparse.add_argument("advertis_aeavy_number_sign")
        self.reqparse.add_argument('sign')
        self.request_args = self.reqparse.parse_args()
        self.sign = self.request_args.pop('sign')
        generate_sign = createSignByMd5(self.request_args)
        #logger.info("aa---\n%s" % generate_sign
        if (generate_sign != self.sign):
            return False, self.request_args
        return True, self.request_args