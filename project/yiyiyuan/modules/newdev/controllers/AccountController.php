<?php
/**
 * Created by PhpStorm.
 * User: wangyongqiang
 * Date: 2017/4/26
 * Time: 15:56
 */
namespace app\modules\newdev\controllers;

use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\dev\User_extend;
use app\models\news\Coupon_list;
use app\models\news\Selection;
use app\models\news\User_bank;
use app\models\news\User;
use app\models\news\Juxinli;
use app\models\news\Favorite_contacts;
use app\models\news\Common;
use app\models\news\PrizeList;
use app\commonapi\Crypt3Des;
use app\models\news\User_quota_new;
use app\models\news\SystemMessageList;
use app\models\news\WarnMessageList;
use app\models\news\MessageApply;
use yii\helpers\ArrayHelper;
use Yii;

class AccountController extends NewdevController
{

    /**
     * "我"的页面
     */
    public function actionIndex()
    {

        $this->layout = "_data";
        $this->getView()->title = "账户";
        $user = $this->getUser();
        $userinfo = User::findOne($user->user_id);
        return $this->redirect('/borrow/account');
        //优惠券
        $now_time = date('Y-m-d H:i:s');
        $frcoupon = Coupon_list::find()->where(['status' => 1, 'mobile' => $userinfo->mobile])->andWhere("end_date > '$now_time'")->count();
        //奖品 start_time
        $prize_count = PrizeList::find()->where(['user_id' => $userinfo->user_id, 'status' => 6, 'use_status' => 1])->andWhere("start_time < '$now_time'")->andWhere("end_time > '$now_time'")->count();

        //银行卡
        $bank = User_bank::find()->where(['user_id' => $userinfo->user_id, 'status' => 1, 'type' => '0'])->count();
        //交易记录
        $transaction_record_url = "/new/loan/loanlist";
        //邀请好友
        $invite_friends_url = "/new/invitation/index";
        $jsinfo = $this->getWxParam();
        
        //额度
        $currentAmount = (new User_quota_new())->getQuotaByUserId($userinfo->user_id);
        $guaranteeAmount = 2500.00;//担保额度（默认2500）

        //提醒消息
        $warnmsg = WarnMessageList::find()->where(['user_id' => $userinfo->user_id, 'read_status' => 0,'status'=>[2,3]])->count();

        $msg_res = $this->pullMsg($userinfo->user_id);

        //系统消息(未读)
        $sysmsgpart_count = (new SystemMessageList())->getSysmsgcount($userinfo->user_id, 0);
        $unread_message_count = $sysmsgpart_count + $warnmsg;

        return $this->render('index', [
            'userinfo' => $userinfo,
            'jsinfo' => $jsinfo,
            'couponcount' => $frcoupon, //优惠券
            'bank' => $bank, //银行卡
            'transaction_record_url' => $transaction_record_url, //交易记录
            'invite_friends_url' => $invite_friends_url,//邀请好友
            'current_amount' => $currentAmount,
            'guarantee_amount' => $guaranteeAmount,
            'prize' => $prize_count,
            'unread_message_count' => $unread_message_count,
        ]);

    }

      /**
     * 读取消息数量
     * @param $mobile
     * @return bool
     */
    public function pullMsg($user_id)
    {
        if (empty($user_id)) {
            return false;
        }
        $where = [
            'AND',
            ['<', 'send_time', date('Y-m-d H:i:s')],
            ['type' => 2],
            ['apply_status' => 1],
            ['!=', 'exec_status', 3]
        ];
        $applyList = (new MessageApply())->find()->where($where)->all();
        
        if (empty($applyList)) {
            return true;
        }
        $cidArr = ArrayHelper::getColumn($applyList, 'id');
        
        $func = function ($str) {
            return intval($str);
        };
        $cidArr = array_map($func, $cidArr);
        $list = SystemMessageList::find()->where(['user_id' => $user_id, 'mid' => $cidArr])->all();
        $alreadyList = ArrayHelper::getColumn($list, 'mid');
       
        $cidDiff = array_diff($cidArr, $alreadyList);
        
        if (empty($cidDiff)) {
            return true;
        }
        $data = array();

            foreach ($cidDiff as $key => $value) {
                $apply_val = MessageApply::findOne($value);
                $data[$key][0]  = $value; 
                $data[$key][1]  = $apply_val->title; 
                $data[$key][2]  = $apply_val->contact; 
                $data[$key][3]  = $user_id; 
                $data[$key][4]  = 0; 
                $data[$key][5]  = date('Y-m-d H:i:s');
                $data[$key][6]  = date('Y-m-d H:i:s');
                $data[$key][7]  = 0; 
                $data[$key][8]  = $apply_val->send_time;
            }
            
           $res_insert = Yii::$app->db->createCommand()->batchInsert(
             SystemMessageList::tableName(), ['mid', 'title', 'contact', 'user_id', 'read_status', 'create_time', 'last_modify_time','version','send_time'],$data)->execute();
            if(!$res_insert){
            Logger::dayLog('api/getuserinfo', '写入systemmessagelist数据失败' . $user_id, $res_insert);

            }
           
        return true;
    }


    /**
     * 个人资料页
     * @return type
     */
    public function actionPeral()
    {
        $this->layout = "data";
        $user = $this->getUser();
        $userinfo = (new User())->findOne($user->user_id);
        $user_extend = (new User_extend())->find()->where(['user_id' => $user->user_id])->one();
        //查看用户的个人信息是否完善
        if ($userinfo->identity_valid != 2 && $userinfo->identity_valid != 4) {
            $pinfo = '未认证';
        } else {
            $pinfo = '修改';
        }
        //判断工作信息是否完善
        if (empty($user_extend) || empty($user_extend->company_area) || empty($user_extend->position)) {
            $cinfo = '未认证';
        } else {
            $cinfo = '修改';
        }
        //判断信用信息是否完善
        if ($userinfo->status == 3 || $userinfo->status == 5) {
            $xinfo = '已完善';
        } else if ($userinfo->status == 2) {
            $xinfo = '审核中';
        } else {
            $xinfo = '未完善';
        }
        if ($userinfo->status == 2) {
            $juli = 1;
        } else {
            $juxinliModel = new Juxinli();
            $juxinli = $juxinliModel->getJuxinliByUserId($userinfo->user_id);
            $juli = 0;
            if (empty($juxinli) || $juxinli->process_code != '10008' || ($juxinli->process_code == '10008' && date('Y-m-d H:i:s', strtotime('-4 month')) >= $juxinli->last_modify_time)) {
                $juli = 1;
            }
        }
        $juxinliModel = new Juxinli();
        $jingdong = $juxinliModel->getJuxinliByUserId($userinfo->user_id, 2);
        if (empty($jingdong) || $jingdong->process_code != '10008') {
            $jing = 1;
        } else {
            $jing = 2;
        }
        /*
         * 学历
         * */
        $selection_xueli = (new Selection())->getSelectiontype($userinfo->user_id, 1);
        $xueli = 1;
        $xueliName = '未认证';
        if (!empty($selection_xueli)) {
            $isVal = $selection_xueli->getValidity();
            if (!empty($isVal)) {
                $xueli = 2;
                $xueliName = '已认证';
            }
            if ($selection_xueli->process_code == '10002') {
                $xueli = 3;
                $xueliName = '认证中';
            }
        }

        /*
         * 社保
         * */
        $selection_shebao = (new Selection())->getSelectiontype($userinfo->user_id, 2);
        $shebao = 1;
        $shebaoName = '未认证';
        if (!empty($selection_shebao)) {
            $isVal = $selection_shebao->getValidity();
            if (!empty($isVal)) {
                $shebao = 2;
                $shebaoName = '已认证';
            }
            if ($selection_shebao->process_code == '10002') {
                $shebao = 3;
                $shebaoName = '认证中';
            }
        }
        $userbank_xyk = (new User_bank())->getCreditCardInfo($userinfo->user_id);
        if (empty($userbank_xyk)) {
            $juxinli = (new Juxinli())->getJuxinliByUserId($userinfo->user_id, 1);
            $xyk = 1;
            if ($pinfo == '未认证') {
                $xyk = 3;
            }
        } else {
            $xyk = 2;
        }
        /*
         * 公积金
         * */
        $selection_gongjijin = (new Selection())->getSelectiontype($userinfo->user_id, 3);
        $gongjijin = 1;
        $gongjijinName = '未认证';
        if (!empty($selection_gongjijin)) {
            $isVal = $selection_gongjijin->getValidity();
            if (!empty($isVal)) {
                $gongjijin = 2;
                $gongjijinName = '已认证';
            }
            if ($selection_gongjijin->process_code == '10002') {
                $gongjijin = 3;
                $gongjijinName = '认证中';
            }
        }
        $favorite = new Favorite_contacts();
        $fav = $favorite->getFavoriteByUserId($userinfo->user_id);
        $contacts = !empty($fav) ? 1 : 2;
        $this->getView()->title = "个人资料";
        $jsinfo = $this->getWxParam();
        return $this->render('peral', [
            'userinfo' => $userinfo,
            'pinfo' => $pinfo,
            'cinfo' => $cinfo,
            'xinfo' => $xinfo,
            'juli' => $juli,
            'xueli' => $xueli,
            'shebao' => $shebao,
            'xyk' => $xyk,
            'gongjijin' => $gongjijin,
            'jing' => $jing,
            'contacts' => $contacts,
            'jsinfo' => $jsinfo,
            'xueli_name' => $xueliName,
            'shebao_name' => $shebaoName,
            'gongjijin_name' => $gongjijinName,
            'csrf' => $this->getCsrf(),

        ]);
    }

    /*
     * 新的认证
     * */
    public function actionRenzheng()
    {
        $user_id = Yii::$app->request->post('user_id');
        $type = Yii::$app->request->post('type');

        if (empty($user_id) || empty($type)) {
            exit(json_encode(['code' => '2', 'msg' => '非法请求']));
        }

        $selectionObj = (new Selection())->getByUserIdAndTpey($user_id, $type);
        if (!empty($selectionObj)) {
            if ($selectionObj->process_code == '10002') {
                exit(json_encode(['code' => '2', 'msg' => $this->getErrorMsg('10221')]));
            }
            $isVal = $selectionObj->getValidity();
            if (!empty($isVal)) {
                exit(json_encode(['code' => '2', 'msg' => $this->getErrorMsg('10219')]));
            }
        }

        $url = Yii::$app->request->hostInfo . '/new/selection/middle';
        $res = (new Http())->selection_choice($user_id, $type, $url);
        if ($res['res_code'] != '105002' || empty($res['res_url'])) {
            Logger::dayLog('newdev/account/renzheng', '认证接口请求失败' . $user_id, $res);
            if (isset($res['res_data']['reason']) && !empty($res['res_data']['reason']) && in_array($res['res_code'], ['105003', '105007'])) {
                exit(json_encode(['code' => '2', 'msg' => $res['res_data']['reason']]));
            }
            exit(json_encode(['code' => '2', 'msg' => $this->getErrorMsg('10220')]));
        }

        if (empty($selectionObj)) {
            $condition = [
                'user_id' => $user_id,
                'type' => $type,//认证类型
                'source' => 1,//认证来源
            ];
            $save_result = (new Selection())->addRecord($condition);
        } else {
            $condition = [
                'source' => 1,//认证来源
            ];
            $save_result = $selectionObj->updateRecord($condition);
        }
        if (!$save_result) {
            Logger::dayLog('newdev/account/renzheng', '请求认证之后存储数据yi_selection失败' . $user_id, $save_result);
            exit(json_encode(['code' => '2', 'msg' => $this->getErrorMsg('99987')]));
        }
        exit(json_encode(['code' => '1', 'data' => $res['res_url'], 'msg' => '成功']));
    }

    /**
     * 主动认证分发方法
     */
    public function actionDistribute()
    {
        $user = $this->getUser();
        $userinfo = (new User())->findOne($user->user_id);
        $getData = $this->get();
        if (!isset($getData['type']) || !isset($getData['from'])) {
            exit('非法请求');
        }
        $type = $getData['type'];
        switch ($getData['from']) {
            case ($getData['from'] == 'nameauth')://实名
                $current_code = 2;
                break;
            case ($getData['from'] == 'workinfo')://工作
                $current_code = 3;
                break;
            case ($getData['from'] == 'contacts')://联系人
                $current_code = 5;
                break;
            case ($getData['from'] == 'pic')://自拍
                $current_code = 4;
                break;
            case ($getData['from'] == 'phoneauth')://运营商
                $current_code = 7;
                break;
            case ($getData['from'] == 'jingdong')://京东
                $current_code = 8;
                break;
            case ($getData['from'] == 'xyk')://京东
                $current_code = 13;
                break;
            default:
                exit('非法请求');
        }
        $order = $userinfo->getPerfectOrder($userinfo->user_id, 4, $current_code, $type);
        $orderInfo = (new Common())->create3Des(json_encode($order, true));
        $nextPage = $order['nextPage'] . '?orderinfo=' . urlencode($orderInfo);
        return $this->redirect($nextPage);
    }

    /**
     * 冻结页面
     * @return string
     */
    public function actionBlack()
    {
        $this->layout = "renzhen";
        $this->getView()->title = "您提交的信息不符合规则，该账户已被冻结";
        $jsinfo = $this->getWxParam();
        return $this->render('black', ['jsinfo' => $jsinfo]);
    }

    /**
     * 京东认证页面
     */
    public function actionJingdong()
    {
        $this->layout = 'data';
        $orderinfo = $this->get('orderinfo');
        if (!$orderinfo) {
            exit;
        }
        $nextPage = $this->nextUrl($orderinfo, 8);
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);

        return $this->render('jingdong', array(
            'user' => $userInfo,
            'csrf' => $this->getCsrf(),
            'nextPage' => $nextPage,
        ));
    }

    /**
     * 京东认证方法
     */
    public function actionJindongajax()
    {
        $postData = $this->post();
        $user_id = $postData['user_id'];
        $userInfo = User::findOne($user_id);
        if (!$userInfo) {
            echo json_encode(array('step' => 3, 'process_msg' => '授权错误，请稍后重试'));
            exit;
        }

        $juxinliModel = new Juxinli();
        $postData['get_type'] = 2;
        $key = Yii::$app->params['app_3des_key'];
        $pwd = Crypt3Des::encrypt($postData['password'], $key);
        $postData['pwd'] = $pwd;
        unset($postData['_csrf']);
        unset($postData['user_id']);
        $ret = $juxinliModel->juxinli($userInfo, $postData);
        if (!$ret || $ret['rsp_code'] != '0000') {
            echo json_encode(array('step' => 3, 'process_msg' => '网络错误，请稍后重试'));
            exit;
        }
        unset($ret['rsp_code']);
        unset($ret['process_code']);
        echo json_encode($ret);
        exit;
    }

    /**
     * 优惠券
     */
    public function actionCoupon()
    {
        $this->layout = "newmain";
        $this->getView()->title = "优惠券";
        $now_time = date('Y-m-d H:i:s');
        $user = $this->getUser();
        $user_id = $user->user_id;
        if (!empty($user_id)) {
            //查询可用的优惠券
            $mobile = User::find()->select('mobile')->where(['user_id' => $user_id])->asArray()->one();
            $mobile = implode(',', $mobile);
            $sql = "select id,title,val,`limit`,end_date,status,create_time,@type:=1 from " . Coupon_list::tableName() . " where mobile='" . $mobile . "' and status=1 and end_date>'$now_time' order by create_time desc";
            $data = Yii::$app->db->createCommand($sql)->queryAll();
        }
        if (!empty($data)) {
            return $this->render('coupon', [
                'couponlist' => $data,
            ]);
        } else {
            $this->layout = "loan";
            return $this->render('nocoupon', [
            ]);
        }
    }

    /**
     * 使用说明
     * @return string
     */
    public function actionUsehelp()
    {
        $this->layout = 'newmain';
        $this->getView()->title = "使用规则";
        return $this->render('usehelp');
    }

    /**
     * 获取csrf
     * @return string
     */
    private function getCsrf()
    {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }

}