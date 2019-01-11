from functools import wraps
from django.http import HttpResponseRedirect, HttpResponse
from django.shortcuts import render
import json

class baseView():
    # 检测用户是否登录的过滤器
    @classmethod
    def checkLogin(cls):
        def doCheck(view_func):
            @wraps(view_func)
            def _wrapped_view(request, *args, **kwargs):
                if not request.session.get("is_login", None):
                    return HttpResponseRedirect('/login')
                return view_func(request, *args, **kwargs)
            return _wrapped_view
        return doCheck

    # 设置登录session
    @classmethod
    def setSession(cls, request, key, values):
        request.session[key] = values
        # 设置session永不失效
        request.session.set_expiry(0)

    # 跳转渲染页面
    @classmethod
    def urlRender(cls, request, template, data=None, active=None):
        if data!=None and active!=None:
            data["active"] = active
        return render(request, template, data)

    # 重定向url请求
    @classmethod
    def urlRedirect(cls, url):
        return HttpResponseRedirect(url)

    # ajax返回错误
    @classmethod
    def errorJson(cls, code, msg):
        ret = {'code': code, 'msg': msg}
        return HttpResponse(json.dumps(ret))

    # ajax返回成功
    @classmethod
    def successJson(cls, data = None):
        ret = {'code': 'success', 'msg': '成功', 'data': data}
        return HttpResponse(json.dumps(ret))
