import pyssdb
import requests
from requests import RequestException
from lxml import etree
import random
import json
from module.yiyiyuan import YiUser
from model.antifraud import TagInfo
import re
import sys
import os
from lib.logger import logger
import time
from lib.ssdb_config import *

# 爬取2345实用查询 http://tools.2345.com/hmhmd/
class Spider(object):
    rootPath = sys.path[0]
    path = rootPath + '/commands/data/bst.txt'

    def __init__(self):
        self.ua_li =[
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/22.0.1207.1 Safari/537.1",
        "Mozilla/5.0 (X11; CrOS i686 2268.111.0) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.6 (KHTML, like Gecko) Chrome/20.0.1092.0 Safari/536.6",
        "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.6 (KHTML, like Gecko) Chrome/20.0.1090.0 Safari/536.6",
        "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/19.77.34.5 Safari/537.1",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/536.5 (KHTML, like Gecko) Chrome/19.0.1084.9 Safari/536.5",
        "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/536.5 (KHTML, like Gecko) Chrome/19.0.1084.36 Safari/536.5",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1063.0 Safari/536.3",
        "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1063.0 Safari/536.3",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_0) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1063.0 Safari/536.3",
        "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1062.0 Safari/536.3",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1062.0 Safari/536.3",
        "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1061.1 Safari/536.3",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1061.1 Safari/536.3",
        "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1061.1 Safari/536.3",
        "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1061.0 Safari/536.3",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.24 (KHTML, like Gecko) Chrome/19.0.1055.1 Safari/535.24",
        "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/535.24 (KHTML, like Gecko) Chrome/19.0.1055.1 Safari/535.24",
        "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/531.21.8 (KHTML, like Gecko) Version/4.0.4 Safari/531.21.10",
        "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/533.17.8 (KHTML, like Gecko) Version/5.0.1 Safari/533.17.8",
        "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.2 Safari/533.18.5",
        "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-GB; rv:1.9.1.17) Gecko/20110123 (like Firefox/3.x) SeaMonkey/2.0.12",
        "Mozilla/5.0 (Windows NT 5.2; rv:10.0.1) Gecko/20100101 Firefox/10.0.1 SeaMonkey/2.7.1",
        "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; en-US) AppleWebKit/532.8 (KHTML, like Gecko) Chrome/4.0.302.2 Safari/532.8",
        "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.464.0 Safari/534.3",
        "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_5; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.15 Safari/534.13",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_2) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.186 Safari/535.1",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.54 Safari/535.2",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16.0.912.36 Safari/535.7",
        "Mozilla/5.0 (Macintosh; U; Mac OS X Mach-O; en-US; rv:2.0a) Gecko/20040614 Firefox/3.0.0 ",
        "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10.5; en-US; rv:1.9.0.3) Gecko/2008092414 Firefox/3.0.3",
        "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.1) Gecko/20090624 Firefox/3.5",
        "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.14) Gecko/20110218 AlexaToolbar/alxf-2.0 Firefox/3.6.14",
        "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10.5; en-US; rv:1.9.2.15) Gecko/20110303 Firefox/3.6.15",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.89 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36'
    ]
        self.temp_url = "http://tools.2345.com/frame/black/result/{}"
        self.headers = {'User-Agent': random.choice(self.ua_li)}

    def search_phone(self, phone_num):  # 解析url，获取响应
        item = {}
        url = self.temp_url.format(phone_num)
        r = requests.get(url, headers=self.headers)
        html_str = r.content.decode('gbk')
        try:
            html = etree.HTML(html_str)
            number = re.search(r'http://tools.2345.com/frame/black/result/(\d*)', url, re.S).group(1)
            item['phone'] = number
            li_list = html.xpath("//ul[@class='ulInforList']/li")
            if len(li_list) > 0:
                for li in li_list:
                    biaoshi = li.xpath("//span[@class='sStyle']/text()")
                biaoshi = list(set(biaoshi))
                item['tag_type'] = biaoshi
                logger.info('爬取出来的标识是%s' % item)
            else:
                item = {}
                logger.info('电话号码%s的标识为空' % number)
            return item
        except RequestException as e:
            with open(self.path, 'r') as f:
                user_id = f.read()
            logger.error('userid is %s ,request fail %s'%(user_id, e))

    def read_sql(self):  # 读数据库
        c = pyssdb.Client(SSDB_IP, SSDB_PORT)
        with open(self.path, 'r') as f:
            start = f.read()
        start = int(start)
        end = start + 1
        maxid = YiUser().get_maxid()
        if start >= maxid:
            logger.info('start的值是%d,最大的uid是%d'%(start,maxid))
            os._exit(0)
        if end > maxid:
            end = maxid
        rows = YiUser().getMobileNum(start, end)
        with open(self.path, 'w') as f:
            f.write(json.dumps(end))
        if len(rows) == 0:
            return False
        values = []
        for key in rows:
            mobile = key.mobile
            values.append(mobile)
            ssnum = c.get(mobile)
            if ssnum is not None:
                num = json.loads(ssnum)
                logger.info('读取通讯里面的数据个数为%d'%len(num))
                for i in num:
                    values.append(i)
        return values

    def save_sql(self, item):  # 存储到数据库
        TagInfo().addResult(item)

    def run(self):  # 主要逻辑运行
        start = time.time()
        numbers = self.read_sql()
        if numbers:
            for tel in numbers:
                try:
                    logger.info('正在爬的手机号码是%s' % tel)
                    item = self.search_phone(tel)
                    self.save_sql(item)
                except Exception as e:
                    logger.info(e)
            time.sleep(1)
        end = time.time()
        logger.info('这次的爬取时间是%d秒' % (end-start))
