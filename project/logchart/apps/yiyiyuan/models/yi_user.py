from django.db import models

class yi_user(models.Model):
    user_id = models.BigIntegerField(primary_key=True)
    openid = models.CharField(max_length=64, null=True)
    mobile = models.CharField(max_length=20, null=True)
    invite_code = models.CharField(max_length=32, null=True)
    invite_qrcode = models.CharField(max_length=32, null=True)
    from_code = models.CharField(max_length=32, null=True)
    user_type = models.IntegerField(null=True, default=1)
    status = models.IntegerField(null=False, default=1)
    identity_valid = models.IntegerField(null=False, default=1)
    school_valid = models.IntegerField(null=False, default=1)
    school = models.CharField(max_length=64, null=True)
    school_id = models.IntegerField(null=True)
    edu = models.CharField(max_length=64, null=True)
    school_time = models.CharField(max_length=64, null=True)
    realname = models.CharField(max_length=32, null=True)
    identity = models.CharField(max_length=20, null=True)
    industry = models.IntegerField(null=True)
    company = models.CharField(max_length=128, null=True)
    position = models.CharField(max_length=128, null=True)
    telephone = models.CharField(max_length=32, null=True)
    address = models.CharField(max_length=128, null=True)
    pic_self = models.CharField(max_length=128, null=True)
    pic_identity = models.CharField(max_length=128, null=True)
    pic_type = models.IntegerField(null=False, default=0)
    come_from = models.IntegerField(null=False, default=2)
    down_from = models.CharField(max_length=32, null=True)
    serverid = models.CharField(max_length=128, null=True)
    create_time = models.DateTimeField(null=True)
    pic_up_time = models.DateTimeField(null=True)
    final_score = models.IntegerField(null=True)
    birth_year = models.IntegerField(null=True)
    last_login_time = models.DateTimeField(null=True)
    last_login_type = models.CharField(max_length=16, null=True)
    verify_time = models.DateTimeField(null=True)
    is_webunion = models.CharField(max_length=8, null=False, default='no')
    webunion_confirm_time = models.DateTimeField(null=True)
    is_red_packets = models.CharField(max_length=4, null=False, default='no')

    class Meta:
        app_label = "yiyiyuan"
        db_table = "yi_user"

    # 获取第一条
    def get_all(self):
        data = yi_user.objects. \
            values("user_id", "mobile", "status", "realname", "identity", "come_from",
                   "down_from", "create_time", "birth_year", "last_login_time", "verify_time")
        return data

    # 通过id获取手机号与注册时间
    def get_mobile(self, user_id):
        data = yi_user.objects.values('create_time', 'mobile').filter(user_id=user_id)
        return data

    def get_time_rang_count(self, start_time, end_time):
        count = yi_user.objects.filter(create_time__range=[start_time, end_time]).count()
        return count

    def get_time_rang(self, start_time, end_time, offset = 0, limit = 0):
        if limit == 0:
            data = yi_user.objects. \
                values("user_id", "mobile", "status", "realname", "identity", "come_from",
                    "down_from", "create_time", "birth_year", "last_login_time", "verify_time"). \
                filter(create_time__range=[start_time, end_time]).order_by("-create_time")
        else:
            data = yi_user.objects. \
                values("user_id", "mobile", "status", "realname", "identity", "come_from",
                    "down_from", "create_time", "birth_year", "last_login_time", "verify_time"). \
                    filter(create_time__range=[start_time, end_time]).order_by("-create_time")[offset:limit]
        return data