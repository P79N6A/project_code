import datetime
import time
import calendar


class timeHelper():

    # 获取时间戳, seconds为相距当前时间秒数
    @classmethod
    def getTimestamp(cls, seconds = 0):
        return time.time() + seconds

    # 将格式化后时间转化为时间戳
    @classmethod
    def datetime2Timestamp(cls, datetime):
        datetime = str(datetime)
        return int(time.mktime(time.strptime(datetime, "%Y-%m-%d %H:%M:%S")))

    # 获取格式化后的日期时间, withTime为格式化样式选项, timestamp为时间戳
    @classmethod
    def getFormatDate(cls, withTime = 'none', timestamp = 0):
        if timestamp == 0:
            timestamp = time.time()

        timeObj = time.localtime(timestamp)
        if withTime == 'now':
            return time.strftime("%Y-%m-%d %H:%M:%S", timeObj)
        elif withTime == 'start':
            return time.strftime("%Y-%m-%d 00:00:00", timeObj)
        elif withTime == 'end':
            return time.strftime("%Y-%m-%d 23:59:59", timeObj)
        elif withTime == 'none':
            return time.strftime("%Y-%m-%d", timeObj)
        else:
            return time.strftime("%Y-%m-%d", timeObj)

    # 获取今天是周几
    @classmethod
    def getWeekNum(cls):
        return datetime.datetime.now().isoweekday()





    # 返回本月的详细时间
    def mouth_time_now(request):
        tomouth = []
        # 获取查询的月份的起止时间
        date = mytime.tomouth(request)
        # 获取最后一天的日期
        day = int(datetime.datetime.strftime(date[1],'%d'))
        # 循环日期
        for i in range(1,day+1):
            if i <10:
                # 获取本月的每天的开始时间
                start_day = str(date[0])[:8] +'0'+ str(i) + str(date[0])[10:]
                start_day = datetime.datetime.strptime(start_day, '%Y-%m-%d %H:%M:%S')
                # 获取本月每天的结束时间
                end_day = str(date[1])[:8] +'0'+str(i) + str(date[1])[10:]
                end_day = datetime.datetime.strptime(end_day, "%Y-%m-%d %H:%M:%S")
                tomouth.append([start_day, end_day, i])
            else:
                # 获取本月的每天的开始时间
                start_day = str(date[0])[:8] + str(i) + str(date[0])[10:]
                start_day = datetime.datetime.strptime(start_day, '%Y-%m-%d %H:%M:%S')
                # 获取本月每天的结束时间
                end_day = str(date[1])[:8] + str(i) + str(date[1])[10:]
                end_day = datetime.datetime.strptime(end_day, "%Y-%m-%d %H:%M:%S")
                tomouth.append([start_day, end_day,i])
        # 返回list列表
        return tomouth

    # 返回今天时间列表
    def day_time_now(request):
        # 获取查询天的起止时间
        date = mytime.today(request)
        today = []
        # 循环24小时
        for i in range(0,24):
            # 获取每小时开始时间
            start_day = str(date[0])[:11] + str(i) + str(date[0])[13:]
            start_day = datetime.datetime.strptime(start_day, '%Y-%m-%d %H:%M:%S')
            # 获取每小时结束时间
            end_day = str(date[1])[:11] + str(i) + str(date[1])[13:]
            end_day = datetime.datetime.strptime(end_day, '%Y-%m-%d %H:%M:%S')
            # 组装成列表
            today.append([start_day, end_day,i+1])
        # 返回今天时间列表
        return today

    # 返回今天日期
    def daynow(request):
        # 获取查询的日期
        date = mytime.get_time(request)
        # 返回日期
        if date:
            now = date[0][:10]
            return now
        else:
            now = time.strftime("%Y-%m-%d", time.localtime())
            return now


    # 返回当前日期小时
    def housenow():
        now = time.strftime("%Y-%m-%d_%H", time.localtime())
        return now

    # 返回今天日期
    def dayhournow(request):
        # 获取查询的日期
        date = mytime.get_time(request)
        # 返回日期
        if date:
            now = date[0][:13]
            return now
        else:
            now = time.strftime("%Y-%m-%d_%H", time.localtime())
            return now




    # 返回当前月份
    def mouthnow(request):
        # 获取查询的月份
        date = mytime.get_time(request)
        # 返回查询月份
        if date:
            now = date[0][:7]
            return now
        else:
            now = time.strftime("%Y-%m", time.localtime())
            return now


    # 返回今天开始时间与结束时间
    def today(request):
        date = mytime.get_time(request)
        if date:
            # 获取今天开始时间
            start_day = datetime.datetime.strptime(date[0], '%Y-%m-%d %H:%M:%S')
            end_day = date[1][0:11]+'23'+date[1][13:]
            # 获取今天的结束时间
            end_day = datetime.datetime.strptime(end_day, '%Y-%m-%d %H:%M:%S')
            # 组装成列表
            today = [start_day, end_day]
        else:
            # 获取今天开始时间
            startday = time.strftime("%Y-%m-%d 00:00:00", time.localtime())
            start_day = datetime.datetime.strptime(startday, '%Y-%m-%d %H:%M:%S')
            # 获取今天的结束时间
            endday = time.strftime("%Y-%m-%d 23:59:59", time.localtime())
            end_day = datetime.datetime.strptime(endday, '%Y-%m-%d %H:%M:%S')
            # 组装成列表
            today = [start_day,end_day]
        # 返回今天时间列表
        return today

    # 返回本月时间
    def tomouth(request):
        date = mytime.get_time(request)
        if date:
            start_mouth = date[0][0:8]+'01'+date[0][10:]
            # 获取本月开始时间
            start_mouth = datetime.datetime.strptime(start_mouth, '%Y-%m-%d %H:%M:%S')
            # 获取年月
            year = int(date[0][:3])
            mouth = int(date[0][5:7])
            # 获取这个月的最后一天
            end_day = str(calendar.monthrange(year,mouth)[1])
            # 拼接最后一天日期
            end_mouth = date[1][:8]+end_day+" 23:59:59"
            end_mouth = datetime.datetime.strptime(end_mouth, '%Y-%m-%d %H:%M:%S')
            # 组装成列表
            tomouth = [start_mouth, end_mouth]
            return tomouth
        else:
            # 获取本月的开始时间
            start_mouth = time.strftime("%Y-%m-01 00:00:00", time.localtime())
            start_mouth = datetime.datetime.strptime(start_mouth, '%Y-%m-%d %H:%M:%S')
            # 获取本月的结束时间
            end_mouth = time.strftime("%Y-%m-%d 23:59:59", time.localtime())
            end_mouth = datetime.datetime.strptime(end_mouth, '%Y-%m-%d %H:%M:%S')
            tomouth = [start_mouth, end_mouth]
            # 返回list列表
            return tomouth


    # 返回今天开始时间与结束时间
    def tohour(request):
        date = mytime.get_time(request)
        if date:
            # 获取今天开始时间
            start_day = datetime.datetime.strptime(date[0], '%Y-%m-%d %H:%M:%S')
            # 获取今天的结束时间
            end_day = datetime.datetime.strptime(date[1], '%Y-%m-%d %H:%M:%S')
            # 组装成列表
            today = [start_day, end_day]
        else:
            # 获取今天开始时间
            startday = time.strftime("%Y-%m-%d %H:00:00", time.localtime(time.time() - 60 * 60))
            start_day = datetime.datetime.strptime(startday, '%Y-%m-%d %H:%M:%S')
            # 获取今天的结束时间
            endday = time.strftime("%Y-%m-%d %H:00:00", time.localtime())
            end_day = datetime.datetime.strptime(endday, '%Y-%m-%d %H:%M:%S')
            # 组装成列表
            today = [start_day,end_day]
        # 返回今天时间列表
        return today





    # 从前台获取数据
    def get_time(request):
        year = request.GET.get('YYYY')
        mouth = request.GET.get('MM')
        day = request.GET.get('DD')
        # 判断是否为空
        if year == None or day == None or mouth == None or year == '' or day == '' or mouth == '':
            return False
        else:
            # 返回查询的日期开始时间与结束时间
            startday = str(year) + '-' + str(mouth) + '-' + str(day) + ' ' + '00:00:00'
            endday = str(year) + '-' + str(mouth) + '-' + str(day) + ' ' + '00:59:59'
            return [startday, endday]





    def get_an_hours_before(self):
        now = time.strftime("%Y-%m-%d %H:00:00", time.localtime())
        before = time.strftime("%Y-%m-%d %H:00:00",time.localtime(time.time() - 60*60))
        return {'now':now,'before':before}

    def peanut_browse_date(self,date):
        if date == None or date == '' or date == 'None':
            datestart = time.strftime("%Y-%m-%d %H:00:00", time.localtime())
            dateend = time.strftime("%Y-%m-%d %H:59:59", time.localtime())
            datestart = datetime.datetime.strptime(datestart, "%Y-%m-%d %H:%M:%S")
            dateend = datetime.datetime.strptime(dateend, "%Y-%m-%d %H:%M:%S")
            return {'datestart':datestart,'dateend':dateend}
        else:
            date = date[:13]
            datestart = datetime.datetime.strptime(date + ":00:00", "%Y-%m-%d %H:%M:%S")
            dateend = datetime.datetime.strptime(date + ":59:59", "%Y-%m-%d %H:%M:%S")
            return {'datestart': datestart, 'dateend': dateend}

