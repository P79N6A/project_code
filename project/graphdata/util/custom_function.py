
import json
import urllib.request, urllib.error, urllib.parse
import socket
import hashlib

from lib.logger import logger
from lib.config import get_config

def getReportByUrl(url):
        # 获取url中的内容, 设置超时
        html = __getByUrl2(url)
        if html is None:
            # 重试
            html = __getByUrl2(url)
        if html is None:
            raise Exception(1000, 'cant download by ' + url)
        data = json.loads(html)
        return data

def __getByUrl2(url):
    socket.setdefaulttimeout(25)
    try:
        response = urllib.request.urlopen(url)
        html = response.read()
    except Exception as e:
        logger.error('url get fail %s' % e)
        html = None
    return html

def createSignByMd5(params):
    sorted_params = sorted(params.items(), key=lambda item:item[0],reverse=False)
    list_iterms = []
    for iterm in sorted_params:
        list_iterms.append('='.join(iterm))
    params_str = '&'.join(list_iterms)
    ms = hashlib.md5()
    ms.update(params_str.encode('utf-8'))
    md5_str = ms.hexdigest()
    SECRET_KEY = get_config().SECRET_KEY
    md5obj = hashlib.md5()
    md5obj.update((md5_str[:30]+SECRET_KEY).encode('utf-8'))
    sign = md5obj.hexdigest()
    return sign 