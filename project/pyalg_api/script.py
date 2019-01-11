# -*- coding: utf-8 -*-
from flask_script import Manager
from commands import RunCommand
from commands import WsmCommand
from commands import DetailCommand
from commands import RunOperatorScore
# from commands import OrientCommand
from commands import NumberCommand
from commands import SyncappCommand
from commands import AppdumpCommand

from lib.application import app

manager = Manager(app)

@manager.command
def runbase():
    # python script.py runbase
    return RunCommand().runbase()

@manager.command
def run():
    # python script.py run
    return RunCommand().index()

@manager.command
def runmatch():
    # python script.py runmatch
    return RunCommand().multimatch()

@manager.command
def runjcard():
    # python script.py runjcard
    return RunCommand().jcardmatch()

@manager.command
def runrelation():
    # python script.py runrelation
    return RunCommand().relationmatch()

@manager.option('-s', '--step', dest='step', default=0)
def runtestjcard(step):
    # python script.py runtestjcard --s 0
    return RunCommand().testjcard(step)

@manager.command
def getbyid(id):
    # python script.py getbyid --method=jxldb
    if not id.isdecimal():
        return u"请输入数字"
    return RunCommand().getbyid(id)

@manager.option('-s', '--start_time', dest='start_time', default=None)
@manager.option('-e', '--end_time', dest='end_time', default=None)
def runwsm(start_time,end_time):
    #  python script.py runwsm --s 2017-11-02 --e 2017-11-04
    #  python script.py runwsm --s 2017-11-06 --e 2017-11-08
    return WsmCommand().runwsm(start_time, end_time)

@manager.command
def rundetail():
    # python script.py rundetail
    # return DetailCommand().mulrundetail()
    return DetailCommand().runDetail()

@manager.option('-i', '--id', dest='id', default=None)
def rundetailone(id):
    # python script.py rundetailone --i 5199762
    return DetailCommand().mulrundetail1(id)
    # return DetailCommand().runDetail()

'''
@manager.command
def runorient():
    # python script.py runorient
    return OrientCommand().runorient()
'''

'''
号码标签相关特征匹配
'''
@manager.command
def numberlable():
    # python script.py numberlable
    return NumberCommand().incrMatching()


@manager.command
def run_test():
    # python script.py getbyid --method=jxldb
    return RunCommand().run_test()

@manager.command
def runSyncApp():
    # python script.py runSyncApp
    return SyncappCommand().runSyncApp()

@manager.option('-n', '--name', dest='name', default=None)
def appDump(name):
    # python script.py appDump -n app_sample_data.csv
    return AppdumpCommand().runAppDump(name)

@manager.command
def run_score():
    return RunOperatorScore().runScore()

@manager.command
def runStrategy():
    # python script.py runStrategy
    return RunCommand().run_strategy()

if __name__ == '__main__':
    manager.run()
