from apps.yiyiyuan.models.yi_user import yi_user

class yiUser():

    # 获取用户手机号与创建时间
    def get_user(self,user_id):
        data = yi_user().get_mobile(user_id=user_id)
        return data