from django.db import models

class pea_user(models.Model):
    user_id = models.BigIntegerField(primary_key=True)
    nickname = models.CharField(max_length=64, null=True)
    head = models.CharField(max_length=256, null=True)
    openid = models.CharField(max_length=128, null=True)
    status = models.CharField(max_length=16, null=True)
    role = models.IntegerField(null=False, default=0)
    mobile = models.CharField(max_length=20, null=False)
    identity = models.CharField(max_length=20, null=True)
    realname = models.CharField(max_length=32, null=True)
    identity_valid = models.IntegerField(null=False)
    sex =  models.IntegerField(null=False, default=1)
    from_code = models.IntegerField(null=True)
    invite_code = models.IntegerField(null=False)
    come_from = models.IntegerField(null=False, default=1)
    last_login_time = models.DateTimeField(null=True)
    create_time = models.DateTimeField(null=True)
    last_modify_time = models.DateTimeField(null=True)
    auth_key =  models.CharField(max_length=100, null=True)
    notice = models.DateTimeField(null=True)
    password = models.CharField(max_length=64, null=True)
    trade_password = models.CharField(max_length=64, null=True)
    password_type = models.IntegerField(null=False, default=1)
    trade_password_type = models.IntegerField(null=False, default=1)
    is_auth = models.IntegerField(null=False, default=1)
    dotch_id = models.BigIntegerField(null=False, default=0)
    app_windows = models.DateTimeField(null=True)
    web_windows = models.DateTimeField(null=True)
    wap_windows = models.DateTimeField(null=True)
    is_ourbank = models.BigIntegerField(null=False, default=1)
    android_id = models.CharField(max_length=40, null=True)
    windows_tag = models.BigIntegerField(null=False, default=0)
    version = models.IntegerField(null=True)

    class Meta:
        app_label = "peanut"
        db_table = "pea_user"

    # 获取所有数据
    def get_all(self):
        data = pea_user.objects. \
            values("user_id", "nickname", "status", "mobile", "identity", "realname",
                   "last_login_time", "create_time", "last_modify_time")
        return data

    # 通过id获取手机号与注册时间
    def get_mobile(self, user_id):
        data = pea_user.objects.values('create_time', 'mobile').filter(user_id=user_id)
        return data

    def get_time_rang_count(self, start_time, end_time):
        count = pea_user.objects.filter(create_time__range=[start_time, end_time]).count()
        return count

    def get_time_rang(self, start_time, end_time, offset = 0, limit = 0):
        if limit == 0:
            data = pea_user.objects. \
                values("user_id", "nickname", "status", "mobile", "identity", "realname",
                   "last_login_time", "create_time", "last_modify_time"). \
                filter(create_time__range=[start_time, end_time]).order_by("-create_time")
        else:
            data = pea_user.objects. \
                values("user_id", "nickname", "status", "mobile", "identity", "realname",
                   "last_login_time", "create_time", "last_modify_time"). \
                    filter(create_time__range=[start_time, end_time]).order_by("-create_time")[offset:limit]
        return data