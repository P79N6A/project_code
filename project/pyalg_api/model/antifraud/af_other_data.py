# -*- coding: utf-8 -*-
from lib.application import db
from model.base_model import BaseModel
from lib.logger import logger
from datetime import datetime, timedelta

class AfOtherData(db.Model, BaseModel):
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'af_other_data'

    id = db.Column(db.BigInteger, primary_key=True)
    user_id = db.Column(db.BigInteger, nullable=False, server_default='0')
    learning_letter = db.Column(db.Integer, nullable=False, server_default='0')
    learning_letter_contrast = db.Column(db.String(32), nullable=False, server_default='')
    ocial_security = db.Column(db.Integer, nullable=False, server_default='0')
    ocial_security_contrast = db.Column(db.String(32), nullable=False, server_default='')
    accumulation_fund = db.Column(db.Integer, nullable=False, server_default='0')
    accumulation_fund_contrast = db.Column(db.String(32), nullable=False, server_default='')
    create_time = db.Column(db.DateTime, nullable=False)
    modify_time = db.Column(db.DateTime, nullable=False)

    '''
    通过user_id获取数据
    '''
    def getDataForUserId(self, user_id):
        if user_id is None:
            return False
        return db.session.query(AfOtherData).filter(AfOtherData.user_id==user_id).first()

    def getCount(self, user_id):
        if user_id is None:
            return False
        return db.session.query(AfOtherData).filter(AfOtherData.user_id == user_id).count()

    '''
    保存数据
    '''
    def saveResources(self, data):
        try:
            self.user_id = data.get('user_id')
            self.learning_letter = data.get('other_data').get('learning_letter_field').get("submission")
            self.learning_letter_contrast = data.get('other_data').get('learning_letter_field').get("contrast")
            self.ocial_security = data.get('other_data').get('social_security_field').get("submission")
            self.ocial_security_contrast = data.get('other_data').get('social_security_field').get("contrast")
            self.accumulation_fund = data.get('other_data').get('accumulation_fund_field').get("submission")
            self.accumulation_fund_contrast = data.get('other_data').get('accumulation_fund_field').get("contrast")
            self.create_time = datetime.now()
            self.modify_time = datetime.now()
            self.add()
            db.session.commit()
            return True
        except AttributeError as error:
            logger.info("af_other_data: 记录失败：%s" % error)
            print(error)
            return False

        except Exception as error:
            logger.info("af_other_data: 记录失败：%s" % error)
            print(error)
            return False

    def updateResources(self, source_data, data):
        try:
            source_data.learning_letter = data.get('other_data').get('learning_letter_field').get("submission")
            source_data.learning_letter_contrast = data.get('other_data').get('learning_letter_field').get("contrast")
            source_data.ocial_security = data.get('other_data').get('social_security_field').get("submission")
            source_data.ocial_security_contrast = data.get('other_data').get('social_security_field').get("contrast")
            source_data.accumulation_fund = data.get('other_data').get('accumulation_fund_field').get("submission")
            source_data.accumulation_fund_contrast = data.get('other_data').get('accumulation_fund_field').get("contrast")
            source_data.modify_time = datetime.now()
            db.session.commit()
            return True
        except AttributeError as error:
            logger.info("af_other_data: 修改失败：%s" % error)
            print(error)
            return False

        except Exception as error:
            logger.info("af_other_data: 修改失败：%s" % error)
            print(error)
            return False


