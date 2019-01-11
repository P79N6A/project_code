# 饼图类
class pie():
    equipmentList = {1: "安卓",2: "苹果",3: "微信",4: "PC端",}
    data = None

    # 饼图初始化
    def __init__(self,text,series_name):
        legend = list(self.equipmentList.values())
        self.data = {
            "header": series_name,
            "title": text,
            "legend": legend,
            "seriesName": series_name,
            "seriesData": [],
        }

    # 饼图插入值返回list
    def set_data(self, allBlockData):
        for eachBlockData in allBlockData:
            blockData = {"name": "" , "value": 0}
            blockData["name"] = self.equipmentList[eachBlockData["equipment"]]
            blockData["value"] = eachBlockData["total"]
            # 插入数据列表
            self.data["seriesData"].append(blockData)
        return self.data
