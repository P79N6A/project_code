# !/usr/bin/python
# -*- coding: utf-8 -*-
""" 
@software: PyCharm 
@file: app_class.py 
@time: 2018/10/24 15:01 
"""

import time
import re
import os
import json
import urllib.request
import urllib.parse
from bs4 import BeautifulSoup


class YybAppDefine(object):
    def __init__(self):
        self.url_applaction = "https://android.myapp.com/myapp/cate/appList.htm?orgame=1&categoryId="
        self.url_applaction_id = ['-10',
                                '122',
                                '102',
                                '110',
                                '103',
                                '108',
                                '115',
                                '106',
                                '101',
                                '119',
                                '104',
                                '114',
                                '117',
                                '107',
                                '112',
                                '118',
                                '111',
                                '109',
                                '105',
                                '100',
                                '113',
                                '116']
        self.url_game = 'https://android.myapp.com/myapp/cate/appList.htm?orgame=2&categoryId='
        self.url_game_id = ['147',
                            '121',
                            '149',
                            '144',
                            '151',
                            '148',
                            '153',
                            '146']
        self.apkPublishTime = ''
        self.appDownCount = ''
        self.appId = ''
        self.appName = ''
        self.authorId = ''
        self.authorName = ''
        self.averageRating = ''
        self.categoryId = ''
        self.categoryName = ''
        self.newFeature = ''
        self.pkgName = ''
        self.versionCode = ''
        self.versionName = ''

        self.url_byapkname = 'https://sj.qq.com/myapp/detail.htm?apkName='


        self.header = { 'User-agent': 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36',
                        'Connection': 'keep-alive',
                        'Accept': '*/*',
                        'Accept-Encoding': 'sdch',
                        'Accept-Language': 'zh-CN,zh;q=0.8'}

    def gethtmldata(self,categoryId,pageconten,isappla):

        if isappla:
            if pageconten == '':
                url = self.url_applaction + categoryId
            else:
                url = self.url_applaction + categoryId + '&pageContext=' + pageconten
        else:
            if pageconten == '':
                url = self.url_game + categoryId
            else:
                url = self.url_game + categoryId + '&pageContext=' + pageconten
        print(url)
        req = urllib.request.Request(url,None,self.header)
        # req.add_header("User-Agent","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safri/537.36")
        req.add_header('User-Agent','Mozilla/5.0 (Windows NT 10.0; WOW64; rv:61.0) Gecko/20100101 Firefox/61.0')
        data = urllib.request.urlopen(req).read().decode("utf-8")
        data_info = json.loads(data)
        return data_info

    def json2string(self,data_dict):
        result = []
        data_list = data_dict.get('obj','')
        pageContext = data_dict.get('pageContext','')
        if data_list == '' or data_list is None or type(data_list) != list:
            return result
        for data in data_list:
            result_str =  str(data.get('appId','')) + '\t' + \
                        str(data.get('appName','')) + '\t' + \
                         str(data.get('pkgName', '')) + '\t' + \
                         str(data.get('authorId','')) + '\t' + \
                        str(data.get('authorName','')) + '\t' + \
            str(time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(data.get('apkPublishTime',0)))) + '\t' + \
                        str(data.get('appDownCount', '')) + '\t' + \
                        str(data.get('averageRating','')) + '\t' + \
                        str(data.get('categoryId','')) + '\t' + \
                        str(data.get('categoryName','')) + '\t' + \
                        str(data.get('versionCode','')) + '\t' + \
                        str(data.get('versionName','')) + '\n'
            result.append(result_str)

        return result,pageContext

    def getappdatabypkgname(self,pkgname):
        url = self.url_byapkname + pkgname
        result = {}
        try:
            req = urllib.request.Request(url, None, self.header)
            response = urllib.request.urlopen(req)
            the_page = response.read()
            soup = BeautifulSoup(the_page, 'lxml')

            app_name_ = soup.find('div', {'class': "det-name-int"})
            app_name = re.findall(r'>(.+?)</div>', str(app_name_))[0]

            # < div class ="com-blue-star-num" > 5分 < / div >
            # < div class ="det-ins-num" > 80万下载 < / div >
            # < div class ="det-size" > 12.3M < / div >
            # < a href = "category.htm?categoryId=114" class ="det-type-link" id="J_DetCate" > 理财 < / a >
            combluestarnum = soup.find('div', {'class': 'com-blue-star-num'})
            combluestarnum = re.findall(r'>(.+?)</div>', str(combluestarnum))[0]
            combluestarnum = combluestarnum[0:len(combluestarnum)-1]
            detinsnum = soup.find('div', {'class': 'det-ins-num'})
            detinsnum = re.findall(r'>(.+?)</div>', str(detinsnum))[0]
            detinsnum = detinsnum[0:len(detinsnum)-2]
            detsize = soup.find('div', {'class': 'det-size'})
            detsize = re.findall(r'>(.+?)</div>', str(detsize))[0]
            J_DetCate = soup.find('a', {'id': 'J_DetCate'})
            J_DetCate = re.findall(r'>(.+?)</a>', str(J_DetCate))[0]

            othinfo = soup.find_all('div', {'class': 'det-othinfo-data'})[0:3]

            version = re.findall(r'>(.+?)</div>', str(othinfo[0]))[0]
            apkpublishtime = re.findall(r'data-apkpublishtime="(.+?)"', str(othinfo[1]))[0]
            apkpublishtime = str(time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(int(apkpublishtime))))
            company = re.findall(r'>(.+?)</div>', str(othinfo[2]))[0]

            result['app_package'] = pkgname
            result['app_name'] = app_name
            result['first_label'] = J_DetCate
            result['second_lable'] = ''
            result['company'] = company
            result['app_version'] = version[:30]
            result['publish_date'] = apkpublishtime
            result['down_count'] = detinsnum
            result['comment_rate'] = combluestarnum
            result['comment_person'] = ''
            result['app_size'] = detsize
            result['down_from'] = 1
        finally:
            return result

    #应用宝市场全量爬取分类
    def avoid_10060(self,categoryId,pageconten,isappla):
    		
        error_time = 0
        while True:
            #防止爬取拦截 限时 爬取多次
            time.sleep(3)
            try:
                return self.gethtmldata(categoryId,pageconten,isappla)
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

    #应用宝市场根据apkname 查询分类
    def avoid_100602(self,apkname):
        error_time = 0
        while True:
            time.sleep(3)
            #防止爬取拦截 限时 爬取多次
            try:
                return self.getappdatabyapkname(apkname)
            except:
                error_time += 1
                if error_time == 100:
                    print('your network is little bad')
                    time.sleep(40)
                if error_time == 101:
                    print('your network is broken')
                    return ''

                continue
            break