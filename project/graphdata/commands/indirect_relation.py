import pandas as pd
from util.ssdb import SsdbObject
import csv
from model.analysis.reverse_address_list import ReverseAddressList
import os
import time
class IndirectRelation:
    re = ReverseAddressList()

    #读取文件 返回电话号列表
    def readFile(self):
        modeal_data = pd.read_csv("./commands/ashuju.csv",usecols=[1],encoding="utf-8").values
        return modeal_data

    def rundata(self):
        if not os.path.isfile("./间接数据.csv"):
            f = open("./间接数据.csv",'w')
            f.close()
        #打开一个csv文件
        csvFile2 = open('间接数据.csv', 'a+', newline='', encoding="utf-8")
        #创建表头
        da = ["目标数据","间接数据","目标创建时间","间接创建时间"]
        #创建一个writer
        writer = csv.writer(csvFile2)
        #写入表头
        writer.writerow(da)
        #获取文件长度
        num = len(self.readFile())
        # num = 10
        #起始条数
        star = 0
        #打开数据库连接
        ssdb = SsdbObject(False)
        ssdb_resources = ssdb.ssdbConnection()
        while star < num:
            # 每次获取一条电话号并转化为int
            modephone = int(self.readFile()[star:(star + 1)][0])
            # modephone = "13315931115"
            #获取通讯录
            modelist = ssdb_resources.get(modephone)
            if  modelist is not None:
                #转化为str
                modelist = bytes.decode(modelist)
                #将'替换为"
                modelist = modelist.replace("\'", "\"")
                #去除[
                modelist = modelist.replace("[","")
                # 去除]
                modelist = modelist.replace("]", "")
                # 去除,
                modelist = modelist.replace(",","")
                #去除{
                modelist = modelist.replace("{","")
                #去除}
                modelist = modelist.replace("}","")
                #在'拆分
                modelist = modelist.split("'")
                modelist = modelist[0].split("\"")
                if modelist is not None:
                    if len(modelist) != 0:
                        #移除目标手机号
                        if str(modephone) in modelist:
                            modelist.remove(str(modephone))
                        #循环通讯录
                        for i in modelist:
                            #查间接数据与时间
                            info = self.re.getCreateTimeByPhone(i,str(modephone))
                            if len(info) != 0:
                                m = len(info)
                                for i in range(m):
                                    #循环写入csv
                                    writer.writerow(info[i])
                                    csvFile2.flush()
            if star%1000 ==0:
                time.sleep(1)
                print(star%1000)
            star += 1
            print(star)

        csvFile2.close()





























