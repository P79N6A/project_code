# -*- coding: utf-8 -*-
from flask_script import Manager
from commands import RunCommand
from commands import WsmCommand
from commands import DetailCommand
from commands import OrientCommand
from commands import GremlinCommand
from commands import MaillistCommand
from commands import MobilefileCommand
from commands import FileCommand
from commands import DatarepairCommand
from commands import ColonyCommand
from commands import ColonylistCommand
#from commands import ColonymoreCommand
#from commands import NeojCommand

from commands import DetailimportCommand
from commands import DetailedgeCommand
from commands import DetaildownCommand
from commands import DetailDownSeCommand
from commands import DetaildownvertexCommand
from commands import DetailDownSeVertexCommand
from commands import DetaildownEdgeCommand
from commands import DetailDownSeEdgeCommand
#ip手机号
from commands import IpdownCommand
from commands import IpdownSeCommand
from commands import IpdownvertexCommand
from commands import IpDownSeVertexCommand
from commands import IpdownEdgeCommand
from commands import IpDownSeEdgeCommand

from commands import TestinsertCommand
from commands import Neo4jcsvCommand

# from commands import RelationCommand
from commands.indirect_relation import IndirectRelation
from lib.application import app

manager = Manager(app)


#读数据库
@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def runorient(start_time, end_time):
    # python script.py runorient -s 2017-05-08 -e 2017-05-09
    return OrientCommand().runData(start_time, end_time)

@manager.command
def rungremlin():
    # python script.py rungremlin
    return GremlinCommand().runData()

#通讯录下载
@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def runmaillist(start_time, end_time):
    # python script.py runmaillist -s 2018-03-09 -e 2018-03-10
    return MaillistCommand().runData(start_time, end_time)
#运行下载的文件
@manager.option('-s', '--date_time', dest='date_time', default=None)
def runmobilefile(date_time):
    # python script.py runmobilefile -s 2018-03-09
    return MobilefileCommand().runData(date_time)

#批量运行
@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def runfile(start_time, end_time):
    # python script.py runfile -s 2018-03-09 -e 2018-03-10
    return FileCommand().runData(start_time, end_time)

#间接关系
@manager.command
def relation():
    #python script.py relation
    return IndirectRelation().rundata()

#修改一天的数据
@manager.option('-s', '--date_time', dest='date_time', default=None)
def datarepair(date_time):
    # python script.py datarepair -s 2018-03-09
    return DatarepairCommand().runData(date_time)

#集群测试
@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def colonyorientdb(start_time, end_time):
    # python script.py colonyorientdb -s 2018-03-09 -e 2018-03-10
    return ColonyCommand().runData(start_time, end_time)

#集群增加缓存
@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def colonylistssdb(start_time, end_time):
    # python script.py colonylistssdb -s 2017-05-08 -e 2017-05-09
    return ColonylistCommand().runData(start_time, end_time)

'''
#集群增加缓存
@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def colonymore(start_time, end_time):
    # python script.py colonymore -s 2017-05-08 -e 2017-05-09
    return ColonymoreCommand().runData(start_time, end_time)

#neo4j
@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def Neoj(start_time, end_time):
    # python script.py Neoj -s 2017-05-08 -e 2017-05-09
    return NeojCommand().runData(start_time, end_time)
'''
#通讯详单顶点导入
@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def detailimport(start_time, end_time):
    # python script.py detailimport -s 2017-05-08 -e 2017-05-09
    return DetailimportCommand().runData(start_time, end_time)

#通讯详单创建边缘
@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def detailedge(start_time, end_time):
    # python script.py detailedge -s 2017-05-08 -e 2017-05-09
    return DetailedgeCommand().runData(start_time, end_time)


#===========================1下载详单文件========================================
#通讯详单下载
@manager.option('-s', '--date_time', dest='date_time', default=None)
def detaildown(date_time):
    # python script.py detaildown -s 2017-05-08
    return DetaildownCommand().runData(date_time)


#时间区间通讯详单下载
@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def detaildownse(start_time, end_time):
    # python script.py detaildownse -s 2017-05-08 -e 2017-05-09
    return DetailDownSeCommand().runData(start_time, end_time)

#===========================1详单创建顶点==========================================
#一天详单文件创建顶点
@manager.option('-s', '--date_time', dest='date_time', default=None)
def detaildownvertex(date_time):
    # python script.py detaildownvertex -s 2017-05-08
    return DetaildownvertexCommand().runData(date_time)

#时间区间详单文件创建顶点
@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def detaildownsevertex(start_time, end_time):
    # python script.py detaildownsevertex -s 2017-05-08 -e 2017-05-09
    return DetailDownSeVertexCommand().runData(start_time, end_time)
#===========================1详单创建边缘==========================================
#一天详单文件创建边缘
@manager.option('-s', '--date_time', dest='date_time', default=None)
def detaildownedge(date_time):
    # python script.py detaildownedge -s 2017-05-08
    return DetaildownEdgeCommand().runData(date_time)

#时间区间详单文件创建边缘
@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def detaildownseedge(start_time, end_time):
    # python script.py detaildownseedge -s 2017-05-08 -e 2017-05-09
    return DetailDownSeEdgeCommand().runData(start_time, end_time)

#===========================2载下通过ip找到的手机号==================================
#下载一天通过ip找到的手机号
@manager.option('-s', '--date_time', dest='date_time', default=None)
def ipdown(date_time):
    # python script.py ipdown -s 2017-05-08
    return IpdownCommand().runData(date_time)

#时间区间下载一天通过ip找到的手机号
@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def ipdownse(start_time, end_time):
    # python script.py ipdownse -s 2017-05-08 -e 2017-05-09
    return IpdownSeCommand().runData(start_time, end_time)

#============================2ip创建顶点=========================================
#一天ip文件创建顶点
@manager.option('-s', '--date_time', dest='date_time', default=None)
def ipdownvertex(date_time):
    # python script.py ipdownvertex -s 2017-05-08
    return IpdownvertexCommand().runData(date_time)

#时间区间ip文件创建顶点
@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def ipdownsevertex(start_time, end_time):
    # python script.py ipdownsevertex -s 2017-05-08 -e 2017-05-09
    return IpDownSeVertexCommand().runData(start_time, end_time)

#=============================2ip创建边缘========================================
#一天ip文件创建边缘
@manager.option('-s', '--date_time', dest='date_time', default=None)
def ipdownedge(date_time):
    # python script.py ipdownedge -s 2017-05-08
    return IpdownEdgeCommand().runData(date_time)

#时间区间ip文件创建边缘
@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def ipdownseedge(start_time, end_time):
    # python script.py ipdownseedge -s 2017-05-08 -e 2017-05-09
    return IpDownSeEdgeCommand().runData(start_time, end_time)

#=====================================================================


#用于测试
@manager.option('-s', '--loop_num', dest='loop_num', default=None)
def testinsert(loop_num):
    # python script.py testinsert -s 10
    return TestinsertCommand().runData(loop_num)


#====================================================================
#保存csv
@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def neo4jcsv(start_time, end_time):
    # python script.py neo4jcsv -s 2017-05-08 -e 2017-05-09
    return Neo4jcsvCommand().runData(start_time, end_time)


if __name__ == '__main__':
    manager.run()
