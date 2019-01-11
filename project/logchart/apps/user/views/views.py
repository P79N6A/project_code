from django.http import HttpResponseRedirect
from django.shortcuts import render
from ..models.models import adminUser
from session import session
from django.contrib.auth.hashers import make_password,check_password
# Create your views here.
def index(request):
    return render(request,'user.html')

def login(request):
    # 获取用户输入的信息
    user_name = request.POST.get('user_name')
    user_pwd = request.POST.get('user_pwd')
    user = adminUser()
    # 判断用户是否输入用户名密码
    if user_name == '' or user_pwd == '':
        # 返回user.html页面
        return render(request,'user.html')
    # 判断是否正确
    elif user.login(user_name=user_name,user_pwd=user_pwd):
        # 往session中插入用户名
        session.set(request,'username',user_name)
        # 往session中插入登陆状态
        session.set(request, 'is_login', True)
        # 重定向到index
        return HttpResponseRedirect('/index')
    else:
        # 返回user.html页面
        return render(request, 'user.html')




