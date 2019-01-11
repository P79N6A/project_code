# # -*- coding: utf-8 -*-

# from lib.logger import logger
# from module.yiyiyuan.model import YiUser
# from module.yiyiyuan.model import YiLoan
# from module.yiyiyuan.model import YiUserLoanExtend
# from model.antifraud.af_detail_tag import AfDetailTag


# from module.yigeyi.model import YgyUser
# from module.yigeyi.model import YgyLoan
# from module.yigeyi.model import YgyUserLoanExtend

# from module.sevendayshappy.model import LoanUser
# from module.sevendayshappy.model import LoanUserLoan
# from module.sevendayshappy.model import LoanOverdueLoan
# from module.sevendayshappy.model import LoanRepay
# from module.sevendayshappy.model import LoanUserLoanExtend


# from module.sevendayshappyshop.model import ShopLoanUser
# from module.sevendayshappyshop.model import ShopLoanUserLoan
# from module.sevendayshappyshop.model import ShopLoanOverdueLoan
# from module.sevendayshappyshop.model import ShopLoanRepay
# from module.sevendayshappyshop.model import ShopLoanUserLoanExtend
# # #
# # from module.yigeyi.model import YgyUser
# # # from module.yigeyi.model import YgyLoan

# import re
# import pandas as pd
# import math
# import json


# class Addrloanlogic(object):



#     def __init__(self, pd_address, phone, identity, contact, contain=1):
#         # if pd_address is None:
#         #     raise Exception(1001, "pd_address can't analysis")
#         self.contain = int(contain)
#         self.pd_address = pd_address
#         self.contact = contact #常用联系人
#         self.identity = identity
#         self.phone = phone
#         self.__dictData()

#     def __dictData(self):
#         '''
#         param   :pd_address
#                 :aid  项目类型
#         '''
#         self.user_total = {}  # 通讯录是平台用户数量
#         self.loan_all = {}  # 通讯录与loan表总数(含申请)
#         self.loan_total = {}  # 通讯录通讯录有过放款数量
#         self.overdue_norepay = {}  # 逾期未还款
#         self.overdue_repay = {}  # 逾期已还款
#         self.overdue7_norepay = {}  # 逾期7天未还款
#         self.overdue7_repay = {}  # 逾期7天已还款
#         self.last_loan_day = {}  # 通讯录最近一次申请借款天数
#         self.normal_repay = {}  # 通讯录借款提前/正常还款
#         self.realadl_tot_reject_num = {}
#         self.realadl_tot_freject_num = {}
#         self.realadl_tot_sreject_num = {}
#         self.realadl_tot_dlq14_num = {}
#         self.realadl_dlq14_ratio = {}
#         self.history_bad_status = {}
#         self.realadl_wst_dlq_sts = {}
#         self.all_data = {}
#         self.detail_tag = {}
#         self.com_c_user = {}
#         self.success_num = {}
#         self.last_end_date = {}
#         self.last_repay_time = {}
#         self.last_success_loan_days = {}
#         self.mth3_dlq_num = {}
#         self.mth3_dlq7_num = {}
#         self.mth3_wst_sys = {}
#         self.mth6_dlq_ratio = {}
#         self.wst_dlq_sts = {}
#         self.is_overdue = {}
#         self.is_loaning = {}
#         self.user_loan_total = {}

#     def run(self):
#         # 获取手机号
#         mobiles = self.getMobiles()
#         #logger.info("aaaaa=%s" % mobiles)
#         #mobiles = ['13037976790', '15736513030', '13078937808', '15974609898', '15162685351', '15093560261']

#         #mobiles = ["13800138001", "18301676657","15093560261"]
#         # if len(mobiles) == 0:
#         #     return None
#         get_whole = self.getWhole(mobiles);
#         #logger.info("dsfdsfdfdsfds=%s" % get_whole)
#         return get_whole

#     def getWhole(self, mobiles):
#         #一亿元
#         if (self.contain & 1):
#             #logger.info("aaaaa=%s" % self.contain)
#             self.yiyiyuanRelevant(mobiles)

#         #一个亿
#         if (self.contain & 2):
#             #logger.info("bbbbb=%s" % self.contain)
#             self.yigeyiRelevant(mobiles)

#         #七天乐
#         if (self.contain & 4):
#             #logger.info("cccc=%s" % self.contain)
#             self.sevenDaysHappy(mobiles)

#         #七天乐商城
#         if (self.contain & 8):
#             #logger.info("dddd=%s" % self.contain)
#             self.sevenDaysHappyShop(mobiles)

#         return self.all_data

#     #一亿元数据
#     def yiyiyuanRelevant(self, mobiles):
#         relevant = {}
#         if len(mobiles) > 0:
#             user_ids = YiUser().getUidsByMobiles(mobiles)
    
#             self.user_total["yiyiyuan"] = YiUser().getUidCounts(mobiles)
#             relevant['user_total'] = self.user_total["yiyiyuan"]

#             self.loan_all["yiyiyuan"] = YiLoan().getAllLoanByUids(user_ids)
#             relevant['loan_all'] = self.loan_all["yiyiyuan"]

#             self.loan_total["yiyiyuan"] = YiLoan().getLoanedByUids(user_ids)
#             relevant['loan_total'] = self.loan_total["yiyiyuan"]

#             self.overdue_norepay["yiyiyuan"] = YiLoan().overdueAndNorepayByUids(user_ids)
#             relevant['overdue_norepay'] = self.overdue_norepay["yiyiyuan"]

#             self.overdue_repay["yiyiyuan"] = YiLoan().overdueAndRepayByUids(user_ids)
#             relevant['overdue_repay'] = self.overdue_repay["yiyiyuan"]

#             self.overdue7_norepay["yiyiyuan"] = YiLoan().overdue7AndNorepay(user_ids)
#             relevant['overdue7_norepay'] = self.overdue7_norepay["yiyiyuan"]

#             self.overdue7_repay["yiyiyuan"] = YiLoan().overdue7AndRepay(user_ids)
#             relevant['overdue7_repay'] = self.overdue7_repay["yiyiyuan"]

#             self.last_loan_day["yiyiyuan"] = YiLoan().lateApplyDay(user_ids)
#             relevant['last_loan_day'] = self.last_loan_day["yiyiyuan"]

#             self.normal_repay["yiyiyuan"] = YiLoan().advanceRepay(user_ids)
#             relevant['normal_repay'] = self.normal_repay["yiyiyuan"]

#             history_bad_status_total = YiLoan().getHistroyBadStatus(user_ids)

#             self.realadl_tot_reject_num["yiyiyuan"] = history_bad_status_total.get(
#                 'realadl_tot_reject_num') if history_bad_status_total else 0
#             relevant['realadl_tot_reject_num'] = self.realadl_tot_reject_num["yiyiyuan"]

#             self.realadl_tot_freject_num["yiyiyuan"] = history_bad_status_total.get(
#                 'realadl_tot_freject_num') if history_bad_status_total else 0
#             relevant['realadl_tot_freject_num'] = self.realadl_tot_freject_num["yiyiyuan"]

#             self.realadl_tot_sreject_num["yiyiyuan"] = history_bad_status_total.get(
#                 'realadl_tot_sreject_num') if history_bad_status_total else 0
#             relevant['realadl_tot_sreject_num'] = self.realadl_tot_sreject_num["yiyiyuan"]

#             self.realadl_tot_dlq14_num["yiyiyuan"] = history_bad_status_total.get(
#                 'realadl_tot_dlq14_num') if history_bad_status_total else 0
#             relevant['realadl_tot_dlq14_num'] = self.realadl_tot_dlq14_num["yiyiyuan"]

#             self.realadl_dlq14_ratio["yiyiyuan"] = history_bad_status_total.get('realadl_dlq14_ratio') if history_bad_status_total else 0
#             relevant['realadl_dlq14_ratio'] = self.realadl_dlq14_ratio["yiyiyuan"]

#             self.history_bad_status["yiyiyuan"] = history_bad_status_total.get('history_bad_status') if history_bad_status_total else 0
#             relevant['history_bad_status'] = self.history_bad_status["yiyiyuan"]
#             # #==================================
#             #历史最坏逾期天数(history_bad_status)
#             self.realadl_wst_dlq_sts["yiyiyuan"] = history_bad_status_total.get('history_bad_status') if history_bad_status_total else 0
#             relevant['realadl_wst_dlq_sts'] = self.realadl_wst_dlq_sts["yiyiyuan"]
#         #常用联系人是否存在于一亿元用户中
#         try:
#             mobile = json.loads(self.contact).get("mobile")
#         except Exception as e:
#             mobile = 0
#         self.com_c_user["yiyiyuan"] = YiUser().getUidCounts([mobile])
#         relevant['com_c_user'] = self.com_c_user["yiyiyuan"]


#         # self.identity = 232132
#         user_info = YiUser().getByIdentity(self.identity)
#         if user_info is None:
#             user_id = 0
#         else:
#             user_id = user_info.user_id

#         # 成功借款次数
#         self.success_num["yiyiyuan"] = YiLoan().getSuccessNum(self.phone,self.identity)
#         relevant['success_num'] = self.success_num["yiyiyuan"]

#         last_succ_loan = YiLoan().getLastSuccLoan(user_id)

#         #上次成功借款id的到期时间
#         self.last_end_date["yiyiyuan"] = last_succ_loan.get("last_end_date")
#         relevant['last_end_date'] = self.last_end_date["yiyiyuan"]

#         #上次成功借款id的还款时间
#         self.last_repay_time["yiyiyuan"] = last_succ_loan.get("last_repay_time")
#         relevant['last_repay_time'] = self.last_repay_time["yiyiyuan"]

#         #上笔成功还款账单的借款天数
#         self.last_success_loan_days["yiyiyuan"] = last_succ_loan.get("last_success_loan_days")
#         relevant['last_success_loan_days'] = self.last_success_loan_days["yiyiyuan"]

#         overdu_loan = YiLoan().getOverdueLoan(user_id)
#         #客户过去3个月逾期次数（按照贷款记）
#         self.mth3_dlq_num["yiyiyuan"] = overdu_loan.get("mth3_dlq_num") #if overdu_loan.get("mth3_dlq_num") else 0
#         relevant['mth3_dlq_num'] = self.mth3_dlq_num["yiyiyuan"]

#         #客户过去3个月逾期超过7天的贷款数
#         self.mth3_dlq7_num["yiyiyuan"] = overdu_loan.get("mth3_dlq7_num") #if overdu_loan.get("mth3_dlq7_num") else 0
#         relevant['mth3_dlq7_num'] = self.mth3_dlq7_num["yiyiyuan"]

#         #客户过去3个月最坏逾期天数
#         self.mth3_wst_sys["yiyiyuan"] = overdu_loan.get("mth3_wst_sys") #if  overdu_loan.get("mth3_wst_sys") else 0
#         relevant['mth3_wst_sys'] = self.mth3_wst_sys["yiyiyuan"]

#         #客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
#         self.mth6_dlq_ratio["yiyiyuan"] = overdu_loan.get("mth6_dlq_ratio") #if overdu_loan.get("mth6_dlq_ratio") else 0
#         relevant['mth6_dlq_ratio'] = self.mth6_dlq_ratio["yiyiyuan"]

#         #客户历史最坏逾期天数
#         self.wst_dlq_sts["yiyiyuan"] = overdu_loan.get("wst_dlq_sts") #if overdu_loan.get("wst_dlq_sts") else 0
#         relevant['wst_dlq_sts'] = self.wst_dlq_sts["yiyiyuan"]

#         self.is_overdue["yiyiyuan"] = self.getIsOverdue(user_id)
#         relevant['is_overdue'] = self.is_overdue["yiyiyuan"]

#         self.is_loaning["yiyiyuan"] = self.getIsLoaning(user_id)
#         relevant['is_loaning'] = self.is_loaning["yiyiyuan"]

#         self.user_loan_total["yiyiyuan"] = YiLoan().getApplyLoan(user_id)
#         # logger.info("aaaaa=%s" % self.detail_tag)
#         relevant['user_loan_total'] = self.user_loan_total["yiyiyuan"]

#         relevant['contain_id'] = 1
#         #逾期
#         #be_overdue = YiLoan().getReloanDates(user_id)

#         #logger.info("aaaaa=%s" % be_overdue)

#         self.all_data['yyy_loan'] = relevant

#     #七天乐
#     def sevenDaysHappy(self, mobiles):
#         relevant = {}
#         if len(mobiles) > 0:
#             user_ids = LoanUser().getUidsByMobiles(mobiles)

#             self.user_total["sevendayshappy"] = LoanUser().getUidCounts(mobiles)
#             relevant['user_total'] = self.user_total["sevendayshappy"]

#             self.loan_all["sevendayshappy"] = LoanUserLoan().getAllLoanByUids(user_ids)
#             relevant['loan_all'] = self.loan_all["sevendayshappy"]

#             self.loan_total["sevendayshappy"] = LoanUserLoan().getLoanedByUids(user_ids)
#             relevant['loan_total'] = self.loan_total["sevendayshappy"]

#             self.overdue_norepay["sevendayshappy"] = LoanOverdueLoan().overdueAndNorepayByUids(user_ids)
#             relevant['overdue_norepay'] = self.overdue_norepay["sevendayshappy"]

#             self.overdue_repay["sevendayshappy"] = LoanOverdueLoan().overdueAndRepayByUids(user_ids)
#             relevant['overdue_repay'] = self.overdue_repay["sevendayshappy"]

#             self.overdue7_norepay["sevendayshappy"] = LoanOverdueLoan().overdue7AndNorepay(user_ids)
#             relevant['overdue7_norepay'] = self.overdue7_norepay["sevendayshappy"]

#             self.overdue7_repay["sevendayshappy"] = LoanOverdueLoan().overdue7AndRepay(user_ids)
#             relevant['overdue7_repay'] = self.overdue7_repay["sevendayshappy"]

#             self.last_loan_day["sevendayshappy"] = LoanUserLoan().lateApplyDay(user_ids)
#             relevant['last_loan_day'] = self.last_loan_day["sevendayshappy"]

#             self.normal_repay["sevendayshappy"] = LoanRepay().advanceRepay(user_ids)
#             relevant['normal_repay'] = self.normal_repay["sevendayshappy"]

#             history_bad_status_total = LoanUserLoan().getHistroyBadStatus(user_ids)

#             self.realadl_tot_reject_num["sevendayshappy"] = history_bad_status_total.get(
#                 'realadl_tot_reject_num') if history_bad_status_total else 0
#             relevant['realadl_tot_reject_num'] = self.realadl_tot_reject_num["sevendayshappy"]

#             self.realadl_tot_freject_num["sevendayshappy"] = history_bad_status_total.get(
#                 'realadl_tot_freject_num') if history_bad_status_total else 0
#             relevant['realadl_tot_freject_num'] = self.realadl_tot_freject_num["sevendayshappy"]

#             self.realadl_tot_sreject_num["sevendayshappy"] = history_bad_status_total.get(
#                 'realadl_tot_sreject_num') if history_bad_status_total else 0
#             relevant['realadl_tot_sreject_num'] = self.realadl_tot_sreject_num["sevendayshappy"]

#             self.realadl_tot_dlq14_num["sevendayshappy"] = history_bad_status_total.get(
#                 'realadl_tot_dlq14_num') if history_bad_status_total else 0
#             relevant['realadl_tot_dlq14_num'] = self.realadl_tot_dlq14_num["sevendayshappy"]

#             self.realadl_dlq14_ratio["sevendayshappy"] = history_bad_status_total.get('realadl_dlq14_ratio') if history_bad_status_total else 0
#             relevant['realadl_dlq14_ratio'] = self.realadl_dlq14_ratio["sevendayshappy"]

#             self.history_bad_status["sevendayshappy"] = history_bad_status_total.get('history_bad_status') if history_bad_status_total else 0
#             relevant['history_bad_status'] = self.history_bad_status["sevendayshappy"]
#             #===================================
#             # 历史最坏逾期天数(history_bad_status)
#             self.realadl_wst_dlq_sts["sevendayshappy"] = history_bad_status_total.get(
#                 'history_bad_status') if history_bad_status_total else 0
#             relevant['realadl_wst_dlq_sts'] = self.realadl_wst_dlq_sts["sevendayshappy"]

#         # # 常用联系人是否存在于一亿元用户中
#         try:
#             mobile = json.loads(self.contact).get("mobile")
#         except Exception as e:
#             mobile = 0
#         self.com_c_user["sevendayshappy"] = LoanUser().getUidCounts(mobile)
#         relevant['com_c_user'] = self.com_c_user["sevendayshappy"]

#         # self.identity = 232132
#         user_info = LoanUser().getByIdentity(self.identity)
#         if user_info is None:
#             user_id = 0
#         else:
#             user_id = user_info.user_id

#         # 成功借款次数
#         self.success_num["sevendayshappy"] = LoanRepay().getSuccessNum(user_id)
#         relevant['success_num'] = self.success_num["sevendayshappy"]

#         #
#         last_succ_loan = LoanUserLoan().getLastSuccLoan(user_id)
#         # 上次成功借款id的到期时间
#         self.last_end_date["sevendayshappy"] = last_succ_loan.get("last_end_date")
#         relevant['last_end_date'] = self.last_end_date["sevendayshappy"]

#         # 上次成功借款id的还款时间
#         self.last_repay_time["sevendayshappy"] = last_succ_loan.get("last_repay_time")
#         relevant['last_repay_time'] = self.last_repay_time["sevendayshappy"]

#         # 上笔成功还款账单的借款天数
#         self.last_success_loan_days["sevendayshappy"] = last_succ_loan.get("last_success_loan_days")
#         relevant['last_success_loan_days'] = self.last_success_loan_days["sevendayshappy"]

#         overdue_loan = LoanOverdueLoan().getOverdueLoan(user_id)
#         # 客户过去3个月逾期次数（按照贷款记）
#         self.mth3_dlq_num["sevendayshappy"] = overdue_loan.get("mth3_dlq_num") 
#         relevant['mth3_dlq_num'] = self.mth3_dlq_num["sevendayshappy"]

#         # 客户过去3个月逾期超过7天的贷款数
#         self.mth3_dlq7_num["sevendayshappy"] = overdue_loan.get("mth3_dlq7_num") 
#         relevant['mth3_dlq7_num'] = self.mth3_dlq7_num["sevendayshappy"]

#         # 客户过去3个月最坏逾期天数
#         self.mth3_wst_sys["sevendayshappy"] = overdue_loan.get("mth3_wst_sys") 
#         relevant['mth3_wst_sys'] = self.mth3_wst_sys["sevendayshappy"]

#         # 客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
#         self.mth6_dlq_ratio["sevendayshappy"] = overdue_loan.get("mth6_dlq_ratio") 
#         relevant['mth6_dlq_ratio'] = self.mth6_dlq_ratio["sevendayshappy"]

#         # 客户历史最坏逾期天数
#         self.wst_dlq_sts["sevendayshappy"] = overdue_loan.get("wst_dlq_sts") 
#         relevant['wst_dlq_sts'] = self.wst_dlq_sts["sevendayshappy"]

#         self.is_overdue["sevendayshappy"] = self.getIsOverdue(user_id)
#         relevant['is_overdue'] = self.is_overdue["sevendayshappy"]

#         self.is_loaning["sevendayshappy"] = self.getIsLoaning(user_id)
#         relevant['is_loaning'] = self.is_loaning["sevendayshappy"]

#         self.user_loan_total["sevendayshappy"] = LoanUserLoan().getApplyLoan(user_id)
#         # logger.info("aaaaa=%s" % self.detail_tag)
#         relevant['user_loan_total'] = self.user_loan_total["sevendayshappy"]
        
#         relevant['contain_id'] = 4

#         self.all_data['sdh_loan'] = relevant

#     #七天乐商城
#     def sevenDaysHappyShop(self, mobiles):
#         relevant = {}
#         if len(mobiles) > 0:
#             user_ids = ShopLoanUser().getUidsByMobiles(mobiles)
#             self.user_total["sevendayshappyshop"] = ShopLoanUser().getUidCounts(mobiles)
#             relevant['user_total'] = self.user_total["sevendayshappyshop"]

#             self.loan_all["sevendayshappyshop"] = ShopLoanUserLoan().getAllLoanByUids(user_ids)
#             relevant['loan_all'] = self.loan_all["sevendayshappyshop"]

#             self.loan_total["sevendayshappyshop"] = ShopLoanUserLoan().getLoanedByUids(user_ids)
#             relevant['loan_total'] = self.loan_total["sevendayshappyshop"]

#             self.overdue_norepay["sevendayshappyshop"] = ShopLoanOverdueLoan().overdueAndNorepayByUids(user_ids)
#             relevant['overdue_norepay'] = self.overdue_norepay["sevendayshappyshop"]

#             self.overdue_repay["sevendayshappyshop"] = ShopLoanOverdueLoan().overdueAndRepayByUids(user_ids)
#             relevant['overdue_repay'] = self.overdue_repay["sevendayshappyshop"]

#             self.overdue7_norepay["sevendayshappyshop"] = ShopLoanOverdueLoan().overdue7AndNorepay(user_ids)
#             relevant['overdue7_norepay'] = self.overdue7_norepay["sevendayshappyshop"]

#             self.overdue7_repay["sevendayshappyshop"] = ShopLoanOverdueLoan().overdue7AndRepay(user_ids)
#             relevant['overdue7_repay'] = self.overdue7_repay["sevendayshappyshop"]

#             self.last_loan_day["sevendayshappyshop"] = ShopLoanUserLoan().lateApplyDay(user_ids)
#             relevant['last_loan_day'] = self.last_loan_day["sevendayshappyshop"]

#             self.normal_repay["sevendayshappyshop"] = ShopLoanRepay().advanceRepay(user_ids)
#             relevant['normal_repay'] = self.normal_repay["sevendayshappyshop"]

#             history_bad_status_total = ShopLoanUserLoan().getHistroyBadStatus(user_ids)

#             self.realadl_tot_reject_num["sevendayshappyshop"] = history_bad_status_total.get(
#                 'realadl_tot_reject_num') if history_bad_status_total else 0
#             relevant['realadl_tot_reject_num'] = self.realadl_tot_reject_num["sevendayshappyshop"]

#             self.realadl_tot_freject_num["sevendayshappyshop"] = history_bad_status_total.get(
#                 'realadl_tot_freject_num') if history_bad_status_total else 0
#             relevant['realadl_tot_freject_num'] = self.realadl_tot_freject_num["sevendayshappyshop"]

#             self.realadl_tot_sreject_num["sevendayshappyshop"] = history_bad_status_total.get(
#                 'realadl_tot_sreject_num') if history_bad_status_total else 0
#             relevant['realadl_tot_sreject_num'] = self.realadl_tot_sreject_num[
#                 "sevendayshappyshop"]

#             self.realadl_tot_dlq14_num["sevendayshappyshop"] = history_bad_status_total.get(
#                 'realadl_tot_dlq14_num') if history_bad_status_total else 0
#             relevant['realadl_tot_dlq14_num'] = self.realadl_tot_dlq14_num[
#                 "sevendayshappyshop"]

#             self.realadl_dlq14_ratio["sevendayshappyshop"] = history_bad_status_total.get(
#                 'realadl_dlq14_ratio') if history_bad_status_total else 0
#             relevant['realadl_dlq14_ratio'] = self.realadl_dlq14_ratio[
#                 "sevendayshappyshop"]

#             self.history_bad_status["sevendayshappyshop"] = history_bad_status_total.get(
#                 'history_bad_status') if history_bad_status_total else 0
#             relevant['history_bad_status'] = self.history_bad_status[
#                 "sevendayshappyshop"]
#             # ===================================
#             # 历史最坏逾期天数(history_bad_status)
#             self.realadl_wst_dlq_sts["sevendayshappyshop"] = history_bad_status_total.get(
#                 'history_bad_status') if history_bad_status_total else 0
#             relevant['realadl_wst_dlq_sts'] = self.realadl_wst_dlq_sts["sevendayshappyshop"]
#         # # 常用联系人是否存在于一亿元用户中
#         try:
#             mobile = json.loads(self.contact).get("mobile")
#         except Exception as e:
#             mobile = 0
#         self.com_c_user["sevendayshappyshop"] = ShopLoanUser().getUidCounts(mobile)
#         relevant['com_c_user'] = self.com_c_user["sevendayshappyshop"]

#         # self.identity = 232132
#         user_info = ShopLoanUser().getByIdentity(self.identity)
#         if user_info is None:
#             user_id = 0
#         else:
#             user_id = user_info.user_id

#         # 成功借款次数

#         self.success_num["sevendayshappyshop"] =  ShopLoanRepay().getSuccessNum(user_id)
#         relevant['success_num'] = self.success_num["sevendayshappyshop"]

#         last_succ_loan = ShopLoanUserLoan().getLastSuccLoan(user_id)
#         # 上次成功借款id的到期时间
#         self.last_end_date["sevendayshappyshop"] = last_succ_loan.get("last_end_date")
#         relevant['last_end_date'] = self.last_end_date["sevendayshappyshop"]

#         # 上次成功借款id的还款时间
#         self.last_repay_time["sevendayshappyshop"] = last_succ_loan.get("last_repay_time")
#         relevant['last_repay_time'] = self.last_repay_time["sevendayshappyshop"]

#         # 上笔成功还款账单的借款天数
#         self.last_success_loan_days["sevendayshappyshop"] = last_succ_loan.get("last_success_loan_days")
#         relevant['last_success_loan_days'] = self.last_success_loan_days["sevendayshappyshop"]

#         overdue_loan = ShopLoanOverdueLoan().getOverdueLoan(user_id)
#         # 客户过去3个月逾期次数（按照贷款记）
#         self.mth3_dlq_num["sevendayshappyshop"] = overdue_loan.get("mth3_dlq_num") 
#         relevant['mth3_dlq_num'] = self.mth3_dlq_num["sevendayshappyshop"]

#         # 客户过去3个月逾期超过7天的贷款数
#         self.mth3_dlq7_num["sevendayshappyshop"] = overdue_loan.get("mth3_dlq7_num") 
#         relevant['mth3_dlq7_num'] = self.mth3_dlq7_num["sevendayshappyshop"]

#         # 客户过去3个月最坏逾期天数
#         self.mth3_wst_sys["sevendayshappyshop"] = overdue_loan.get("mth3_wst_sys") 
#         relevant['mth3_wst_sys'] = self.mth3_wst_sys["sevendayshappyshop"]

#         # 客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
#         self.mth6_dlq_ratio["sevendayshappyshop"] = overdue_loan.get("mth6_dlq_ratio") 
#         relevant['mth6_dlq_ratio'] = self.mth6_dlq_ratio["sevendayshappyshop"]

#         # 客户历史最坏逾期天数
#         self.wst_dlq_sts["sevendayshappyshop"] = overdue_loan.get("wst_dlq_sts")
#         relevant['wst_dlq_sts'] = self.wst_dlq_sts["sevendayshappyshop"]

#         self.is_overdue["sevendayshappyshop"] = self.getIsOverdue(user_id)
#         relevant['is_overdue'] = self.is_overdue["sevendayshappyshop"]

#         self.is_loaning["sevendayshappyshop"] = self.getIsLoaning(user_id)
#         relevant['is_loaning'] = self.is_loaning["sevendayshappyshop"]

#         self.user_loan_total["sevendayshappyshop"] = ShopLoanUserLoan().getApplyLoan(user_id)
#         # logger.info("aaaaa=%s" % self.detail_tag)
#         relevant['user_loan_total'] = self.user_loan_total["sevendayshappyshop"]

#         relevant['contain_id'] = 8

#         self.all_data['sdhs_loan'] = relevant

#     # 一个亿数据
#     def yigeyiRelevant(self, mobiles):
#         relevant = {}
#         if len(mobiles) > 0:
#             user_ids = YgyUser().getUidsByMobiles(mobiles)
#             self.user_total["yigeyi"] = YgyUser().getUidCounts(mobiles)
#             relevant['user_total'] = self.user_total["yigeyi"]

#             self.loan_all["yigeyi"] = YgyLoan().getAllLoanByUids(user_ids)
#             relevant['loan_all'] = self.loan_all["yigeyi"]

#             self.loan_total["yigeyi"] = YgyLoan().getLoanedByUids(user_ids)
#             relevant['loan_total'] = self.loan_total["yigeyi"]

#             self.overdue_norepay["yigeyi"] = YgyLoan().overdueAndNorepayByUids(user_ids)
#             relevant['overdue_norepay'] = self.overdue_norepay["yigeyi"]

#             self.overdue_repay["yigeyi"] = YgyLoan().overdueAndRepayByUids(user_ids)
#             relevant['overdue_repay'] = self.overdue_repay["yigeyi"]

#             self.overdue7_norepay["yigeyi"] = YgyLoan().overdue7AndNorepay(user_ids)
#             relevant['overdue7_norepay'] = self.overdue7_norepay["yigeyi"]

#             self.overdue7_repay["yigeyi"] = YgyLoan().overdue7AndRepay(user_ids)
#             relevant['overdue7_repay'] = self.overdue7_repay["yigeyi"]

#             self.last_loan_day["yigeyi"] = YgyLoan().lateApplyDay(user_ids)
#             relevant['last_loan_day'] = self.last_loan_day["yigeyi"]

#             self.normal_repay["yigeyi"] = YgyLoan().advanceRepay(user_ids)
#             relevant['normal_repay'] = self.normal_repay["yigeyi"]

#             history_bad_status_total = YgyLoan().getHistroyBadStatus(user_ids)

#             self.realadl_tot_reject_num["yigeyi"] = history_bad_status_total.get(
#                 'realadl_tot_reject_num') if history_bad_status_total else 0
#             relevant['realadl_tot_reject_num'] = self.realadl_tot_reject_num["yigeyi"]

#             self.realadl_tot_freject_num["yigeyi"] = history_bad_status_total.get(
#                 'realadl_tot_freject_num') if history_bad_status_total else 0
#             relevant['realadl_tot_freject_num'] = self.realadl_tot_freject_num["yigeyi"]

#             self.realadl_tot_sreject_num["yigeyi"] = history_bad_status_total.get(
#                 'realadl_tot_sreject_num') if history_bad_status_total else 0
#             relevant['realadl_tot_sreject_num'] = self.realadl_tot_sreject_num["yigeyi"]

#             self.realadl_tot_dlq14_num["yigeyi"] = history_bad_status_total.get(
#                 'realadl_tot_dlq14_num') if history_bad_status_total else 0
#             relevant['realadl_tot_dlq14_num'] = self.realadl_tot_dlq14_num["yigeyi"]

#             self.realadl_dlq14_ratio["yigeyi"] = history_bad_status_total.get(
#                 'realadl_dlq14_ratio') if history_bad_status_total else 0
#             relevant['realadl_dlq14_ratio'] = self.realadl_dlq14_ratio["yigeyi"]

#             self.history_bad_status["yigeyi"] = history_bad_status_total.get(
#                 'history_bad_status') if history_bad_status_total else 0
#             relevant['history_bad_status'] = self.history_bad_status["yigeyi"]
#             # #==================================
#             # 历史最坏逾期天数(history_bad_status)
#             self.realadl_wst_dlq_sts["yigeyi"] = history_bad_status_total.get(
#                 'history_bad_status') if history_bad_status_total else 0
#             relevant['realadl_wst_dlq_sts'] = self.realadl_wst_dlq_sts["yigeyi"]
#         # 常用联系人是否存在于一亿元用户中
#         try:
#             mobile = json.loads(self.contact).get("mobile")
#         except Exception as e:
#             mobile = 0
#         self.com_c_user["yigeyi"] = YgyUser().getUidCounts(mobile)
#         relevant['com_c_user'] = self.com_c_user["yigeyi"]

#         # self.identity = 232132
#         user_info = YgyUser().getByIdentity(self.identity)
#         if user_info is None:
#             user_id = 0
#         else:
#             user_id = user_info.user_id

#         # 成功借款次数
#         self.success_num["yigeyi"] = YgyLoan().getSuccessNum(self.phone,self.identity)
#         relevant['success_num'] = self.success_num["yigeyi"]

#         last_succ_loan = YgyLoan().getLastSuccLoan(user_id)

#         # 上次成功借款id的到期时间
#         self.last_end_date["yigeyi"] = last_succ_loan.get("last_end_date")
#         relevant['last_end_date'] = self.last_end_date["yigeyi"]

#         # 上次成功借款id的还款时间
#         self.last_repay_time["yigeyi"] = last_succ_loan.get("last_repay_time")
#         relevant['last_repay_time'] = self.last_repay_time["yigeyi"]

#         # 上笔成功还款账单的借款天数
#         self.last_success_loan_days["yigeyi"] = last_succ_loan.get("last_success_loan_days")
#         relevant['last_success_loan_days'] = self.last_success_loan_days["yigeyi"]

#         overdu_loan = YgyLoan().getOverdueLoan(user_id)
#         #logger.info("aaaaaa= %s" % isinstance(overdu_loan.get("mth3_dlq_num"), str))
#         # 客户过去3个月逾期次数（按照贷款记）
#         self.mth3_dlq_num["yigeyi"] = overdu_loan.get("mth3_dlq_num")
#         relevant['mth3_dlq_num'] = self.mth3_dlq_num["yigeyi"]

#         # 客户过去3个月逾期超过7天的贷款数
#         self.mth3_dlq7_num["yigeyi"] = overdu_loan.get("mth3_dlq7_num") #str(overdu_loan.get("mth3_dlq7_num")) if not math.isnan(overdu_loan.get("mth3_dlq7_num")) else 0
#         relevant['mth3_dlq7_num'] = self.mth3_dlq7_num["yigeyi"]

#         # 客户过去3个月最坏逾期天数
#         self.mth3_wst_sys["yigeyi"] = overdu_loan.get("mth3_wst_sys")#str(overdu_loan.get("mth3_wst_sys")) if  not math.isnan(overdu_loan.get("mth3_wst_sys")) else 0
#         relevant['mth3_wst_sys'] = self.mth3_wst_sys["yigeyi"]

#         # 客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
#         self.mth6_dlq_ratio["yigeyi"] = overdu_loan.get("mth6_dlq_ratio")#str(overdu_loan.get("mth6_dlq_ratio")) if not math.isnan(overdu_loan.get("mth6_dlq_ratio")) else 0
#         relevant['mth6_dlq_ratio'] = self.mth6_dlq_ratio["yigeyi"]

#         # 客户历史最坏逾期天数
#         self.wst_dlq_sts["yigeyi"] = overdu_loan.get("wst_dlq_sts") #str(overdu_loan.get("wst_dlq_sts")) if not math.isnan(overdu_loan.get("wst_dlq_sts")) else 0
#         relevant['wst_dlq_sts'] = self.wst_dlq_sts["yigeyi"]

#         self.is_overdue["yigeyi"] = self.getIsOverdue(user_id)
#         relevant['is_overdue'] = self.is_overdue["yigeyi"]

#         self.is_loaning["yigeyi"] = self.getIsLoaning(user_id)
#         relevant['is_loaning'] = self.is_loaning["yigeyi"]

#         self.user_loan_total["yigeyi"] = YgyLoan().getApplyLoan(user_id)
#         # logger.info("aaaaa=%s" % self.detail_tag)
#         relevant['user_loan_total'] = self.user_loan_total["yigeyi"]

#         relevant['contain_id'] = 2

#         self.all_data['ygy_loan'] = relevant


#     def isStrTrue(self, strname):
#         if isinstance(strname, str):
#             return str(strname) if strname else 0
#         else:
#             str(strname) if not math.isnan(strname) else 0



#     def formatAdd(self, dict_list):
#         if not dict_list:
#             return 0
#         if not isinstance(dict_list, dict):
#             return 0
#         num = 0
#         for key, value in dict_list.items():
#             #logger.info("aaaaa=%s" % value)
#             num += float(value)
#         return num


#     def getMobiles(self):
#         if self.pd_address is None:
#             return []
#         addr_phones = self.pd_address.phone.str.replace('-','')
#         addr_phones = addr_phones.drop_duplicates()
#         addr_phones = addr_phones[addr_phones.str.len()==11]
#         is_tel = '^0\d{2,3}\d{7,8}$|^\d{7,8}$|^400'
#         p = re.compile(is_tel, re.DOTALL)
#         mobiles=addr_phones[addr_phones.str.contains(p)==False]

#         #最多分析1000个, 以减少数据库压力.(应该极小几率出现)
#         mlen = mobiles.count()
#         if mlen == 0:
#             return []

#         if mlen > 1000:
#             mlen=1000

#         return list(mobiles[0:mlen])

#     def getIsOverdue(self, user_id):
#         overdue_dict = {
#             "ygy_loan":0,
#             "sdhs_loan":0,
#             "sdh_loan":0,
#             "yyy_loan":0
#         }
#         if not user_id:
#             return overdue_dict
#         overdue_dict = {}
#         overdue_dict["ygy_loan"] = YgyLoan().getIsOverdue(user_id)
#         # overdue_dict["sdhs_loan"] = ShopLoanOverdueLoan().getIsOverdue(user_id)
#         # overdue_dict["sdh_loan"] = LoanOverdueLoan().getIsOverdue(user_id)
#         overdue_dict["sdhs_loan"] = 0
#         overdue_dict["sdh_loan"] = 0
#         overdue_dict["yyy_loan"] = YiLoan().getIsOverdue(user_id)
#         return overdue_dict

#     def getIsLoaning(self, user_id):
#         loaning_dict = {
#             "ygy_loan": 0,
#             "sdhs_loan": 0,
#             "sdh_loan": 0,
#             "yyy_loan": 0
#         }
#         if not user_id:
#             return loaning_dict

#         loaning_dict["yyy_loan"] = YiLoan().getIsLoading(user_id)
#         # loaning_dict["sdh_loan"] = LoanUserLoan().getIsLoading(user_id)
#         # loaning_dict["sdhs_loan"] = ShopLoanUserLoan().getIsLoading(user_id)
#         loaning_dict["sdh_loan"] = 0
#         loaning_dict["sdhs_loan"] = 0
#         loaning_dict["ygy_loan"] = YgyLoan().getIsLoading(user_id)
#         return loaning_dict