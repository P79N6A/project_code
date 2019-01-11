# -*- coding: utf-8 -*-
from multiprocessing import Pool
import os
from time import sleep

from lib.logger import logger
from .base_command import BaseCommand
from service import DetailAnalysis
from model.open import OpenJxlStat
from model.analysis import DetailList
from model.analysis import ReverseDetailList

class DetailCommand(BaseCommand):
    def __init__(self):
        self.worker = 10
        super(DetailCommand, self).__init__()

    def mulrundetail1(self,id):
        jxlData = OpenJxlStat().getData(id)
        if jxlData is None:
            print("there is nothing data to deal with")
        # 获取聚信立报告并分析
        try:
            phone = jxlData.phone
            if not str(phone).isdigit():
                print("the phone must be a number: %s" % phone)

            detailData = OpenJxlStat().getByPhone(phone)
            if detailData is None:
                print("the phone does not exist in jxlstat : %s" % phone)

            # 2.获取通话详情信息
            url = detailData.get('detail_url')
            detail_data = OpenJxlStat().getDetail(url)
            if detail_data is None:
                print("json_url:%s can't get detail_data" % (url))

            # 3. 分析详单
            analisis_res = DetailAnalysis(detail_data)._analysis()
            if analisis_res is None:
                print("mobile:%s can't get analisis_res" % (phone))
            # 4. 详单数据入库
            print(analisis_res)
        except Exception as e:
            logger.error("analysis fail: %s" % e)
            return False

    #多线程处理数据
    def mulrundetail(self):
        start_id = self.__getStartId()
        end_id = self.__getEndId(start_id)
        try:
            logger.error('parent pid %s:', os.getpid())
            interval = self.__interval(start_id, end_id)
            pool = Pool(processes=self.worker)
            for i in interval:
                result = pool.apply_async(self._stepData,(i,))
            pool.close()
            pool.join()
            print(result.get())
            if result.successful():
                print('successful')
            else:
                print('fail')
        except Exception as e:
            print(e)

    def __getStartId(self):
        f = open('./util/detailId.txt','r')
        start_id = f.read()
        f.close()
        return int(start_id)
    
    def __getEndId(self, start_id):
        end_id = int(start_id)+10
        max_id = OpenJxlStat().getMaxId()
        if end_id > max_id:
            end_id = max_id

        f = open('./util/detailId.txt', 'w')
        f.write(str(end_id))
        f.close()
        return end_id

    def __interval(self, startId, endId):
        arrSlice = []
        interval = endId - startId
        baseNum = int(interval/self.worker)

        for i in range(1,self.worker+1):
            step = []
            start = startId + (i-1)*baseNum
            step.append(start)
            end = startId + i*baseNum
            if(i == self.worker):
                end = endId
            step.append(end)
            arrSlice.append(step)
        return arrSlice

    def _stepData(self,step):
        for id in range(step[0],step[1]+1):
            # print(id)
            # sleep(10)
            self._rundetail(id)
    #单线程处理数据
    def runDetail(self):
        start_id = self.__getStartId()
        end_id = self.__getEndId(start_id)
        suc_num = 0
        try:
            for i in range(start_id, end_id):
                run_res = self._rundetail(i)
                if run_res:
                    suc_num = suc_num +1
            print("success number is %s" % suc_num)
        except Exception as e:
            print(e)

    def _rundetail(self,id):
        # logger.error('child pid %s:', os.getpid())
        #获取开放平台聚信立信息
        jxlData = OpenJxlStat().getData(id)
        if jxlData is None:
            print("there is nothing data to deal with")
            return None
        #获取聚信立报告并分析
        try:
            phone = jxlData.phone
            if not str(phone).isdigit():
                logger.error("the phone must be a number: %s" % phone)
                return False

            detailData = OpenJxlStat().getByPhone(phone)
            if detailData is None:
                logger.error("the phone does not exist in jxlstat : %s" % phone)
                return False

            #2.获取通话详情信息
            url = detailData.get('detail_url')
            detail_data = OpenJxlStat().getDetail(url)
            if detail_data is None:
                logger.error("json_url:%s can't get detail_data" % (url))
                return False

            #3. 分析详单
            analisis_res = DetailAnalysis(detail_data)._analysis()
            if analisis_res is None:
                logger.error("mobile:%s can't get analisis_res" % (phone))
                return False
            #4. 详单数据入库
            # print(analisis_res)
            save_res = DetailList().saveDetailList(analisis_res,jxlData)
            re_save_res = ReverseDetailList().saveDetailList(analisis_res, jxlData)
            return True
        except Exception as e:
            logger.error("analysis fail: %s" % e)
            return False
