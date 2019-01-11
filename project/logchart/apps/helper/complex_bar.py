# 树状图
import pandas as pd
from apps.helper.timeHelper import timeHelper
from apps.logchart.models.peanutPageName import peanutPageNameModel

class complexBar():
    # 初始化数据
    def __init__(self,text):
        self.x_axis = []
        self.an_num = []
        self.ios_num = []
        self.weixin_num = []
        self.pc_num = []
        self.data = {'clb_text':"\"{}\"".format(text),
                     'clb_legend': ['安卓', 'ios', '微信', 'PC'],
                     'clb_x_axis':self.x_axis,
                     'clb_an_num':self.an_num,
                     'clb_ios_num':self.ios_num,
                     'clb_weixin_num':self.weixin_num,
                     'clb_pc_num':self.pc_num}
        self.page_name=['首页','注册页','借款页','详情页','个人中心']


    # 将页面数据插入相应的设备
    def equipment_classify(self,equipment,num,pagenameid):
        if equipment == 1:
            self.an_num[pagenameid-1] = num
        elif equipment == 2:
            self.ios_num[pagenameid-1] = num
        elif equipment == 3:
            self.weixin_num[pagenameid-1] = num
        elif equipment == 4:
            self.pc_num[pagenameid-1] = num

    # 将注册量数据插入相应的设备数据
    def set_equipment_classify(self,equipment,num):
        if equipment == 1:
            self.an_num.append(num)
        elif equipment == 2:
            self.ios_num.append(num)
        elif equipment == 3:
            self.weixin_num.append(num)
        elif equipment == 4:
            self.pc_num.append(num)



    # 插入每天按小时分组的数据
    def set_day_data(self,data,request):
        # 循环今天的时间段
        for i in mytime.day_time_now(request):
            # 添加时间名称
            self.x_axis.append(str(i[2])+'时')
            # 分段获取数据
            time_df = data[(data.data >= i[0])&(data.data <= i[1])]
            if len(time_df) == 0:
                self.set_equipment_classify(equipment=1, num=0)
                self.set_equipment_classify(equipment=2, num=0)
                self.set_equipment_classify(equipment=3, num=0)
                self.set_equipment_classify(equipment=4, num=0)
            else:
                # 循环分段数据
                for row in range(0,time_df.iloc[:,0].size):
                    # 插入数据
                    self.set_equipment_classify(equipment=time_df.iat[row,1],num=time_df.iat[row,2])
        return self.data


    #插入每月按日期分组的数据
    def set_mouth_data(self,data,request):
        # 循环本月时间段
        for i in mytime.mouth_time_now(request):
            # 添加日期名称
            self.x_axis.append(str(i[2]) + '号')\
            # 筛选时间段中的数据
            time_df = data[(data.data >= i[0]) & (data.data <= i[1])]
            df = time_df.groupby(by=['equipment'])['num'].sum()
            df = pd.DataFrame(df)
            if len(df) == 0:
                self.set_equipment_classify(equipment=1, num=0)
                self.set_equipment_classify(equipment=2, num=0)
                self.set_equipment_classify(equipment=3, num=0)
                self.set_equipment_classify(equipment=4, num=0)
            else:
                for row in range(0,df.iloc[:,0].size):
                    # 插入数据
                    self.set_equipment_classify(equipment=row+1,num=df.iat[row,0])
        return self.data

    # 插入四端的浏览量（月，日通用）
    def set_browse(self,data):
        # 添加空变量
        for pagesize in range(0, peanutPageNameModel().getPageSum()):
            self.an_num.append(0)
            self.ios_num.append(0)
            self.weixin_num.append(0)
            self.pc_num.append(0)
        page_name = []
        # 循环数据
        for i in range(1,5):
            # 得到各设备的数据
            df = data[data.equipment == i]
            # 循环每列
            for row in range(0, df.iloc[:, 0].size):
                # 将各个设备数据插入相应设备
                self.equipment_classify(equipment=df.iat[row, 0],num=df.iat[row, 2],pagenameid=df.iat[row, 1])
        # 获取页面名称的列表
        for pagesize in range(1, peanutPageNameModel().getPageSum() + 1):
            page_name.append(peanutPageNameModel().getPageName(pagesize))
        # 插入横坐标
        self.data['clb_x_axis'] = page_name
        return self.data




    # 插入投标量每天按小时分组的数据
    def set_bid_day_data(self,data,bid_type,text,request):
        self.data.clear()
        clb_text = 'clb_text'+str(bid_type)
        clb_legend = 'clb_legend'+str(bid_type)
        clb_x_axis = 'clb_x_axis'+str(bid_type)
        clb_an_num = 'clb_an_num'+str(bid_type)
        clb_ios_num = 'clb_ios_num'+str(bid_type)
        clb_weixin_num = 'clb_weixin_num'+str(bid_type)
        clb_pc_num = 'clb_pc_num'+str(bid_type)
        self.data[clb_text] = "\"{}\"".format(text)
        # 循环今天的时间段
        for i in mytime.day_time_now(request):
            # 添加时间名称
            self.x_axis.append(str(i[2])+'时')
            # 分段获取数据
            time_df = data[(data.data >= i[0])&(data.data <= i[1])]
            if len(time_df) == 0:
                self.set_equipment_classify(equipment=1, num=0)
                self.set_equipment_classify(equipment=2, num=0)
                self.set_equipment_classify(equipment=3, num=0)
                self.set_equipment_classify(equipment=4, num=0)
            else:
                # 循环分段数据
                for row in range(0,time_df.iloc[:,0].size):
                    # 插入数据
                    self.set_equipment_classify(equipment=time_df.iat[row,1],num=time_df.iat[row,2])
        self.data[clb_legend] = ['安卓', 'ios', '微信', 'PC']
        self.data[clb_x_axis] = self.x_axis
        self.data[clb_an_num] = self.an_num
        self.data[clb_ios_num] = self.ios_num
        self.data[clb_weixin_num] = self.weixin_num
        self.data[clb_pc_num] = self.pc_num
        return self.data


    #插入投标量每月按日期分组的数据
    def set_bid_mouth_data(self,data,bid_type,text,request):
        self.data.clear()
        clb_text = 'clb_text'+str(bid_type)
        clb_legend = 'clb_legend'+str(bid_type)
        clb_x_axis = 'clb_x_axis'+str(bid_type)
        clb_an_num = 'clb_an_num'+str(bid_type)
        clb_ios_num = 'clb_ios_num'+str(bid_type)
        clb_weixin_num = 'clb_weixin_num'+str(bid_type)
        clb_pc_num = 'clb_pc_num'+str(bid_type)
        self.data[clb_text] = "\"{}\"".format(text)
        # 循环本月时间段
        for i in mytime.mouth_time_now(request):
            # 添加日期名称
            self.x_axis.append(str(i[2]) + '号')\
            # 筛选时间段中的数据
            time_df = data[(data.data >= i[0]) & (data.data <= i[1])]
            df = time_df.groupby(by=['equipment'])['num'].sum()
            df = pd.DataFrame(df)
            if len(df) == 0:
                self.set_equipment_classify(equipment=1, num=0)
                self.set_equipment_classify(equipment=2, num=0)
                self.set_equipment_classify(equipment=3, num=0)
                self.set_equipment_classify(equipment=4, num=0)
            else:
                for row in range(0,df.iloc[:,0].size):
                    # 插入数据
                    self.set_equipment_classify(equipment=row+1,num=df.iat[row,0])
        self.data[clb_legend] = ['安卓', 'ios', '微信', 'PC']
        self.data[clb_x_axis] = self.x_axis
        self.data[clb_an_num] = self.an_num
        self.data[clb_ios_num] = self.ios_num
        self.data[clb_weixin_num] = self.weixin_num
        self.data[clb_pc_num] = self.pc_num
        return self.data

