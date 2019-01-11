# -*- coding: utf-8 -*-
from lib.application import db
from lib.logger import logger
from model.base_model import BaseModel
from datetime import datetime

class AfAddressTag(db.Model, BaseModel):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_address_tag'

    id = db.Column(db.BigInteger, primary_key=True)
    aid = db.Column(db.Integer, nullable=False, server_default="0")
    user_id = db.Column(db.BigInteger, nullable=False, server_default="0")
    loan_id = db.Column(db.BigInteger, nullable=False, server_default="0")
    ads_num = db.Column(db.Integer, nullable=False, server_default="0")
    ads_num_uniq = db.Column(db.Integer, nullable=False, server_default="0")
    advertis = db.Column(db.String(255), server_default="")
    express = db.Column(db.String(255), server_default="")
    harass = db.Column(db.String(255), server_default="")
    house_agent = db.Column(db.String(255), server_default="")
    cheat = db.Column(db.String(255), server_default="")
    company_tel = db.Column(db.String(255), server_default="")
    invite = db.Column(db.String(255), server_default="")
    taxi = db.Column(db.String(255), server_default="")
    education = db.Column(db.String(255), server_default="")
    insurance = db.Column(db.String(255), server_default="")
    ring = db.Column(db.String(255), server_default="")
    service_tel = db.Column(db.String(255), server_default="")
    delinquency = db.Column(db.String(255), server_default="")
    modify_time = db.Column(db.DateTime)
    create_time = db.Column(db.DateTime)


    '''
    保存数据
    '''
    def saveData(self, tag_list, proportion):
        try:
            self.aid = tag_list.aid
            self.user_id = proportion['user_id']
            self.loan_id = proportion['loan_id']
            self.ads_num = proportion['mail_list_num']
            self.ads_num_uniq = proportion['weight_loss_num']
            self.advertis = proportion['label_num']['advertisement_tel']
            self.express = proportion['label_num']['express_tel']
            self.harass = proportion['label_num']['harass_tel']
            self.house_agent = proportion['label_num']['house_propert_tel']
            self.cheat = proportion['label_num']['cheat_tel']
            self.company_tel = proportion['label_num']['enterprise_tel']
            self.invite = proportion['label_num']['recruit_tel']
            self.taxi = proportion['label_num']['lease_car_tel']
            self.education = proportion['label_num']['education_tel']
            self.insurance = proportion['label_num']['insurance_tel']
            self.ring = proportion['label_num']['sound_a_sound_tel']
            self.service_tel = proportion['label_num']['customer_service_tel']
            self.delinquency = proportion['label_num']['illegality_tel']
            self.modify_time = datetime.now()
            self.create_time = datetime.now()
            self.add()
            db.session.commit()
            return True
        except AttributeError as error:
            print(error)
            return False

        except sqlalchemy.exc.IntegrityError as error:
            print(error)
            return False

    '''
    保存数据
    '''
    def saveResources(self, proportion):
        try:
            self.aid = proportion['aid']
            self.user_id = proportion['user_id']
            self.loan_id = proportion['loan_id']
            self.ads_num = proportion['mail_list_num']
            self.ads_num_uniq = proportion['weight_loss_num']
            self.advertis = proportion['label_num']['advertisement_tel']
            self.express = proportion['label_num']['express_tel']
            self.harass = proportion['label_num']['harass_tel']
            self.house_agent = proportion['label_num']['house_propert_tel']
            self.cheat = proportion['label_num']['cheat_tel']
            self.company_tel = proportion['label_num']['enterprise_tel']
            self.invite = proportion['label_num']['recruit_tel']
            self.taxi = proportion['label_num']['lease_car_tel']
            self.education = proportion['label_num']['education_tel']
            self.insurance = proportion['label_num']['insurance_tel']
            self.ring = proportion['label_num']['sound_a_sound_tel']
            self.service_tel = proportion['label_num']['customer_service_tel']
            self.delinquency = proportion['label_num']['illegality_tel']
            self.modify_time = datetime.now()
            self.create_time = datetime.now()
            self.add()
            db.session.commit()
            return True
        except AttributeError as error:
            logger.info("number_label: 通讯录记录失败：%s" % error)
            print(error)
            return False

        except Exception as error:
            logger.info("number_label: 通讯录记录失败：%s" % error)
            print(error)
            return False

