# -*- coding: utf-8 -*-
from lib.application import db
from model.base_model import BaseModel
from datetime import datetime, timedelta
import json
import urllib.request
import urllib.error
import urllib.parse
import socket
from lib.logger import logger
from sqlalchemy import desc, and_


class JxlStat(db.Model, BaseModel):
    # 指定数据库
    __bind_key__ = 'xhh_open'
    # 表名
    __tablename__ = 'jxl_stat'

    id = db.Column(db.Integer, primary_key=True)
    aid = db.Column(db.Integer, nullable=False,
                    server_default=db.FetchedValue())
    requestid = db.Column(db.Integer, nullable=False,
                          index=True, server_default=db.FetchedValue())
    name = db.Column(db.String(50), nullable=False)
    idcard = db.Column(db.String(20), nullable=False)
    phone = db.Column(db.String(20), nullable=False, index=True)
    website = db.Column(db.String(50), nullable=False)
    create_time = db.Column(db.DateTime, nullable=False)
    url = db.Column(db.String(100), nullable=False)
    source = db.Column(db.Integer, nullable=False)

    def __init__(self):
        super(JxlStat, self).__init__()

    def getByRequestId(self, requestid):
        if requestid is None:
            return None
        now_time = datetime.now()
        end_time = (now_time + timedelta(days=-120)
                    ).strftime('%Y-%m-%d %H:%M:%S')
        where = and_(JxlStat.requestid == int(requestid),
                     JxlStat.create_time >= end_time)
        data = self.query.filter(where).order_by(
            desc(JxlStat.create_time)).first()
        if data is None:
            return None
        domain = self._getDomain(data.create_time)
        res = {}
        res['report_url'] = domain + data.url
        res['detail_url'] = res['report_url'].replace(".json", "_detail.json")
        res['jxlstat_id'] = data.id
        res['source'] = data.source
        return res

    def getData(self, id):
        '''
        获取需要处理的数据, 默认查询1个人
        '''
        if id is None:
            return None

        now_time = datetime.now()
        end_time = (now_time + timedelta(days=-120)
                    ).strftime('%Y-%m-%d %H:%M:%S')
        where = and_(JxlStat.id == int(id),
                     JxlStat.create_time >= end_time)
        data = self.query.filter(where).order_by(
            desc(JxlStat.create_time)).first()
        return data

    def getMaxId(self):
        sql = "select max(id) as max_id from jxl_stat"
        max_id = db.session.execute(sql, bind=self.get_engine()).fetchone()
        _max_id = max_id[0]
        return _max_id

    def getById(self, id):
        '''
        根据id获取
        @param int id
        @return []
        '''
        if id is None:
            return None

        now_time = datetime.now()
        end_time = (now_time + timedelta(days=-180)
                    ).strftime('%Y-%m-%d %H:%M:%S')
        where = and_(JxlStat.id == int(id),
                     JxlStat.create_time >= end_time)
        data = self.query.filter(where).order_by(
            desc(JxlStat.create_time)).first()
        if data is None:
            return None

        domain = self._getDomain(data.create_time)
        res = {}
        res['report_url'] = domain + data.url
        #res['report_url'] = "http://182.92.80.211:8091/ofiles/jxl/7309173.json"
        res['detail_url'] = res['report_url'].replace(".json", "_detail.json")
        res['jxlstat_id'] = data.id
        res['source'] = data.source
        return res

    #
    def getByPhone(self, phone):
        if phone is None:
            return None
        now_time = datetime.now()
        end_time = (now_time + timedelta(days=-180)
                    ).strftime('%Y-%m-%d %H:%M:%S')
        where = and_(JxlStat.phone == phone,
                     JxlStat.website.notin_(["jingdong"]),
                     JxlStat.create_time >= end_time)
        data = self.query.filter(where).order_by(
            desc(JxlStat.create_time)).first()
        if data is None:
            return None

        domain = self._getDomain(data.create_time)
        res = {}
        res['report_url'] = domain + data.url
        #res['report_url'] = "http://182.92.80.211:8091/ofiles/jxl/7309173.json"
        res['detail_url'] = res['report_url'].replace(".json", "_detail.json")
        res['jxlstat_id'] = data.id
        res['source'] = data.source
        return res

    def _getDomain(self, create_time):
        '''
        获取域名
        @param  datetime $create_time 时间格式
        @return str 域名
        '''
        #@todo
        from lib.config import get_config
        cf = get_config()
        if cf.TESTING:
            return 'http://182.92.80.211:8091'

        now = datetime.now()
        ta = now - create_time
        if ta.days > 1:
            #domain = "http://124.193.149.180:8100"
            #domain = "http://123.207.141.180"
            domain = "http://10.139.36.194"
        else:
            domain = "http://open.xianhuahua.com"
        return domain

    def _getByUrl(self, url):
        # 获取url中的内容, 设置超时
        html = self._getByUrl2(url)
        if html is None:
            # 重试
            html = self._getByUrl2(url)

        if html is None:
            raise Exception(1000, 'cant download by ' + url)

        return html

    def _getByUrl2(self, url):
        socket.setdefaulttimeout(25)
        try:
            response = urllib.request.urlopen(url)
            html = response.read()
        except Exception as e:
            logger.error('url get fail %s' % e)
            html = None
        return html

    def getReport(self, url):
        # 获取报告
        # return self.getTestReport(url)
        strings = self._getByUrl(url)
        data = json.loads(strings)
        return data

    def getDetail(self, url):
        # 获取详情
        # return self.getTestDetail(url)
        strings = self._getByUrl(url)
        data = json.loads(strings)
        return data

    def getTestReport(self):
        import os
        path = os.getcwd()

        fp = open(path + "/tests/15882797956/15882797956.json", "r")
        data = json.loads(fp.read())
        fp.close()
        return data

    def getTestDetail(self):
        import os
        path = os.getcwd()

        fp = open(path + "/tests/15882797956/15882797956_detail.json", "r")
        data = json.loads(fp.read())
        fp.close()
        return data
