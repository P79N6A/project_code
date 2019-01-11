# 加入定时任务组件
from logchart.timedTask import *

from django.contrib import admin
from django.urls import path,include
from apps.logchart.views.user import user
from apps.logchart.views.index import index
from apps.logchart.views.monitor import monitor
from apps.logchart.views.peanut import peanut
from apps.logchart.views.yiyiyuan import yiyiyuan
from apps.logchart.views.peanutPage import peanutPage
from apps.logchart.views.yiyiyuanPage import yiyiyuanPage

urlpatterns = [
    path('', index.index),
    path('index/', index.index),
    path('admin/', admin.site.urls),
    path('monitor/', monitor.index),
    path('clear/', index.clearData),

    path('login/',user.login),
    path('logout/',user.logout),

    path('peanut/', peanut.index),
    path('peanut/register', peanut.register),
    path('peanut/bid', peanut.bid),
    path('peanut/subscribe', peanut.subscribe),
    path('peanut/browse', peanut.browse),
    path('peanut/pagelist', peanutPage.pagelist),
    path('peanut/addPageName', peanutPage.addPageName),
    path('peanut/editPageName', peanutPage.editPageName),

    path('yiyiyuan/', yiyiyuan.index),
    path('yiyiyuan/register', yiyiyuan.register),
    path('yiyiyuan/browse', yiyiyuan.browse),
    path('yiyiyuan/pagelist', yiyiyuanPage.pagelist),
    path('yiyiyuan/addPageName', yiyiyuanPage.addPageName),
    path('yiyiyuan/editPageName', yiyiyuanPage.editPageName),
]

