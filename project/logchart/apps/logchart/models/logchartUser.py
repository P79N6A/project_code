from django.db import models
from apps.helper.timeHelper import timeHelper
from django.contrib.auth.hashers import make_password, check_password

class logchartUser(models.Model):
    id = models.AutoField(primary_key=True)
    user_name = models.CharField(max_length=30)
    user_pwd = models.CharField(max_length=225)
    creat_time = models.DateTimeField()

    class Meta:
        db_table = "logchart_user"

    # 登陆方法
    def login(self,user_name,user_pwd):
        # 查到用户名对应的user
        user = logchartUser.objects.all().filter(user_name=user_name)
        # 如果用户名重复返回false
        if len(user) == 1:
            userpwd = logchartUser.objects.only('user_pwd').filter(user_name=user_name)
            if check_password(user_pwd,userpwd[0].user_pwd):
                return True
            else:
                return False
        else:
            return False

    # 注册
    def register(self,user_name,user_pwd):
        # 查询用户名是否被注册
        user = logchartUser.objects.filter(user_name=user_name).all()
        if len(user) != 0:
            return False
        else:
            user = logchartUser()
            # 名字
            user.user_name = user_name
            # 时间
            user.creat_time = timeHelper.getFormatDate('now')
            # 加密密码
            user.user_pwd = make_password(user_pwd)
            # 保存
            user.save()
            return True
