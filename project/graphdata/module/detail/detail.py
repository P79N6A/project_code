# -*- coding: utf-8 -*-
'''
通话详情分析
@author: jin
'''
import pandas as pd
from datetime import datetime,timedelta
import re
import traceback
from collections import Counter


class Detail(object):

    def __init__(self, data):
        # 检查数据合法性
        self.originData = data

        try:
            self._analysis()
        except Exception as e:
            # logger here
            exstr = traceback.format_exc()
            raise Exception("detail: %s \n %s" % (e, exstr))

    def _analysis(self):
        # 分析数据
        calls = self.originData['raw_data']['members']['transactions'][0]['calls']
        pd_detail = pd.DataFrame(calls)
        pd_detail.other_cell_phone = pd_detail.other_cell_phone.str[-11:]
        pd_detail['days'] = pd_detail.start_time.str[:10]
        pd_detail = pd_detail[-pd_detail.init_type.isnull()]
        self.pd_detail = pd_detail
        # 通话时间为空
        self.pd_detail.other_cell_phone = self.pd_detail.other_cell_phone.fillna('')

        # 时间间隔:天数, 月份数
        pd_detail.start_time = self.fillnaStartTime(pd_detail.start_time)
        self.com_start_time = pd_detail.start_time.min()
        self.com_end_time = pd_detail.start_time.max()

        time_start = datetime.strptime(self.com_start_time, '%Y-%m-%d %H:%M:%S')
        time_end = datetime.strptime(self.com_end_time, '%Y-%m-%d %H:%M:%S')
        self.com_days = (time_end - time_start).days + 1
        self.com_month_num = self.com_days / 30.

        # 排行情况
        self.phone_rank = pd_detail.other_cell_phone.value_counts().sort_values(ascending=False)
        grp = pd_detail[['other_cell_phone', 'use_time']].groupby('other_cell_phone')
        self.duration_rank = grp.sum().sort_values(by='use_time', ascending=False)

    def fillnaStartTime(self, start_time):
        start_time_valid = start_time.dropna()
        if len(start_time_valid) > 0:
            start_time = start_time.fillna(start_time_valid.iloc[0])
        else:
            raise Exception("detail:start_time is empty or is not valid")
        return start_time

    def run(self):
        pd_detail = self.pd_detail
        com_days = self.com_days
        com_month_num = self.com_month_num
        ######################总通话数, 主叫数, 被叫数#############################
        temp_init_type = pd_detail.init_type
        com_count = temp_init_type.count()
        com_call = temp_init_type[temp_init_type.str.contains('主')].count()
        com_answer = temp_init_type[temp_init_type.str.contains('被')].count()
        #####

        ######################总通话时长, 主叫时长, 被叫时长#############################
        com_duration = pd_detail.use_time.sum()
        com_call_duration = pd_detail.use_time[temp_init_type.str.contains('主')].sum()
        com_answer_duration = pd_detail.use_time[temp_init_type.str.contains('被')].sum()
        #####

        # 总联系人数量
        com_people = pd_detail.other_cell_phone.drop_duplicates().count()
        ##########################按天去重, 通话次数, 主叫次数, 被叫次数##################
        # 61月均有通话行为的天数过少, 即按天去重的数量
        # 62月均有拨打电话行为的天数过少 即按天去重的数量
        # 63月均有接听电话行为的天数过少 即按天去重的数量
        temp_days = pd_detail['days']
        com_day_connect = temp_days.drop_duplicates().count()
        com_days_call = temp_days[pd_detail['init_type'].str.contains('主')].drop_duplicates().count()
        com_days_answer = temp_days[pd_detail['init_type'].str.contains('被')].drop_duplicates().count()
        #####

        # 57 日均有通话行为的时段过少（每个小时为一个时段） 57  com_hours_connect
        # 58 日均有接听电话行为的时段过少（每个小时为一个时段）   58  com_hours_answer
        # 59 日均有拨打电话行为的时段过少（每个小时为一个时段）   59  com_hours_call
        pd_detail['hours'] = pd_detail.start_time.str[:13]
        temp_hours = pd_detail['hours']
        com_hours_connect = temp_hours.drop_duplicates().count()
        com_hours_call = temp_hours[pd_detail['init_type'].str.contains('主')].drop_duplicates().count()
        com_hours_answer = temp_hours[pd_detail['init_type'].str.contains('被')].drop_duplicates().count()

        # 76 月均23点-6点通话次数过多  76  com_night_connect
        # 77 月均23点-6点通话次数比例过高    77  com_night_connect / com_count
        # 78 月均23点-6点通话分钟数过长 78  com_night_duration
        # 79 月均23点-6点通话时长比例过高    79  com_night_duration / com_duration
        temp_hours = pd_detail.hours.str[11:13]
        hour_daylight = ((temp_hours >= '06') & (temp_hours < '23'))
        hour_daynignt = hour_daylight == False

        com_night_connect = temp_hours[hour_daynignt].count()
        com_night_connect_p = float(com_night_connect) / com_count
        com_night_duration = pd_detail.use_time[hour_daynignt].sum()
        com_night_duration_p = None if com_duration == 0 else float(com_night_duration) / com_duration

        # 80 频繁联系手机号码通话次数过少（频繁的定义：前10）   80  com_offen_connect
        # 81 频繁联系手机号码通话时长过短（频繁的定义：前10）   81  com_offen_duration
        com_offen_connect = self.phone_rank[:10].sum()
        com_offen_duration = self.duration_rank[:10]['use_time'].sum()

        # 手机和电话判断
        mobile_pattern = self._getReMobile()
        is_mobile = pd_detail.other_cell_phone.str.contains(mobile_pattern)
        is_mobile = is_mobile == True

        # 68 月均有固话通话联系人过少    68  com_month_tel_people
        # 69 月均手机通话联系人过少 73  com_month_mobile_people
        com_mobile_people = pd_detail.other_cell_phone[is_mobile].drop_duplicates().count()
        com_tel_people = com_people - com_mobile_people
        #com_mobile_people, com_tel_people, com_count

        # 82 最频繁联系手机号码通话次数过少 82  com_max_mobile_connect
        # 84 最频繁通话固话号码通话次数过少 84  com_max_tel_connect
        # 83 最频繁联系手机号码通话时长过短 83  com_max_mobile_duration
        # 85 最频繁通话固话号码通话时长过短 85  com_max_tel_duration
        com_max_mobile_connect, com_max_tel_connect = self._getTopByIndex(self.phone_rank)
        com_max_mobile_duration, com_max_tel_duration = self._getTopByIndex(self.duration_rank)

        # 有效的手机号: 1.通话次数大于15次，2.为正常手机号
        mobile_rank = pd_detail.other_cell_phone[is_mobile].value_counts().sort_values(ascending=False)
        self.com_valid_15_m = mobile_rank[mobile_rank >= 15]
        com_valid_mobile = self.com_valid_15_m.count()
        # 通话次数>=15
        com_valid_all = self.phone_rank[self.phone_rank >= 15].count()

        #过去3个月被叫次数,过去3个月总通话次数,过去4-6个月被叫次数,过去4-6个月总通话次数
        mobile_pattern_other = self._getReMobile_other()
        is_mobile_other = pd_detail.other_cell_phone.str.contains(mobile_pattern)
        is_not_mobile = is_mobile_other == False

        maxday = datetime.now()
        maxtime = maxday.strftime("%Y-%m-%d %H:%M:%S")
        last3 = (maxday + timedelta(days=-90)).strftime('%Y-%m-%d %H:%M:%S')
        #last6 = (maxday + timedelta(days=-180)).strftime('%Y-%m-%d %H:%M:%S')
        #过去三个月被叫次数
        last3_answer = pd_detail.init_type[-(pd_detail.init_type.str.contains('主')) & (pd_detail.start_time>=last3) & (pd_detail.start_time < maxtime)].count()
        #过去三个月总通话次数
        last3_all = pd_detail.init_type[(pd_detail.start_time>=last3) & (pd_detail.start_time<maxtime)].count()
        # 过去3个月不是手机号通话次数
        last3_not_mobile_count = pd_detail.other_cell_phone[is_not_mobile & (pd_detail.start_time>=last3) & (pd_detail.start_time<maxtime)].count()
        #过去3-6个月被叫次数
        last6_answer = pd_detail.init_type[-(pd_detail.init_type.str.contains('主')) & (pd_detail.start_time < last3)].count()
        #过去3-6个月总通话次数
        last6_all = pd_detail.init_type[pd_detail.start_time<last3].count()
        # 过去3-6个月不是手机号通话次数
        last6_not_mobile_count = pd_detail.other_cell_phone[is_not_mobile & (pd_detail.start_time<last3)].count()
        #近3个月TOP20联系人
        top20_three = pd_detail[(pd_detail.start_time>=last3) & (pd_detail.start_time<maxtime)][['other_cell_phone','cell_phone']].groupby(['other_cell_phone'])['cell_phone'].count().reset_index(name='count').sort_values(['count','other_cell_phone'], ascending=[False,True]).head(20)

        #与近4-6个月TOP20联系人相同的数量
        top20_six = pd_detail[pd_detail.start_time<last3][['other_cell_phone','cell_phone']].groupby(['other_cell_phone'])['cell_phone'].count().reset_index(name='count').sort_values(['count','other_cell_phone'], ascending=[False,True]).head(20)

        #两组号码交集个数
        # tot_phone_num = max([top20_six['other_cell_phone'].count(),top20_three['other_cell_phone'].count()])
        tot_phone_num = len(list(set(top20_six['other_cell_phone']).union(set(top20_three['other_cell_phone']))))
        #近3个月TOP20联系人与近4-6个月TOP20联系人相同的数量
        same_phone_num = pd.merge(top20_three,top20_six,on='other_cell_phone').other_cell_phone.count()
        #关机时长
        dates = pd_detail['start_time'].sort_values(ascending=False)
        dates.index = range(len(dates))
        total_duration = []
        shutdown_duration = 0
        shutdown_duration_count = 0
        shutdown_sum_days = 0
        shutdown_max_days = 0
        shutdown_min_days = 0
        shutdown_median_days = 0
        shutdown_mode_days = 0
        for i in range(len(dates)-1):
            interval = (datetime.strptime(dates.loc[i], "%Y-%m-%d %H:%M:%S") - datetime.strptime(dates.loc[i+1], "%Y-%m-%d %H:%M:%S")).days
            if int(interval) > 0 :
                total_duration.append(interval)
        if len(total_duration) > 0:
            shutdown_duration = sum(total_duration)*86400
            shutdown_duration_count = len(total_duration)
            shutdown_sum_days = sum(total_duration)
            shutdown_max_days = max(total_duration) if total_duration else 0
            shutdown_min_days = min(total_duration) if total_duration else 0
            temp = sorted(total_duration)
            shutdown_median_days = temp[round(len(temp)/2)] if temp else 0
            shutdown_mode_days = list(dict(Counter(total_duration).most_common(1)).keys())[0]
        #todo 被叫含义修改
        dict_detail_other = {
            'last3_answer':int(last3_answer),
            'last3_all':int(last3_all),
            'last6_answer':int(last6_answer),
            'last6_all':int(last6_all),
            'same_phone_num':int(same_phone_num),
            'tot_phone_num':int(tot_phone_num),
            'last3_not_mobile_count':int(last3_not_mobile_count),
            'last6_not_mobile_count':int(last6_not_mobile_count),
            'total_duration':shutdown_duration,
            'shutdown_duration_count':shutdown_duration_count,
            'shutdown_sum_days':shutdown_sum_days,
            'shutdown_max_days':shutdown_max_days,
            'shutdown_min_days':shutdown_min_days,
            'shutdown_median_days':shutdown_median_days,
            'shutdown_mode_days':shutdown_mode_days,
        }

        dict_result = {
            # 39 最早通话时间
            'com_start_time': self.com_start_time,

            # 40 最晚通话时间
            'com_end_time': self.com_end_time,

            # 39 通话间隔,天数, 月
            'com_days': int(com_days),
            'com_month_num': float('%.2f' % com_month_num),

            # 75 手机使用历史过短    75  com_use_time
            'com_use_time': int(com_days),

            # 总通话数, 主叫数, 被叫数, 总时长, 主叫时长, 被叫时长
            'com_count': int(com_count),
            'com_call': int(com_call),
            'com_answer': int(com_answer),

            'com_duration': int(com_duration),
            'com_call_duration': int(com_call_duration),
            'com_answer_duration': int(com_answer_duration),

            # 总联系人数量
            'com_people': int(com_people),

            #-------------------- 按天去重总通话次数,主叫, 被叫
            'com_day_connect': int(com_day_connect),
            'com_days_call': int(com_days_call),
            'com_days_answer': int(com_days_answer),
            # 61月均按天通话行为
            'com_day_connect_mavg': float('%.2f' % (com_day_connect / com_month_num)),
            'com_days_call_mavg': float('%.2f' % (com_days_call / com_month_num)),
            'com_days_answer_mavg': float('%.2f' % (com_days_answer / com_month_num)),

            # 64 月均通话次数过少 com_month_connects
            # 65 月均通话时长过少 com_month_duration
            'com_month_connects': float('%.2f' % (com_count / com_month_num)),
            'com_month_duration': float('%.2f' % (com_duration / com_month_num)),
            #----------------------

            #---------------------- 按小时去重总通话次数,主叫, 被叫
            'com_hours_connect': int(com_hours_connect),
            'com_hours_call': int(com_hours_call),
            'com_hours_answer': int(com_hours_answer),
            # 57 日均按小时通话行为
            'com_hours_connect_davg': float('%.2f' % (com_hours_connect / com_days)),
            'com_hours_call_davg': float('%.2f' % (com_days_call / com_days)),
            'com_hours_answer_davg': float('%.2f' % (com_days_answer / com_days)),
            #----------------------

            #----------------------
            # 60 月均通话联系人
            'com_month_people': float('%.2f' % (com_people / com_month_num)),

            # 69 月均接听次数, 时长
            'com_month_answer': float('%.2f' % (com_answer / com_month_num)),
            'com_month_answer_duration': float('%.2f' % (com_answer_duration / com_month_num)),
            # 71月均拨打次数, 时长
            'com_month_call': float('%.2f' % (com_call / com_month_num)),
            'com_month_call_duration': float('%.2f' % (com_call_duration / com_month_num)),
            #----------------------

            #----------------------
            # 76 月均23点-6点通话
            'com_night_connect': int(com_night_connect),
            'com_night_duration': int(com_night_duration),
            'com_night_connect_mavg': float('%.2f' % (com_night_connect / com_month_num)) ,
            'com_night_duration_mavg': float('%.2f' % (com_night_duration / com_month_num)),

            # 比例
            'com_night_connect_p': float('%.2f' % com_night_connect_p),
            'com_night_duration_p': float('%.2f' % com_night_duration_p),
            #----------------------

            #----------------------
            # 频繁
            'com_offen_connect': int(com_offen_connect),
            'com_offen_duration': int(com_offen_duration),
            #----------------------

            #----------------------
            # 月均固话, 手机
            'com_mobile_people': int(com_mobile_people),
            'com_tel_people':  int(com_tel_people),
            'com_mobile_people_mavg': float('%.2f' % (com_mobile_people / com_month_num)),
            'com_tel_people_mavg':  float('%.2f' % (com_tel_people / com_month_num)),
            #----------------------

            #----------------------
            # 最频繁排名情况
            'com_max_mobile_duration':  int(com_max_mobile_duration),
            'com_max_tel_duration':  int(com_max_tel_duration),
            'com_max_mobile_connect':  int(com_max_mobile_connect),
            'com_max_tel_connect':  int(com_max_tel_connect),
            #----------------------

            # 有效手机号次数
            'com_valid_mobile':  int(com_valid_mobile),
            'com_valid_all':  int(com_valid_all),

        }
        return dict_result,dict_detail_other

        # 合并字典
        #contact_stat = self.getContactStat(com_month_num, phone_rank, duration_rank)
        # dict_result.update(contact_stat)
        # return dict_result

    def vsContact(self, contact):
        # 与亲属,常见联系人匹配
        vs = DetailVsContact(contact,
                             self.pd_detail,
                             self.phone_rank,
                             self.duration_rank,
                             self.com_month_num)
        data = vs.run()
        return data

    def vsAddress(self, pd_address):
        # 与通讯录匹配
        vs = DetailVsAddress(pd_address,
                             self.pd_detail,
                             self.phone_rank,
                             self.duration_rank,
                             self.com_valid_15_m
                             )
        return vs.run()

    def vsAuth(self, db_auth):
        # 与通讯录匹配
        vs = DetailVsAuth(db_auth, self.pd_detail)
        return vs.run()

    def vsInvest(self, db_invest_me, db_my_invest):
        # 与通讯录匹配
        vs = DetailVsInvest(db_invest_me, db_my_invest,  self.pd_detail)
        return vs.run()

    def _getReMobile(self):
        # 手机正则
        substr = '^1[2-9][0-9]\d{8}$'
        p = re.compile(substr, re.DOTALL)
        return p
    
    def _getReMobile_other(self):
        # 手机正则
        substr = '1[2-9][0-9]\d{8}'
        p = re.compile(substr, re.DOTALL)
        return p

    def _getTopByIndex(self, rank):
        # 最频繁联系排名情况
        idx_mobile = None
        idx_tel = None
        p = self._getReMobile()
        # 按排名顺序从上到下查找 , 直到手机和电话查完
        for phone in rank.index:
            is_mobile = re.search(p, phone[-11:])
            if is_mobile:
                if idx_mobile is None:
                    idx_mobile = phone
            else:
                if idx_tel is None:
                    idx_tel = phone

            if is_mobile and idx_tel:
                break

        max_mobile = rank.get(idx_mobile, 0)
        max_tel = rank.get(idx_tel, 0)
        return max_mobile, max_tel
    
    def getDistinctPhone(self):
        pd_detail = self.pd_detail
        phone_match = pd_detail.other_cell_phone.drop_duplicates()
        mobile_pattern = self._getReMobile()
        phone = phone_match.str[-11:]
        is_mobile = phone.str.contains(mobile_pattern)
        distinct_phone = phone[is_mobile]
        return list(distinct_phone)

    def runWsm(self,dict_contact_phone):
        pd_detail = self.pd_detail
        pd_detail['hour'] = pd.to_datetime(pd_detail['start_time']).apply(lambda x:x.strftime('%H'))
        pd_detail['week'] = pd.to_datetime(pd_detail['start_time']).apply(lambda x:x.weekday())
        pd_detail['month'] = pd.to_datetime(pd_detail['start_time']).apply(lambda x:x.strftime('%Y%m'))

        def is_week(x):
            x = int(x)
            if 0<=x<5:
                return 'workingday'
            elif 5<=x<=6:
                return 'holiday'

        def time_section(x):
            x = int(x)
            if  0<=x<6:
                return 'zs'
            elif 6<=x<12:
                return 'st'
            elif 12<=x<18:
                return 'te'
            elif 18<=x<24:
                return 'et'
            else:
                return None
        pd_detail['time_section'] = pd_detail['hour'].apply(time_section)
        pd_detail['is_week'] = pd_detail['week'].apply(is_week)
        #0-6  呼入次数 时长
        zs_call_in = pd_detail[pd_detail.init_type.str.contains('被',na=False) & pd_detail.time_section.str.contains('zs',na=False)][['month','use_time']].groupby(['month']).agg(['count','sum']).reset_index()
        zs_call_in.columns = ['ymonth','zs_in_times','zs_in_duration']
        #6-12 呼入次数 时长
        st_call_in = pd_detail[pd_detail.init_type.str.contains('被',na=False) & pd_detail.time_section.str.contains('st',na=False)][['month','use_time']].groupby(['month']).agg(['count','sum']).reset_index()
        st_call_in.columns = ['ymonth','st_in_times','st_in_duration']
        call_all = pd.merge(zs_call_in, st_call_in,how='outer', on=['ymonth']).fillna(0)
        #12-18 呼入次数 时长
        te_call_in = pd_detail[pd_detail.init_type.str.contains('被',na=False) & pd_detail.time_section.str.contains('te',na=False)][['month','use_time']].groupby(['month']).agg(['count','sum']).reset_index()
        te_call_in.columns = ['ymonth','te_in_times','te_in_duration']
        call_all = pd.merge(call_all, te_call_in,how='outer', on=['ymonth']).fillna(0)
        #18-24 呼入次数 时长
        et_call_in = pd_detail[pd_detail.init_type.str.contains('被',na=False) & pd_detail.time_section.str.contains('et',na=False)][['month','use_time']].groupby(['month']).agg(['count','sum']).reset_index()
        et_call_in.columns = ['ymonth','etf_in_times','etf_in_duration']
        call_all = pd.merge(call_all, et_call_in,how='outer', on=['ymonth']).fillna(0)

        #0-6 呼出次数 时长
        zs_call_out = pd_detail[pd_detail.init_type.str.contains('主',na=False) & pd_detail.time_section.str.contains('zs',na=False)][['month','use_time']].groupby(['month']).agg(['count','sum']).reset_index()
        zs_call_out.columns = ['ymonth','zs_out_times','zs_out_duration']
        call_all = pd.merge(call_all, zs_call_out,how='outer', on=['ymonth']).fillna(0)
        #6-12 呼出次数 时长
        st_call_out = pd_detail[pd_detail.init_type.str.contains('主',na=False) & pd_detail.time_section.str.contains('st',na=False)][['month','use_time']].groupby(['month']).agg(['count','sum']).reset_index()
        st_call_out.columns = ['ymonth','st_out_times','st_out_duration']
        call_all = pd.merge(call_all, st_call_out,how='outer', on=['ymonth']).fillna(0)
        #12-18 呼出次数 时长
        te_call_out = pd_detail[pd_detail.init_type.str.contains('主',na=False) & pd_detail.time_section.str.contains('te',na=False)][['month','use_time']].groupby(['month']).agg(['count','sum']).reset_index()
        te_call_out.columns = ['ymonth','te_out_times','te_out_duration']
        call_all = pd.merge(call_all, te_call_out,how='outer', on=['ymonth']).fillna(0)
        #18-24 呼出次数 时长
        et_call_out = pd_detail[pd_detail.init_type.str.contains('主',na=False) & pd_detail.time_section.str.contains('et',na=False)][['month','use_time']].groupby(['month']).agg(['count','sum']).reset_index()
        et_call_out.columns = ['ymonth','etf_out_times','etf_out_duration']
        call_all = pd.merge(call_all, et_call_out,how='outer', on=['ymonth']).fillna(0)

        #0-6点 通话次数 时长
        call_all['zs_call_times'] = call_all['zs_in_times'] + call_all['zs_out_times']
        call_all['zs_call_duration'] = call_all['zs_in_duration'] + call_all['zs_out_duration']
        #6-12点 通话次数 时长
        call_all['st_call_times'] = call_all['st_in_times'] + call_all['st_out_times']
        call_all['st_call_duration'] = call_all['st_in_duration'] + call_all['st_out_duration']
        #12-18点 通话次数 时长
        call_all['te_call_times'] = call_all['te_in_times'] + call_all['te_out_times']
        call_all['te_call_duration'] = call_all['te_in_duration'] + call_all['te_out_duration']
        #18-24点 通话次数 时长
        call_all['etf_call_times'] = call_all['etf_in_times'] + call_all['etf_out_times']
        call_all['etf_call_duration'] = call_all['etf_in_duration'] + call_all['etf_out_duration']
        
        #总通话次数
        call_all['total_times'] = call_all['zs_call_times'] + call_all['st_call_times'] + call_all['te_call_times'] + call_all['etf_call_times']
        #总通话时长
        call_all['total_duration'] = call_all['zs_call_duration'] + call_all['st_call_duration'] + call_all['te_call_duration'] + call_all['etf_call_duration']
        #工作日通话次数
        workingday_count = pd_detail[pd_detail.is_week.str.contains('workingday',na=False)][['month','other_cell_phone']].groupby(['month']).count().reset_index()
        workingday_count.columns = ['ymonth','work_call_times']
        call_all = pd.merge(call_all, workingday_count,how='outer', on=['ymonth']).fillna(0)
        #非工作日通话次数
        holiday_count = pd_detail[pd_detail.is_week.str.contains('holiday',na=False)][['month','other_cell_phone']].groupby(['month']).count().reset_index()
        holiday_count.columns = ['ymonth','weekend_call_times']
        call_all = pd.merge(call_all, holiday_count,how='outer', on=['ymonth']).fillna(0)
        
        #联系人1
        contact_phone = dict_contact_phone.get('mobile',None)
        if contact_phone is not None:
            #联系人1通话次数 通话时长
            contacts_one = pd_detail[pd_detail.other_cell_phone.str.contains(contact_phone)].groupby(['month'])['use_time'].agg(['count','sum']).reset_index()
            contacts_one.columns = ['ymonth','contacts_times','contacts_duration']
            call_all = pd.merge(call_all, contacts_one,how='outer',on=['ymonth']).fillna(0)

            #与联系人2通话最频繁的时间段
            df = pd_detail[pd_detail.other_cell_phone.str.contains(contact_phone)][['month','hour','other_cell_phone']].groupby(['month','hour'])['other_cell_phone'].agg(['count'])
            contacts_often_time_part = df[df['count'] == df.groupby(level=[0])['count'].transform(max)].reset_index().drop_duplicates('month')
            contacts_often_time_part.columns = ['ymonth','contacts_often_time_part','contacts_often_count']
            call_all = pd.merge(call_all,contacts_often_time_part[['ymonth','contacts_often_time_part']],how='outer',on=['ymonth']).fillna(0)
        else:
            call_all['contacts_times'] = 0
            call_all['contacts_duration'] = 0
            call_all['contacts_often_time_part'] = ''

        #联系人2
        relatives_phone = dict_contact_phone.get('phone',None)
        if relatives_phone is not None:
            #联系人2通话次数 通话时长
            contacts_two = pd_detail[pd_detail.other_cell_phone.str.contains(relatives_phone)].groupby(['month'])['use_time'].agg(['count','sum']).reset_index()
            contacts_two.columns = ['ymonth','relatives_times','relatives_duration']
            call_all = pd.merge(call_all,contacts_two,how='outer',on=['ymonth']).fillna(0)

            #与联系人2通话最频繁的时间段
            df = pd_detail[pd_detail.other_cell_phone.str.contains(relatives_phone)][['month','hour','other_cell_phone']].groupby(['month','hour'])['other_cell_phone'].agg(['count'])
            relatives_often_time_part = df[df['count'] == df.groupby(level=[0])['count'].transform(max)].reset_index().drop_duplicates('month')
            relatives_often_time_part.columns = ['ymonth','relatives_often_time_part','relatives_often_count']
            call_all = pd.merge(call_all,relatives_often_time_part[['ymonth','relatives_often_time_part']],how='outer',on=['ymonth']).fillna(0)
        else:
            call_all['relatives_times'] = 0
            call_all['relatives_duration'] = 0
            call_all['relatives_often_time_part'] = ''

        if call_all is not None :
            return call_all.to_dict(orient="index")
        return None



class DetailVsContact:

    '''
    通话详情vs亲属联系人
    '''

    def __init__(self, contact, pd_detail, phone_rank, duration_rank, com_month_num):
        self.contact = contact
        self.pd_detail = pd_detail
        self.phone_rank = phone_rank
        self.duration_rank = duration_rank
        self.com_month_num = com_month_num

    def run(self):
        '''
        _r表示亲属
        _c表示社会
        '''
        if self.contact is None:
            return None

        pd_detail = self.pd_detail
        other_cell_phone = pd_detail.other_cell_phone
        use_time = pd_detail.use_time

        phone_rank = self.phone_rank
        duration_rank = self.duration_rank
        com_month_num = self.com_month_num

        # 参数验证 @todo
        relatives_phone = self.contact.get('phone',0)
        contacts_phone = self.contact.get('mobile',0)

        # 43 亲属联系人月均通话次数过少
        # 48 社会联系人月均通话次数过少
        com_r_total = other_cell_phone[other_cell_phone == relatives_phone].count()
        com_c_total = other_cell_phone[other_cell_phone == contacts_phone].count()

        # 45 亲属联系人通话次数排名过于靠后
        # 50 社会联系人通话次数排名过于靠后
        com_r_rank = phone_rank[phone_rank > com_r_total].count()
        com_c_rank = phone_rank[phone_rank > com_c_total].count()

        # 44 亲属联系人月均通话时长过短
        # 48 社会联系人月均通话时长过短
        com_r_duration = use_time[other_cell_phone == relatives_phone].sum()
        com_c_duration = use_time[other_cell_phone == contacts_phone].sum()

        # 46亲属联系人通话时长排名过于靠后  46  com_r_duration_rank 当前时长名次
        # 51社会联系人通话时长排名过于靠后  51  com_c_duration_rank 当前时长名次
        duration_rank = duration_rank.use_time
        com_r_duration_rank = duration_rank[duration_rank > com_r_duration].count()
        com_c_duration_rank = duration_rank[duration_rank > com_c_duration].count()

        return {
            # 亲属的次数,时长的 汇总; 排名; 月均数;
            'com_r_total': int(com_r_total),
            'com_r_rank': int(com_r_rank) + 1,
            'com_r_total_mavg':  float('%.2f' % (com_r_total / com_month_num)),
            'com_r_duration': int(com_r_duration),
            'com_r_duration_rank': int(com_r_duration_rank) + 1,
            'com_r_duration_mavg':  float('%.2f' % (com_r_duration / com_month_num)),

            # 联系人的次数,时长的 汇总; 排名; 月均数;
            'com_c_total': int(com_c_total),
            'com_c_rank': int(com_c_rank) + 1,
            'com_c_total_mavg': float('%.2f' % (com_c_total / com_month_num)),
            'com_c_duration': int(com_c_duration),
            'com_c_duration_rank': int(com_c_duration_rank) + 1,
            'com_c_duration_mavg':  float('%.2f' % (com_c_duration / com_month_num)),
        }


class DetailVsAddress:
    # 通话详情与通讯录匹配情况

    def __init__(self, pd_address, pd_detail, phone_rank, duration_rank, com_valid_15_m):
        self.pd_address = pd_address
        self.pd_detail = pd_detail
        self.phone_rank = phone_rank
        self.duration_rank = duration_rank

        # 有效联系人
        self.com_valid_15_m = com_valid_15_m

    def run(self):
        # 次数排行的手机匹配
        pd_phone_rank = pd.DataFrame({'phone_rank': self.phone_rank}, index=self.phone_rank.index)

        # 总体匹配度
        cgrp = pd.merge(pd_phone_rank, self.pd_address,
                        left_index=True,
                        right_on='phone',
                        how="inner")
        vs_phone_match = cgrp['phone'].drop_duplicates().count()

        # 排行前40匹配度
        cgrp = pd.merge(pd_phone_rank[:40], self.pd_address,
                        left_index=True,
                        right_on='phone',
                        how="inner")

        vs_connect_match = cgrp['phone'].drop_duplicates().count()

        # 有效联系人匹配度
        pd_com_valid_15_m = pd.DataFrame({'phone_rank': self.com_valid_15_m}, index=self.com_valid_15_m.index)
        cgrp = pd.merge(pd_com_valid_15_m, self.pd_address,
                        left_index=True,
                        right_on='phone',
                        how="inner")
        vs_valid_match = cgrp['phone'].drop_duplicates().count()

        # print(str(self.duration_rank))
        # 时长排行的手机匹配
        pd_duration_rank = pd.DataFrame({'duration_rank': self.duration_rank}, index=self.duration_rank.index)
        dgrp = pd.merge(pd_duration_rank[:40], self.pd_address, left_index=True, right_on='phone', how="inner")
        vs_duration_match = dgrp['phone'].drop_duplicates().count()

        # 99 运营商前40位通话时长手机号与通讯录匹配度   99  vs_duration_match   60  1小时
        # 100 运营商前40位通话次数手机号与通讯录匹配度  100 vs_connect_match    30  30分钟
        return {
            'vs_phone_match': int(vs_phone_match),
            'vs_connect_match': int(vs_connect_match),
            'vs_duration_match': int(vs_duration_match),

            # 有效联系人(>=15的手机号)与通讯录匹配度
            'vs_valid_match': int(vs_valid_match),
        }


class DetailVsAuth:
    # 认证人匹配

    def __init__(self, db_auth, pd_detail):
        self.db_auth = db_auth
        self.pd_auth = self.db2pandas(db_auth)
        self.pd_detail = pd_detail

    def db2pandas(self, db_auth):
        # 切换为pandas格式
        data = [(d.YiFriend.user_id, d.YiFriend.fuser_id, d.YiFriend.auth, d.YiFriend.authed,
                 d.YiFriend.type, d.YiUser.mobile if d.YiUser else None) for d in db_auth]
        pd_auth = pd.DataFrame(data=data, columns=['user_id', 'fuser_id', 'auth', 'authed', 'type', 'mobile'])
        return pd_auth

    def run(self):
        # 103 运营商内容与认证人匹配度   103 vs_auth_match
        # 认证我的人匹配度 authed=1
        pd_auth = self.pd_auth
        authme = pd_auth[pd_auth.authed == 1]
        authme_num, vs_auth_match = getMatchStat(authme, self.pd_detail)

        # 105 运营商内容与被认证人匹配度  105 vs_authed_match
        # 我认证的人匹配度 auth=1
        myauth = pd_auth[pd_auth.auth == 1]
        myauth_num, vs_authed_match = getMatchStat(myauth, self.pd_detail)

        return {
            'authme_num': int(authme_num),
            'vs_auth_match': int(vs_auth_match),
            'myauth_num': int(myauth_num),
            'vs_authed_match': int(vs_authed_match),
        }


class DetailVsInvest:
    # 投资人匹配

    def __init__(self, db_invest_me, db_my_invest, pd_detail):
        self.db_invest_me = db_invest_me
        self.db_my_invest = db_my_invest

        self.pd_invest_me = self.db2pandas(db_invest_me)
        self.pd_my_invest = self.db2pandas(db_my_invest)

        self.pd_detail = pd_detail

    def db2pandas(self, db_data):
        # 切换为pandas格式
        data = [(d.my_user_id, d.i_user_id, d.mobile) for d in db_data]
        pd_data = pd.DataFrame(data=data, columns=['my_user_id', 'i_user_id', 'mobile'])
        return pd_data

    def run(self):
        # 运营商内容与投资人匹配度
        investme_num, vs_invest_match = getMatchStat(self.pd_invest_me, self.pd_detail)
        # 运营商内容与被投资人匹配度
        myinvest_num, vs_invested_match = getMatchStat(self.pd_my_invest, self.pd_detail)

        return {
            'investme_num':  int(investme_num),
            'vs_invest_match': int(vs_invest_match),
            'myinvest_num': int(myinvest_num),
            'vs_invested_match': int(vs_invested_match),
        }


def getMatchStat(auth_phone, pd_detail):
    auth_num = auth_phone['mobile'].count()
    if auth_num == 0:
        return 0, 0

    innerJoin = pd.merge(auth_phone,
                         pd_detail[['other_cell_phone']],
                         left_on='mobile',
                         right_on='other_cell_phone',
                         how="inner")

    match_num = innerJoin['mobile'].drop_duplicates().count()
    return auth_num, match_num
