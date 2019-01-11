# -*- coding: utf-8 -*-
# sqlacodegen  mysql://root:123!@#@127.0.0.1/xhh_test --outfile yyy.py --flask
from lib.application import db
from .base_model import BaseModel
from .yi_user import YiUser

class YiFavoriteContact(db.Model, BaseModel):
    __bind_key__ = 'xhh_yiyiyuan'
    __tablename__ = 'yi_favorite_contacts'

    id = db.Column(db.BigInteger, primary_key=True)
    user_id = db.Column(db.BigInteger, nullable=False)
    contacts_name = db.Column(db.String(20), nullable=False)
    mobile = db.Column(db.String(20), nullable=False)
    relatives_name = db.Column(db.String(20), nullable=False)
    phone = db.Column(db.String(20), nullable=False)
    last_modify_time = db.Column(db.DateTime, nullable=False)
    create_time = db.Column(db.DateTime)

    def getByUserId(self, user_id):
        return self.query.filter_by(user_id=user_id).limit(1).first()

    def contactDue(self, dbContact):
        # 亲属联系人是否逾期
        if dbContact is None:
            return {}

        mobiles = []
        #亲属 
        if 'phone' in dbContact.keys():
            mobiles.append(str(dbContact['phone']))
        #常用
        if 'mobile' in dbContact.keys():
            mobiles.append(str(dbContact['mobile']))

        if len(mobiles) == 0:
            return {}

        oUser = YiUser()
        overdue_users = oUser.isOverdueMobile(mobiles)
        overdue_mobiles = [user[0] for user in overdue_users]

        # # 判断是否逾期
        contract_due_data = {}
        if 'phone' in dbContact.keys() and dbContact['phone'] is not None:
            if dbContact['phone'] in overdue_mobiles:
                contract_due_data['com_r_overdue'] = 1
            else:
                contract_due_data['com_r_overdue'] = 0

        if 'mobile' in dbContact.keys() and dbContact['mobile'] is not None:
            if dbContact['mobile'] in overdue_mobiles:
                contract_due_data['com_c_overdue'] = 1
            else:
                contract_due_data['com_c_overdue'] = 0

        return contract_due_data
