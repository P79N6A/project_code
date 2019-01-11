from .base import Base
from lib.application import db
from lib.logger import logger
from datetime import datetime
import os


class TagInfo(db.Model, Base):
    # 指定数据库
    __bind_key__ = 'xhh_antifraud'
    __tablename__ = 'tag_info_list'
    id = db.Column(db.BigInteger, primary_key=True)
    phone = db.Column(db.String(20), unique=True)
    source = db.Column(db.Integer)
    tag_type = db.Column(db.String(255))
    status = db.Column(db.SmallInteger, default=0)
    type = db.Column(db.SmallInteger, nullable=False)
    modify_time = db.Column(db.DateTime, nullable=False)
    create_time = db.Column(db.DateTime, nullable=False)

    def addResult(self, dict_data, source=100):
        try:
            if not dict_data:
                return False
            self.phone = dict_data.get('phone')
            self.source = source
            self.tag_type = dict_data.get('tag_type')
            self.status = dict_data.get('status', 0)
            self.type = 1
            self.modify_time = datetime.now()
            self.create_time = datetime.now()
            res = db.session.query(TagInfo).filter(TagInfo.phone == self.phone).first()
            tags = dict_data['tag_type']
            for i in tags:
                if i == '其他' or i == '其它':
                    tags.remove(i)
            if len(tags):
                if res is not None:
                    res.type = 2
                    tag_list = res.tag_type.split(',')  # 列表
                    for i in tags:
                        if i not in tag_list:
                            tag_list.append(i)
                    res.tag_type = ','.join(tag_list)
                    res.modify_time = datetime.now()
                    db.session.commit()
                else:
                    self.tag_type = ','.join(tags)
                    self.add()
                    db.session.commit()
                logger.info('存储到数据库的手机号%s的标识是%s' % (self.phone, self.tag_type))
        except Exception as e:
            logger.error("TagInfo-addOne:%s" % e)
            return False
