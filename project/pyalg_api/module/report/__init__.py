from .report import Report
from .reportbr import Reportbr
from .reportss import Reportss
from .reportmh import Reportmh
from lib.logger import logger
from util.custom_function import getReportByUrl
import time

def analysis_report(source,report_url):
    if source in [1,2]:
        res = __report(report_url)
    elif source == 4 :
        res = __reportss(report_url)
    elif source == 5 :
        res = __reportbr(report_url)
    elif source == 6 :
        res = __reportmh(report_url)
    return res

def __report(url):
    '''获取聚信立报告处理对象'''
    report_data = {}
    try:
        json_data = getReportByUrl(url)
        oReport = Report(json_data)
        report_data = oReport.run()
    except Exception as e:
        logger.error("url:%s jxl report is fail: %s" % (url,e))
    return report_data

def __reportss(url):
    '''获取上数处理对象'''
    report_data = {}
    try:
        start = time.clock()
        json_data = getReportByUrl(url)
        end = time.clock()
        logger.info("拉取上树报告时间耗时:%s" % str(end-start))
        start = time.clock()
        oReport = Reportss(json_data)
        report_data = oReport.run()
        end = time.clock()
        logger.info("分析上树报告时间耗时:%s" % str(end-start))
    except Exception as e:
        logger.error("url:%s ss report is fail: %s" % (url,e))
    return report_data

def __reportbr(url):
    '''获取百融处理对象'''
    report_data = {}
    try:
        json_data = getReportByUrl(url)
        oReport = Reportbr(json_data)
        report_data = oReport.run()
    except Exception as e:
        logger.error("url:%s br report is fail: %s" % (url,e))
    return report_data

def __reportmh(url):
    '''获取数据魔盒处理对象'''
    report_data = {}
    try:
        all_url = url.replace(".json", "_all.json")
        start = time.clock()
        json_data = getReportByUrl(all_url)
        report_json_data = getReportByUrl(url)
        end = time.clock()
        logger.info("拉取魔盒报告时间耗时:%s" % str(end-start))
        start = time.clock()
        oReport = Reportmh(json_data,report_json_data)
        report_data = oReport.run()
        end = time.clock()
        logger.info("分析魔盒报告时间耗时:%s" % str(end-start))
    except Exception as e:
        logger.error("url:%s mh report is fail: %s" % (all_url, e))
    return report_data
