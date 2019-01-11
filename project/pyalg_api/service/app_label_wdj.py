# !/usr/bin/python
# -*- coding: utf-8 -*-
""" 
@software: PyCharm 
@file: app_class_wdj.py 
@time: 2018/10/29 10:35 
"""

import time
import os
import re
import json
import urllib.request
import urllib.parse
from bs4 import BeautifulSoup

datapath = os.getcwd() + '\\data\\'


# noinspection ProblematicWhitespace
class WdjAppDefine(object):
    def __init__(self):
        self.url_applaction = "https://www.wandoujia.com/wdjweb/api/category/more?catId={0}&subCatId={1}&page={2}&ctoken=mhqL7hTS_at1n859ZQJqogVF"
        self.urlcargiddict = dict()
        self.urlcargiddict['5029'] = [
            '5029_716',
            '5029_1006',
            '5029_722',
            '5029_718',
            '5029_719',
            '5029_837']

        self.urlcargiddict['5018'] = [
            '5018_895',
            '5018_599',
            '5018_597',
            '5018_596',
            '5018_601',
            '5018_598',
            '5018_947',
            '5018_948']

        self.urlcargiddict['5014'] = [
            '5014_710',
            '5014_713',
            '5014_712',
            '5014_922',
            '5014_946',
            '5014_714']

        self.urlcargiddict['5024'] = [
            '5024_923',
            '5024_634',
            '5024_632',
            '5024_635',
            '5024_924',
            '5024_968',
            '5024_975']

        self.urlcargiddict['5019'] = [
            '5019_605',
            '5019_963',
            '5019_940',
            '5019_606',
            '5019_604',
            '5019_607']

        self.urlcargiddict['5016'] = [
            '5016_721',
            '5016_720',
            '5016_933',
            '5016_932',
            '5016_920',
            '5016_921']

        self.urlcargiddict['5026'] = [
            '5026_638',
            '5026_936',
            '5026_960',
            '5026_639',
            '5026_969',
            '5026_970']

        self.urlcargiddict['5017'] = [
            '5017_591',
            '5017_592',
            '5017_593',
            '5017_949',
            '5017_966']

        self.urlcargiddict['5023'] = [
            '5023_631',
            '5023_628',
            '5023_627',
            '5023_958',
            '5023_629',
            '5023_955',
            '5023_981',
            '5023_1003']

        self.urlcargiddict['5020'] = [
            '5020_614',
            '5020_918',
            '5020_610',
            '5020_612',
            '5020_950',
            '5020_951',
            '5020_952',
            '5020_953']

        self.urlcargiddict['5021'] = [
            '5021_615',
            '5021_962',
            '5021_618',
            '5021_954',
            '5021_617',
            '5021_616']

        self.urlcargiddict['5028'] = [
            '5028_959',
            '5028_647',
            '5028_801',
            '5028_650',
            '5028_649']

        self.urlcargiddict['5022'] = [
            '5022_961',
            '5022_626',
            '5022_919',
            '5022_622',
            '5022_625']

        self.urlcargiddict['5027'] = [
            '5027_645',
            '5027_646',
            '5027_643',
            '5027_644',
            '5027_956',
            '5027_971']

        self.urlcargiddict['6001'] = [
            '6001_666',
            '6001_668',
            '6001_670',
            '6001_672',
            '6001_723',
            '6001_755']

        self.urlcargiddict['6003'] = [
            '6003_677',
            '6003_678',
            '6003_679',
            '6003_685',
            '6003_1000']

        self.urlcargiddict['6008'] = [
            '6008_704',
            '6008_705',
            '6008_706',
            '6008_906',
            '6008_909',
            '6008_928']

        self.urlcargiddict['6004'] = [
            '6004_681',
            '6004_682',
            '6004_683',
            '6004_686',
            '6004_729',
            '6004_926']

        self.urlcargiddict['6002'] = [
            '6002_673',
            '6002_674',
            '6002_675',
            '6002_676',
            '6002_903',
            '6002_996']

        self.urlcargiddict['6007'] = [
            '6007_694',
            '6007_698',
            '6007_700',
            '6007_702',
            '6007_725',
            '6007_744',
            '6007_929']

        self.urlcargiddict['6009'] = [
            '6009_660',
            '6009_661',
            '6009_662',
            '6009_663',
            '6009_664',
            '6009_736',
            '6009_737',
            '6009_738',
            '6009_743',
            '6009_745',
            '6009_746',
            '6009_747',
            '6009_748',
            '6009_749',
            '6009_759']

        self.urlcargiddict['6005'] = [
            '6005_687',
            '6005_688',
            '6005_689',
            '6005_691',
            '6005_994',
            '6005_1002']

        self.urlcargiddict['6006'] = [
            '6006_692',
            '6006_693',
            '6006_695',
            '6006_696',
            '6006_726',
            '6006_957']

        self.urlcargiddict['5015'] = [
            '5015_573',
            '5015_571',
            '5015_944',
            '5015_964']
        self.url_byapkname = 'https://www.wandoujia.com/apps/'


        self.header = { 'User-agent': 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36',
                        'Connection': 'keep-alive',
                        'Accept': '*/*',
                        'Accept-Encoding': 'sdch',
                        'Accept-Language': 'zh-CN,zh;q=0.8'}

    def gethtmlpagedata(self,catId,subCatId,page):
        url = self.url_applaction.format(catId,subCatId,page)
        print(url)
        req = urllib.request.Request(url,None,self.header)
        # req.add_header("User-Agent","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safri/537.36")
        req.add_header('User-Agent','Mozilla/5.0 (Windows NT 10.0; WOW64; rv:61.0) Gecko/20100101 Firefox/61.0')
        data = urllib.request.urlopen(req).read().decode("utf-8")
        data_info = json.loads(data)
        data_info = data_info.get('data','')
        data_content = data_info.get('content','')
        currPage = data_info.get('currPage',-1)
        if currPage < 1:
            return []

        soup = BeautifulSoup(data_content, 'lxml')
        app_name_ = soup.find_all('li', {'class': "card"})
        app_name = re.findall(r' <li class="card" data-pn="(.+?)" data-suffix="">', str(app_name_))
        if len(app_name_) > 0:
            return app_name
		
    #豌豆荚市场全量分类爬取
    def avoid_10060(self,catId,subCatId,page):
        result = set()
        error_time = 0
        while True:
            time.sleep(3)
            #防止爬取拦截 限时 爬取多次
            try:
                applistname =  self.gethtmlpagedata(catId,subCatId,page)
                if len(applistname) == 0:
                    return []
                for app in applistname:
                    app_result = self.getappdatabyapkname(app)
                    result.add(app_result)
                return list(result)
            except:
                error_time += 1
                if error_time == 100:
                    print('your network is little bad')
                    time.sleep(60)
                if error_time == 101:
                    print('your network is broken')
                    break
                continue
            break

    # 豌豆荚市场按apkname 爬取分类
    def getappdatabypkgname(self,pkgname):
        url = self.url_byapkname + pkgname
        result = {}
        try:
            req = urllib.request.Request(url, None, self.header)
            response = urllib.request.urlopen(req)
            the_page = response.read().decode("utf-8")
            soup = BeautifulSoup(the_page, 'lxml')
            #名称
            app_name_ = soup.find('span',{'class':"title",'itemprop':"name"})
            app_name = re.findall(r'>(.+?)</span>', str(app_name_))[0]
            #发布时间
            datePublished = soup.find('span',{'class':"update-time",'itemprop':"datePublished"})
            datePublished = re.findall(r'datetime="(.+?)"', str(datePublished))[0]
            #appinfo 下载次数  好评率 评论人数
            appinfodata = soup.find('div', {'class': 'app-info-data'})
            appinfodata_i = appinfodata.find_all('i')
            down_count,good_rate,comment_count = re.findall(r'>(.+?)</i>', str(appinfodata_i))
            #大小
            infoslist = soup.find('dl', {'class': 'infos-list'})
            fileSize = infoslist.find('meta', {'itemprop': "fileSize"})
            fileSize = re.findall(r'content="(.+?)"', str(fileSize))[0]

            tagbox = infoslist.find_all('dd', {'class': "tag-box"})

            main_category,subcategory = re.findall(r'itemprop="SoftwareApplicationCategory">(.+?)</a>', str(tagbox))[0:2]

            version = re.findall(r'<dt>版本</dt><dd>(.+?)</dd>', str(infoslist))[0]

            devsites = infoslist.find('span', {'class': "dev-sites",'itemprop':"name"})
            devsites = re.findall(r'>(.+?)</span>', str(devsites))[0]

            result['app_package'] = pkgname
            result['app_name'] = app_name
            result['first_label'] = main_category
            result['second_lable'] = subcategory
            result['company'] = devsites
            result['app_version'] = version[:30]
            result['publish_date'] = datePublished
            result['down_count'] = down_count
            result['comment_rate'] = good_rate
            result['comment_person'] = ''
            result['app_size'] = fileSize
            result['down_from'] = 2
        finally:
            return result