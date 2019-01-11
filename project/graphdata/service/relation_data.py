# -*- coding: utf-8 -*-
'''
间接匹配(jaccard)
'''
from datetime import datetime

from lib.logger import logger
from model.relation_match import RelationMatch

from model.antifraud import AfJcardMatch


class RelationData(object):
    def __init__(self, relation):
        self.aid = relation.aid
        self.user_id = relation.user_id
        self.request_id = relation.id
        self.aid = relation.aid
        self.loan_id = relation.loan_id
        self.__init_obj()

    def __init_obj(self):
        # db类
        self.dbRelation = None

    def runRelation(self):
        # 1. 获取relation数据
        self.dbRelation = AfJcardMatch().getRelationData(self.user_id,self.request_id,self.aid)
        if self.dbRelation is None:
            logger.error("reques_id:%s db中没有纪录" % self.request_id)
            raise Exception(1002, "没有纪录")

        # 2. relation相关数据
        oRelationMatch = RelationMatch(self.dbRelation)
        dict_data = oRelationMatch.run()
        dict_data = self.__mergeData(self.aid, self.user_id, self.request_id, self.loan_id, dict_data)
        return dict_data

    def runtestRelation(self, user,url):
        # 1. 获取relation数据
        # self.dbRelation = AfJcardMatch().getRelationData(self.user_id,self.request_id,self.aid)
        # if self.dbRelation is None:
        #     logger.error("reques_id:%s db中没有纪录" % self.request_id)
        #     raise Exception(1002, "没有纪录")
        self.dbRelation = {'jcard_result':url,'request_id':'0000'}
        # 2. relation相关数据
        oRelationMatch = RelationMatch(self.dbRelation)
        dict_data = oRelationMatch.run()
        dict_data = self.__mergeData(self.aid, user[0], '0000', user[2], dict_data)
        return dict_data

    def __mergeData(self, aid, user_id, request_id, loan_id, dict_data):
        ''' 合并 user_id '''
        if dict_data is None:
            return {}

        dict_data['aid'] = aid
        dict_data['user_id'] = user_id
        dict_data['request_id'] = request_id
        dict_data['loan_id'] = loan_id
        dict_data['create_time'] = datetime.now()

        return dict_data


