# -*- coding: utf-8 -*-
'''
号码标签相关特征匹配
'''

from model.antifraud.af_tag_base import AfTagBase
from .base_command import BaseCommand
from service.number_label import NumberLabel
from model.antifraud.af_address_tag import AfAddressTag
from model.antifraud.af_detail_tag import AfDetailTag

from lib.logger import logger

class NumberCommand(BaseCommand):
    otag_base = ''
    onumber_label = ''

    def __init__(self):
        self.otag_base = AfTagBase()
        self.onumber_label = NumberLabel()

    #增加通讯录的匹配
    def incrMatching(self):
        #获取号码标签基础表的数据
        tag_base = self._getTagBase()
        if not tag_base:
            return False;

        num = 0
        detail_num = 0
        for tag_info in tag_base:
            # 通讯录 start
            proportion = self.onumber_label.runMail(tag_info)
            if proportion:
                # 保存数据
                save = self._saveData(tag_info, proportion)
                # 通讯录
                if not save:
                    continue
                num += 1
            # 详单 start
            detail_proportion = self.onumber_label.runDetail(tag_info)

            if detail_proportion:
                # 保存数据
                save_detail = self._saveDetailData(tag_info, detail_proportion)
                if not save_detail:
                    continue
                # 修改锁定
                detail_num += 1

            #完成
            self.otag_base.lockTagSuccess(tag_info)


        print("通讯录条数：%s" % str(num))
        print("详单条数：%s" % str(detail_num))
        print("done!")

    '''
    获取数据并锁定
    '''
    def _getTagBase(self):
        # 1.获取数据
        data = self.otag_base.getTabData()
        data_len = len(data)
        if data_len == 0:
            print("暂无数据！")
            return False
        # 2.锁定数据
        lock_res = self.otag_base.lockTagStatus(data, 1)
        if not lock_res:
            print("锁定失败！")
            return False
        logger.info("there's %s data to tag_base with" % data_len)
        return data

    '''
    保存数据
    '''
    def _saveData(self, tag_info, proportion):
        if not proportion:
            proportion = 0

        oAfAddressTag = AfAddressTag()

        # 保存记录
        save_data = oAfAddressTag.saveData(tag_info, proportion)
        if save_data:
            return save_data
        return False

    def _saveDetailData(self, tag_info, proportion):
        if not proportion:
            proportion = 0

        oAfDetailTag = AfDetailTag()

        # 保存记录
        save_data = oAfDetailTag.saveData(tag_info, proportion)
        if save_data:
            return save_data
        return False



