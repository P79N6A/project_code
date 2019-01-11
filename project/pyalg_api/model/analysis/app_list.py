# -*- coding: utf-8 -*-
from lib.application import db
from .base import Base

class AppList(db.Model, Base):
    # 指定数据库
    __bind_key__ = 'analysis_repertory'
    __tablename__ = 'app_list'

    # table model
    id = db.Column(db.BigInteger, primary_key=True)
    app_name = db.Column(db.String(100), nullable=False, server_default=db.FetchedValue())
    app_package = db.Column(db.String(100), nullable=False, server_default=db.FetchedValue())
    create_time = db.Column(db.DateTime, index=True)

    def getAppByPkgList(self, pkgList):
        data = {}
        if len(pkgList) <= 0:
            return data
        appList =  db.session.query(AppList.id, AppList.app_package).filter(AppList.app_package.in_(pkgList)).all()
        if appList is not None and len(appList) > 0:
            for i in appList:
                data[i.id] = i.app_package
        return data

    def batchInsert(self, insertApp, diffAppList):
        db.session.execute(self.__table__.insert(), insertApp)
        db.session.commit()
        return self.getAppByPkgList(diffAppList)

    def getAppByAppid(self, appidList):
        if len(appidList) <= 0:
            return {}
        appList = db.session.query(AppList).filter(AppList.id.in_(appidList)).all()
        if appList is None or len(appList) == 0:
            return {}
        data = {}
        for appInfo in appList:
            each = {}
            each['app_id'] = appInfo.id
            each['app_name'] = appInfo.app_name
            each['app_package'] = appInfo.app_package
            data[appInfo.id] = each
        return data
