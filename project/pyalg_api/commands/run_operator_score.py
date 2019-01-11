import pandas as pd
import os 
import json

from lib.logger import logger
from util.custom_function import getReportByUrl
from .base_command import BaseCommand
from module.address import Address
from module.detail import Detail
from module.report import analysis_report
from model.analysis import AddressList
from module.yiyiyuan import YiFavoriteContact
import requests


'''
历史用户跑运营商模型需求
'''
class RunOperatorScore(BaseCommand):
    def __init__(self):
        super(RunOperatorScore, self).__init__()

    def runScore(self):
        # 处理pandas 加上详单地址 报告地址 来源
        path = os.getcwd()
        file_path = path +'/commands/data/operator.xlsx'
        pd_data = pd.read_excel(file_path)
        domain = "http://10.139.36.194"
        # domain = "http://182.92.80.211:8091"
        pd_data['report_url'] = domain + pd_data.url
        pd_data['detail_url'] = pd_data['report_url'].str.replace(".json", "_detail.json")
        pd_data['operator_score'] = pd_data.apply(getScore,axis=1)
        result_file = path + '/commands/data/result.csv'
        pd_data.to_csv(result_file)


def getScore(line_data):
    phone = line_data.get('mobile','')
    phone = str(phone)
    detail_url = line_data.get('detail_url','')
    report_url = line_data.get('report_url','')
    source = line_data.get('source',0)
    user_id = line_data.get('user_id',0)
    contact = YiFavoriteContact().getByUserId(user_id)
    if contact is not None:
        print('phone : %s  mobile : %s' %(contact.phone,contact.mobile))
    addrList = AddressList().getByUserPhoneDict(phone)
    print('addrlist len %d' % len(addrList))
    address_data = analysis_address(addrList)
    detail_data,detail_vscontact = analysis_detail(detail_url,contact)
    report_data = analysis_operator_report(source,report_url)
    req_data = {
        'addr_tel_count': address_data.get('addr_tel_count',''),
        'com_r_total_mavg': detail_vscontact.get('com_r_total_mavg',''),
        'com_c_duration': detail_vscontact.get('com_c_duration',''),
        'com_c_duration_rank': detail_vscontact.get('com_c_duration_rank',''),
        'com_answer_duration': detail_data.get('com_answer_duration',''),
        'com_days_answer': detail_data.get('com_days_answer',''),
        'sms_phone_count_nodup': report_data.get('sms_phone_count_nodup',''),
        'call_count_call_time_5min10min':report_data.get('call_count_call_time_5min10min',''),
        'call_duration_holiday_3month':report_data.get('call_duration_holiday_3month_t_7',''),
        'call_duration_workday_3month':report_data.get('call_duration_workday_3month_t_7','')
    }
    req_data_json = json.dumps(req_data)
    print('phone: %s ,req_data: %s' %(phone,req_data_json))
    headers = {'Content-Type': 'application/json'}
    # r = requests.post('http://127.0.0.1:8089/api/xgboost/predict',headers=headers,data=req_data_json)
    r = requests.post('http://10.139.52.241:8089/api/xgboost/predict',headers=headers,data=req_data_json)
    resp_info = json.loads(r.text)
    req_code = resp_info.get('code')
    predict_result = None
    if req_code == '0000':
        req_data = resp_info.get('data')
        dict_req_data = json.loads(req_data)
        predict_result = dict_req_data.get('predict_result')
    return predict_result

    
def analysis_address(address_list):
    address_data = {}
    try:
        oAddress = Address(address_list)
        address_data = oAddress.run()
    except Exception as e:
        logger.error("analysis address is fail: %s" % (e))
    return address_data


def analysis_detail(detail_url,contact):
    detail_data = {}
    detail_other_data = {}
    detail_vscontact = {}
    try:
        json_data = getReportByUrl(detail_url)
        if json_data:
            detail_phone = json_data
            oDetail = Detail(json_data)
            detail_data,detail_other_data = oDetail.run()
            if contact is not None:
                dict_contact = {}
                dict_contact['phone'] = contact.phone
                dict_contact['mobile'] = contact.mobile
            detail_vscontact = oDetail.vsContact(dict_contact)
    except Exception as e:
        logger.error("detail_url:%s detail is fail: %s" % (detail_url, e))    
    return detail_data,detail_vscontact


def analysis_operator_report(source,report_url):
    report_data = {}
    try:
        report_data = analysis_report(source,report_url)
    except Exception as e:
        logger.error("report_url:%s report is fail: %s" % (report_url, e))
    return report_data

