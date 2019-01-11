from .report import Report
from .reportbr import Reportbr
from .reportss import Reportss
from lib.logger import logger
from util.custom_function import getReportByUrl

def analysis_report(source,report_url):
    if source in [1,2]:
        res = __report(report_url)
    elif source == 4 :
        res = __reportss(report_url)
    elif source in [5,6] :
        res = __reportbr(report_url)
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
        json_data = getReportByUrl(url)
        oReport = Reportss(json_data)
        report_data = oReport.run()
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
