# -*- coding:utf-8 -*-
# 将字典转换成对象

class dictObj:
    def __init__(self, **entries):
        self.__dict__.update(entries)

def dict2object(dictData):
    return dictObj(**dictData)