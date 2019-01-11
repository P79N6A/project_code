# -*- coding: utf-8 -*-
'''
Created on 2016-5-5
基类命令行控制器
'''
from flask import render_template


class BaseCommand(object):

    def __init__(self):
        pass

    def render(self, template_name_or_list, **context):
        # 渲染模板
        return render_template(template_name_or_list, **context)

    def getMethod(self, method):
        """  调用类中方法  """
        try:
            # 查找方法并检测是否可调用
            fun = getattr(self, method)
            if callable(fun):
                return fun
            else:
                return None
        except Exception as _a:
            return None

    def run(self, method):
        f = self.getMethod(method)
        if f:
            return f()
        else:
            return None
