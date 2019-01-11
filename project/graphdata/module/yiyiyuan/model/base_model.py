# -*- coding: utf-8 -*-
from lib.application import db

'''
session.flush() 刷新db,但不提交
session.rollback() 回滚
session.commit() 提交
'''


class BaseModel(object):
    # 数据库基类

    def init(self):
        # 初始化
        pass

    def get_app(self):
        ''' 获取当前app'''
        return db.get_app()

    def get_engine(self):
        ''' 获取当前使用的数据库引擎'''
        myapp = self.get_app()
        dbname = self.__bind_key__
        engine = db.get_engine(myapp, dbname)
        return engine

    def execute(self, sql, params=None):
        '''封装execute'''
        engine = self.get_engine()
        return db.session.execute(sql, params, bind=engine)

    def add(self):
        db.session.add(self)
        try:
            db.session.flush()
            db.session.commit()
        except Exception:
            db.session.rollback()
            raise

    def add_all(self, lst):
        db.session.add_all(lst)

    def delete(self):
        db.session.delete(self)

    def addByDict(self, dict_data):
        for k, v in list(dict_data.items()):
            setattr(self, k, v)
        self.add()

    def row2dict(self, row):
        return row2dict(row)


def row2dict(row):
    ''' 转成字典形式'''
    if row is None:
        return None

    d = {}
    for column in row.__table__.columns:
        d[column.name] = str(getattr(row, column.name))
    return d


class DictMerge:

    ''' 字典合并'''

    def __init__(self):
        self.res = {}

    def set(self, dict_data):
        if dict_data is not None and type(dict_data) is dict:
            self.res.update(dict_data)

    def get(self):
        return self.res
