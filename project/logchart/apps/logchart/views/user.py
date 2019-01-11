from apps.logchart.views.baseView import baseView
from apps.logchart.models.logchartUser import logchartUser

class user():

    def login(request):
        # 获取用户输入的信息
        user_name = request.POST.get('user_name', '')
        user_pwd = request.POST.get('user_pwd', '')

        # 参数为空则返回登录页面
        if user_name == '' or user_pwd == '':
            return baseView.urlRender(request,'user.html')

        # 判断是否正确
        if logchartUser().login(user_name, user_pwd):
            # 往session中插入用户名
            baseView.setSession(request,'username',user_name)
            # 往session中插入登陆状态
            baseView.setSession(request, 'is_login', True)
            # 登录成功重定向到首页
            return baseView.urlRedirect('/index')
        else:
            # 账号密码错误则返回登录页面
            return baseView.urlRender(request,'user.html')

    def logout(request):
        baseView.setSession(request, 'is_login', False)
        return baseView.urlRender(request, 'user.html')



