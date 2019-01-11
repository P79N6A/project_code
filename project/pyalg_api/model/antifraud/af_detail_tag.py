# -*- coding: utf-8 -*-
from lib.application import db
from lib.logger import logger
from model.base_model import BaseModel
from datetime import datetime
from sqlalchemy import desc,and_,or_

class AfDetailTag(db.Model, BaseModel):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_detail_tag'

    id = db.Column(db.BigInteger, primary_key=True)
    aid = db.Column(db.Integer, nullable=False, server_default='0')
    user_id = db.Column(db.BigInteger, nullable=False, server_default='0')
    loan_id = db.Column(db.BigInteger, nullable=False, server_default='0')
    request_id = db.Column(db.BigInteger, nullable=False, server_default='0')
    detail_saynum = db.Column(db.Integer, nullable=False, server_default='0')
    detail_telnum = db.Column(db.Integer, nullable=False, server_default='0')
    advertis = db.Column(db.String(255), server_default='')
    express = db.Column(db.String(255), server_default='')
    harass = db.Column(db.String(255), server_default='')
    house_agent = db.Column(db.String(255), server_default='')
    cheat = db.Column(db.String(255), server_default='')
    company_tel = db.Column(db.String(255), server_default='')
    invite = db.Column(db.String(255), server_default='')
    taxi = db.Column(db.String(255), server_default='')
    education = db.Column(db.String(255), server_default='')
    insurance = db.Column(db.String(255), server_default='')
    ring = db.Column(db.String(255), server_default='')
    service_tel = db.Column(db.String(255), server_default='')
    delinquency = db.Column(db.String(255), server_default='')
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
            self.request_id = proportion['request_id']
            self.detail_saynum = proportion['detail_num']
            self.detail_telnum = proportion['weight_loss_detail_num']
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
            self.request_id = proportion['request_id']
            self.detail_saynum = proportion['detail_num']
            self.detail_telnum = proportion['weight_loss_detail_num']
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
            logger.info("number_label: 通话详单记录失败：%s" % error)
            print(error)
            return False

        except Exception as error:
            logger.info("number_label: 通话详单记录失败：%s" % error)
            print(error)
            return False

    def getOne(self, user_id):
        res_data = {
            "advertis": "",
            "express": "",
            "harass": "",
            "house_agent": "",
            "cheat": "",
            "company_tel": "",
            "invite": "",
            "taxi": "",
            "education": "",
            "insurance": "",
            "ring": "",
            "service_tel": "",
            "delinquency": ""
        }
        if not user_id:
            return res_data

        where = and_(
            AfDetailTag.user_id == user_id
        )
        res = db.session.query(AfDetailTag).filter(where).order_by(AfDetailTag.id.desc()).first()

        if res is not None:
            res_data["advertis"] = res.advertis
            res_data["express"] = res.express
            res_data["harass"] = res.harass
            res_data["house_agent"] = res.house_agent
            res_data["cheat"] = res.cheat
            res_data["company_tel"] = res.company_tel
            res_data["invite"] = res.invite
            res_data["taxi"] = res.taxi
            res_data["education"] = res.education
            res_data["insurance"] = res.insurance
            res_data["ring"] = res.ring
            res_data["service_tel"] = res.service_tel
            res_data["delinquency"] = res.delinquency

        return res_data