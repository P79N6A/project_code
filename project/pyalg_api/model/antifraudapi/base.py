# -*- coding: utf-8 -*-
from lib.logger import logger
from model.base_model import BaseModel


class Base(BaseModel):
    # 反欺诈数据部分基类, 且仅限数据部分

    def init(self):
        # 初始化
        super(BaseModel,self).__init__()

    def addData(self, dict_data):
        try:
            if dict_data is None or len(dict_data) == 0:
                return False
            self.addByDict(dict_data)
            return True
        except Exception as e:
            logger.error(self.__class__.__name__ + " addOne:%s" % e)
            return False

