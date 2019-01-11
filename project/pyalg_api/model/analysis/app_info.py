# -*- coding: utf-8 -*-
from lib.application import db
from model.base_model import BaseModel

class AppInfo(db.Model, BaseModel):
    # 指定数据库
    __bind_key__ = 'analysis_repertory'
    __tablename__ = 'app_info'

    # table model
    id = db.Column(db.Integer, primary_key=True)
    app_id = db.Column(db.Integer, nullable=False)
    app_name = db.Column(db.String(100), index=True, nullable=False, server_default=db.FetchedValue())
    app_package = db.Column(db.String(100), index=True, nullable=False, server_default=db.FetchedValue())
    first_label = db.Column(db.String(50), index=True, nullable=False, server_default=db.FetchedValue())
    second_lable = db.Column(db.String(50), index=True, nullable=False, server_default=db.FetchedValue())
    company = db.Column(db.String(100), nullable=False, server_default=db.FetchedValue())
    app_version = db.Column(db.String(32), nullable=False, server_default=db.FetchedValue())
    publish_date = db.Column(db.String(32), nullable=False, server_default=db.FetchedValue())
    down_count = db.Column(db.String(32), nullable=False, server_default=db.FetchedValue())
    comment_rate = db.Column(db.String(32), nullable=False, server_default=db.FetchedValue())
    comment_person = db.Column(db.String(32), nullable=False, server_default=db.FetchedValue())
    app_size = db.Column(db.String(32), nullable=False, server_default=db.FetchedValue())
    down_from = db.Column(db.SmallInteger, nullable=False, server_default=db.FetchedValue())
    create_time = db.Column(db.Date, index=True)

    def getAppByPkgList(self, pkgList):
        data = {}
        if len(pkgList) <= 0:
            return data
        appList =  db.session.query(AppInfo.down_from, AppInfo.app_package).filter(AppInfo.app_package.in_(pkgList)).all()
        if appList is not None and len(appList) > 0:
            for i in appList:
                each = data[i.app_package] if data.get(i.app_package, None) is not None else []
                each.append(i.down_from)
                data[i.app_package] = each
        return data

    def batchInsert(self, insertApp):
        db.session.execute(self.__table__.insert(), insertApp)
        db.session.commit()

    def getAppByAppid(self, appidList):
        if len(appidList) <= 0:
            return []
        appList = db.session.query(AppInfo).filter(AppInfo.app_id.in_(appidList)).all()
        if appList is None or len(appList) == 0:
            return []
        data = []
        for appInfo in appList:
            each = {}
            each['id'] = appInfo.id
            each['app_id'] = appInfo.app_id
            each['app_name'] = appInfo.app_name
            each['app_package'] = appInfo.app_package
            each['first_label'] = appInfo.first_label
            each['second_lable'] = appInfo.second_lable
            each['company'] = appInfo.company
            each['app_version'] = appInfo.app_version
            each['publish_date'] = appInfo.publish_date
            each['down_count'] = appInfo.down_count
            each['comment_rate'] = appInfo.comment_rate
            each['comment_person'] = appInfo.comment_person
            each['app_size'] = appInfo.app_size
            each['down_from'] = appInfo.down_from
            each['create_time'] = appInfo.create_time
            data.append(each)
        return data
