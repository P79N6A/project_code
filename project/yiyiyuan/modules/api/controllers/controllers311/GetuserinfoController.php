<?php

namespace app\modules\api\controllers\controllers311;

use app\commonapi\Common;
use app\commonapi\Crypt3Des;
use app\commonapi\ImageHandler;
use app\commonapi\Keywords;
use app\models\news\Coupon_list;
use app\models\news\Favorite_contacts;
use app\models\news\Information_logs;
use app\models\news\Juxinli;
use app\models\news\Payaccount;
use app\models\news\ScanTimes;
use app\commonapi\Logger;
use app\models\news\Selection_bankflow;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_extend;
use app\models\news\User_password;
use app\models\news\User_quota_new;
use app\models\news\Selection;
use app\models\news\Video_auth;
use app\models\news\WarnMessageList;
use app\models\news\SystemMessageList;
use app\models\news\MessageApply;
use app\modules\api\common\ApiController;
use app\models\news\User_label;
use yii\helpers\ArrayHelper;
use Yii;

class GetuserinfoController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {

        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');

        if (empty($version) || empty($user_id)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }

        if (!preg_match("/^[1-9]\d*$/", $user_id)) {
            $array = $this->returnBack('99996');
            echo $array;
            exit;
        }
        $user = new User();
        $userinfo = $user->getUserinfoByUserId($user_id);
        //用户未注册，提示用户取注册
        if (empty($userinfo)) {
            $array = $this->returnBack('10001');
            echo $array;
            exit;
        } else {
            $userWx = $this->getUserwx($userinfo);
            $bank = $this->getBank($user_id);
            $debit_num = $this->getBank($user_id, 0);
            $coupon = $this->getCouponNum($userinfo->mobile);
            $is_paypassword = $this->getPassword($userinfo);
            $juxinliModel = new Juxinli();
            $juxinli = $juxinliModel->getJuxinliByUserId($userinfo->user_id);
            $juli = 0;
            if (empty($juxinli) || $juxinli->process_code != '10008') {
                    $juli = 1;
            } else {
                if($juxinli->process_code == '10008' && date('Y-m-d H:i:s', strtotime('-4 month')) >= $juxinli->last_modify_time){
                    $juli = 3;
                }else{
                    $juli = 2;
                }
            }
            $xindiao = \app\commonapi\Keywords::xindiao();
            if (!empty($xindiao) && in_array($userinfo->mobile, $xindiao)) {
                $juli = 2;
            }
//            $jingdong = $juxinliModel->getJuxinliByUserId($userinfo->user_id, 2);
//            if (!empty($jingdong) && $jingdong->process_code == '10008') {
//                $jingdong = 2;
//            } else {
//                $jingdong = 1;
//            }

            $favorite = new Favorite_contacts();
            $fav = $favorite->getFavoriteByUserId($userinfo->user_id);
            $contacts = !empty($fav) && !empty($fav->relation_common) ? 1 : 2;
            $inspectOpen=Keywords::inspectOpen();
            if($inspectOpen==1){
                $sacnTimesModel = new ScanTimes();
                $sync = $sacnTimesModel->getByMobileType($userinfo->mobile, 23);
                $isShow = !empty($sync) ? 0 : 1;
            }else{
                $isShow =0;
            }

            $array = $this->reback($userWx, $userinfo, $coupon, $bank, $is_paypassword, $debit_num, $juli, $contacts, $isShow);
            $array = $this->getStatus($userinfo, $array);
            $array['invite_code'] = $userinfo->invite_code;
            //监管开关
            $array['inspectopen'] = $inspectOpen;
            //是否为系统指定后置用户
            $charge = (new User_label())->isChargeUser($userinfo->mobile);
            if ($charge) {
                $array['charge'] = 1;
            } else {
                $array['charge'] = 0;
            }
            $array['g_uid'] = $userinfo->user_id ? Crypt3Des::encrypt($userinfo->user_id) : '';
            //选填资料认证状态
            $array['edu_valid'] = $this->getValid($user_id, 1);
            $array['social_valid'] = $this->getValid($user_id, 2);
            $array['fund_valid'] = $this->getValid($user_id, 3);
            $array['jd_valid'] = $this->getValid($user_id, 4);
            $array['taobao_valid'] = $this->getValid($user_id, 6);
            $array['bank_flow_valid'] = $this->getValid($user_id, 7);
//            $array['bank_flow_valid'] = $this->getBankFlowValid($user_id);
            $userbank = User_bank::find()->where(['user_id' => $user_id, 'status' => 1, 'type' => 1])->one();
            $array['bank_valid'] = empty($userbank) ? 1 : 2;
            //提醒消息
            $warnmsg = WarnMessageList::find()->where(['user_id' => $user_id, 'read_status' => 0, 'status' => [2, 3], 'is_show' => 1])->count();

            //系统消息(未读)
            $msg_res = $this->pullMsg($user_id);
            $read_status = 0;
            $sysmsgpart_count = (new SystemMessageList())->getSysmsgcount($user_id, $read_status);
            $array['message_count'] = $sysmsgpart_count + $warnmsg;

            //侧拉栏奖品和优惠券h5页面地址
            $array['prize_url'] = Yii::$app->request->hostInfo . '/new/prize/index?user_id=' . $user_id;
            $array['coupon_url'] = Yii::$app->request->hostInfo . '/new/coupon/couponlist?user_id=' . $user_id;

            //信用卡绑定页是否在借款流程中显示
            $bankScanTimesObj = (new ScanTimes())->getByMobileType($userinfo->mobile, 24);
            $array['credit_bank_show'] = 2; //不显示
            if (empty($bankScanTimesObj)) {
                $array['credit_bank_show'] = 1; //显示
            }

            $array = $this->returnBack('0000', $array);
            echo $array;
            exit;
        }
    }

    /**
     * 读取消息数量
     * @param $mobile
     * @return bool
     */
    public function pullMsg($user_id) {
        if (empty($user_id)) {
            return false;
        }
        $o_user = (new User())->getById($user_id);
        if(empty($o_user)){
            return false;
        }
        $where = [
            'AND',
            ['<', 'send_time', date('Y-m-d H:i:s')],
            ['>', 'send_time', $o_user->create_time],
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
            $data[$key][0] = $value;
            $data[$key][1] = $apply_val->title;
            $data[$key][2] = $apply_val->contact;
            $data[$key][3] = $user_id;
            $data[$key][4] = 0;
            $data[$key][5] = date('Y-m-d H:i:s');
            $data[$key][6] = date('Y-m-d H:i:s');
            $data[$key][7] = 0;
            $data[$key][8] = $apply_val->send_time;
        }

        $res_insert = Yii::$app->db->createCommand()->batchInsert(
                        SystemMessageList::tableName(), ['mid', 'title', 'contact', 'user_id', 'read_status', 'create_time', 'last_modify_time', 'version', 'send_time'], $data)->execute();
        if (!$res_insert) {
            Logger::dayLog('api/getuserinfo', '写入systemmessagelist数据失败' . $user_id, $res_insert);
        }

        return true;
    }

    private function getValid($userId, $type = 1) {
        if (empty($userId)) {
            return 1;
        }
        $selectionObj = (new Selection())->getByUserIdAndTpey($userId, $type);
        if (empty($selectionObj)) {
            return 1;
        }
        if ($selectionObj->process_code == '10002') {
            return 3;
        }
        if ($selectionObj->process_code != '10008') {
            return 1;
        }
        if( in_array($type, [2,3,4,6,7]) ){ //社保 公积金 京东 淘宝 银行流水 判断3个月有效期
            if ($selectionObj->process_code == '10008' && (date('Y-m-d H:i:s', strtotime('-3 month')) >= $selectionObj->last_modify_time)) {
                return 4;
            }
        }
        return 2;
    }

    private function getBankFlowValid($user_id){
        if (empty($user_id)) {
            return 1;
        }
        $selectionObj = (new Selection_bankflow())->getByUserId($user_id);
        if (empty($selectionObj)) {
            return 1;
        }
        if ($selectionObj->process_code == '10002') {
            return 3;
        }
        if ($selectionObj->process_code != '10008') {
            return 1;
        }
        if ($selectionObj->process_code == '10008' && (date('Y-m-d H:i:s', strtotime('-3 month')) >= $selectionObj->last_modify_time)) {
            return 4;
        }
        return 2;
    }

    /**
     * 获取用户借款优惠券和收益券的总数
     * @param type $mobile
     */
    private function getCouponNum($mobile) {
        $coupon = new Coupon_list();
        $coupon_list = $coupon->getCouponByMobile($mobile);
        $frcoupon = empty($coupon_list) ? 0 : count($coupon_list);
//        $stand = new Standard_coupon_list();
//        $stand_list = $stand->getStandByMobile($mobile);
//        $tzcoupon = empty($stand_list) ? 0 : count($stand_list);
        $coupon_num = $frcoupon; // + $tzcoupon;
        return $coupon_num;
    }

    /**
     * 获取用户微信信息
     * @param object $user 
     */
    private function getUserwx($user) {
        if (empty($user->openid)) {
            return null;
        } else {
            $user_wx = $user->userwx;
            return !empty($user_wx) ? $user_wx : null;
        }
    }

    private function getPassword($user) {
        $password = new User_password();
        $userpassword = $password->getUserPassword($user->user_id);
        if (empty($userpassword) || empty($userpassword->pay_password)) {
            $is_password = 'NO';
        } else {
            $is_password = 'YES';
        }
        return $is_password;
    }

    /**
     * 获取用户银行卡信息
     * @param int $user_id
     */
    private function getBank($user_id, $type = 2) {
        $bank = new User_bank();
        if ($type == 2) {
            $bank_list = $bank->getBankByUserId($user_id);
        } else {
            $bank_list = $bank->getBankByUserId($user_id, $type);
        }
        $bank_num = empty($bank_list) ? 0 : count($bank_list);
        return $bank_num;
    }

    /**
     * 组合返回的数据
     * @param $code
     * @param $user_wx
     * @param $user
     * @param $couponNum
     * @param $bank
     * @param $is_paypassword
     * @param $debit_num
     * @param $juli
     * @param $contacts
     * @return mixed
     */
    private function reback($user_wx, $user, $couponNum, $bank, $is_paypassword, $debit_num, $juli, $contacts, $isShow) {
        $user_extend = User_extend::getUserExtend($user->user_id);
//        $array['work_valid'] = !empty($user_extend->income) && $user_extend->is_new == 1 ? 2 : 1;
        $array['nickname'] = !empty($user_wx) ? $user_wx->nickname : '';
        $array['realname'] = !empty($user->realname) ? $user->realname : '';
        $array['user_type'] = $user->user_type;
        $array['mobile'] = $user->mobile;
        $array['head_url'] = !empty($user_wx) ? $user_wx->head : '';
        $array['current_amount'] = (new User_quota_new())->getQuotaByUserId($user->user_id); //信用额度
        $array['guarantee_amount'] = 2500; //担保额度
        $array['coupon_count'] = $couponNum;
        $array['bank_card_count'] = $bank;
        $array['debit_num'] = $debit_num;
        $array['identity_valid'] = $user->identity_valid == 2 || $user->identity_valid == 4 ? 2 : 1;
        $array['status'] = $user->status;
        $array['contacts'] = $contacts;
        $array['juxinli'] = $juli;
//        $array['jingdong'] = $jingdong;
        $array['loan_juxinli'] = $juli == 2 ? 2 : 1;
        $array['area_version'] = '1.2';
        $array['isPassing'] = '0';
        $array['is_msg_sync'] = $isShow;
        $array['msg_sync'] = $isShow == 1 ? '检测到您是智融钥匙用户，为了保证您的信息安全，5秒后将为您同步信用资料，同步后可立即发起借款。' : '';
        return $array;
    }

    private function getStatus($user, $array) {
        if ($user->status == 3) {
            $array['pic_valid'] = 2;
            $user_extend = User_extend::getUserExtend($user->user_id);
            $array['identity_valid'] =  $user_extend->is_new == 1 ? 2 : 1;
            if(empty($user_extend->company)){
                $array['identity_valid'] = 1;//缺少公司信息返回1
            }
        } else {
            $inforModel = new Information_logs();
            $redis_mark_iden = $inforModel->getMark($user, 1);
//            $redis_mark_pic = $inforModel->getMark($user, 2);
            $artificial_video=$inforModel->getVideoAuthCount($user->user_id);
            $array['artificial_video']=$artificial_video;
            if ($user->status == 5) {
                $array['pic_valid'] = 3;
            }else if($user->status==2){
                $array['pic_valid'] = 4;
            }else {
                $array['pic_valid'] = 1;
            }
            if ($user->identity_valid == 2) {
                $passModel = new User_password();
                $pass = $passModel->getUserPassword($user->user_id);
                $path = !empty($pass) ? (new ImageHandler)->img_domain_url.$pass->iden_url : '';
                if (!empty($pass) && !empty($pass->iden_url) && @fopen($path, 'r')) {
                    $array['identity_valid'] = 2;
                } else {
                    if (!$redis_mark_iden) {
                        $array['identity_valid'] = 3;
                        $array['status'] = 1;
                    } else {
                        $array['identity_valid'] = 1;
                        $array['status'] = 1;
                    }
                }
            } else {
                if (!$redis_mark_iden) {
                    $array['identity_valid'] = 3;
                    $array['status'] = 1;
                } else {
                    $array['identity_valid'] = 1;
                    $array['status'] = 1;
                }
            }
        }
        return $array;
    }

}
