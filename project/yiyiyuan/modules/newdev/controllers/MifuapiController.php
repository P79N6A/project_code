<?php

namespace app\modules\newdev\controllers;

use app\common\ApiClientCrypt;
use app\models\news\Areas;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\models\news\User_loan_cg;
use app\models\news\Payaccount;
use Yii;
use yii\web\Controller;
use app\commonapi\Logger;
use app\commonapi\Crypt3Des;

class MifuapiController extends NewdevController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }

    /**
     * 下载地址
     * @return string
     */
    public function actionIndex()
    {
        $return_infos = array();
        $postData = $this->post();
        if(empty($postData)){
            $down_url_info['err'] = "数据不能为空" ;
            $down_url_info['res_code'] = "1001" ;
            echo json_encode($down_url_info);return false;
        }
       $data_info = Crypt3Des::decrypt(json_encode($postData), "24BEFILOPQRUVWXcdhntvwxy");
        $sql = "select * from yi_app_version ORDER BY id desc";
        $model = Yii::$app->db->createCommand($sql)->queryOne();
        $down_url_info = array('IOS'=>'http://a.app.qq.com/o/simple.jsp?pkgname=com.xianhuahua.yiyiyuan_1',
                                'Android' => $model['download_url'],
            );
        $return_info = Crypt3Des::encrypt(json_encode($down_url_info), "24BEFILOPQRUVWXcdhntvwxy");
        $return_infos['res_code'] = "0000";
        $return_infos['data'] = $return_info;
        echo json_encode($return_infos);
    }

   /**
     * 用户是否在一亿元存管开户
     * @return string
     */
    public function actionIspayaccount()
    {

        $return_infos = array();
        $postData = file_get_contents('php://input');
        Logger::errorLog(print_r($postData, true), 'mifuapi', 'ispayaccount');
        if(empty($postData)){
            $down_url_info['res_code'] = '数据不能为空' ;
            $down_url_info['data'] = "1001" ;
            echo json_encode($down_url_info);return false;
        }
        $mobile = Crypt3Des::decrypt($postData, (new ApiClientCrypt())->getKey());
        // print_r($mobile);die;
        if(empty($mobile)){
            $return_infos['res_code'] = "1001";
            $return_infos['data'] = '非法请求';
            echo json_encode($return_infos);
            exit;
        }
        $userInfo = User::find()->where(['mobile' => $mobile])->one();
        $isPay = 0;
        if(empty($userInfo)){
            $return_info = Crypt3Des::encrypt(json_encode($isPay), "24BEFILOPQRUVWXcdhntvwxy");
            $return_infos['res_code'] = "0000";
            $return_infos['data'] = $return_info;
            echo json_encode($return_infos);
            die;
        }
        $payAccountInfo = Payaccount::find()->where(['type' => 2,'step' => 1, 'activate_result' => 1])->one();
        if(!empty($payAccountInfo)){
            $isPay = 1;
        }

        $return_info = Crypt3Des::encrypt(json_encode($isPay), "24BEFILOPQRUVWXcdhntvwxy");
        $return_infos['res_code'] = "0000";
        $return_infos['data'] = $return_info;
        echo json_encode($return_infos);
    }

    /**
     * 获取用户详细信息
     * @return string
     */
    public function actionGetuserinfo(){
//       echo Crypt3Des::encrypt('5', (new ApiClientCrypt())->getKey());

        $postData= file_get_contents('php://input');
//        $postData=$this->post();
        if(empty($postData)){
            $return_infos['res_code'] = "0001";
            $return_infos['message'] = '非法请求,缺少参数';
            echo json_encode($return_infos);
            exit;
        }
        $loan_id = Crypt3Des::decrypt($postData, (new ApiClientCrypt())->getKey());
        if(empty($loan_id)){
            $return_infos['res_code'] = "0001";
            $return_infos['message'] = '非法请求';
            echo json_encode($return_infos);
            exit;
        }
        if($loan_id<150000){
            $oUserLoan=User_loan_cg::find()->where(['loan_id'=>$loan_id])->one();
        }else{
            $oUserLoan=User_loan::find()->where(['loan_id'=>$loan_id])->one();
        }
        if(empty($oUserLoan)){
            $return_infos['res_code'] = "0001";
            $return_infos['message'] = '没有借款信息';
            echo json_encode($return_infos);
            exit;
        }
        $oUserInfo=(new User())->getUserinfoByUserId($oUserLoan->user_id);
        if(empty($oUserInfo)){
            $return_infos['res_code'] = "0001";
            $return_infos['message'] = '没有该用户！';
            echo json_encode($return_infos);
            exit;
        }
        $oUserBank=User_bank::find()->where(['id'=>$oUserLoan->bank_id])->one();
        if(!empty($oUserInfo->extend->home_area)){
            $city=$this->cityname($oUserInfo->extend->home_area);
            $city=empty($city)? '' : $city;
        }else{
           $city= '';
        }
        $zxs_city_ids=[11,12,13,14];
        $data=[
            'realname'=>empty($oUserInfo->realname) ? '' :$oUserInfo->realname,
            'identity'=>empty($oUserInfo->identity) ? '' :$oUserInfo->identity,
            'age'=>empty($oUserInfo->identity) ? '' :$this->howold($oUserInfo->identity),
            'sex'=>empty($oUserInfo->identity) ? '' :$this->get_xingbie($oUserInfo->identity),
            'mobile'=>$oUserInfo->mobile,
            'income'=>empty($oUserInfo->extend->income) ? '' :$oUserInfo->extend->income,
            'city'=>$city,
            'zxs_city_ids'=>$zxs_city_ids,
            'contacts_mobile'=>empty($oUserInfo->favorite->mobile) ? '' : $oUserInfo->favorite->mobile,
            'contacts_phone'=>empty($oUserInfo->favorite->phone) ? '' : $oUserInfo->favorite->phone,
            'company'=>empty($oUserInfo->company) ? '' :$oUserInfo->company,
            'userbank'=>empty($oUserBank) ? '' : $oUserBank->bank_name,
        ];
        $return_infos['res_code'] = "0000";
        $return_infos['data'] = $data;
        echo json_encode($return_infos);
    }
    private function cityname($code){
        if (strlen($code) < 2 || strlen($code) > 6) {
            return false;
        }
        $area = Areas::find()->where(['code' => $code])->one();
        $area_name = [];
        switch (strlen($code)) {
            case 2:
                $area_name['province_name']=!empty($area) ? $area->name : NULL;
                $area_name['proId']=$code ;
                $area_name['city_name']=!empty($area) ? $area->name : NULL;
                $area_name['cityId']=$code;
                break;
            case 4:
                $city_name = !empty($area) ? $area->name : NULL;
                $proId = substr($code, 0, 2);
                $province = Areas::find()->where(['code' => $proId])->one();
                $province_name = !empty($province) ? $province->name : NULL;
                $area_name['province_name']=$province_name ;
                $area_name['proId']=$proId ;
                $area_name['city_name']=$city_name;
                $area_name['cityId']=$code;
                break;
            case 6:
                $areas_n = !empty($area) ? $area->name : NULL;
                $cityId = substr($code, 0, 4);
                $city = Areas::find()->where(['code' => $cityId])->one();
                $city_name = !empty($city) ? $city->name : NULL;
                $proId = substr($code, 0, 2);
                $province = Areas::find()->where(['code' => $proId])->one();
                $province_name = !empty($province) ? $province->name : NULL;
//                $area_name .=$province_name . $city_name . $areas_n;
                $area_name['province_name']=$province_name ;
                $area_name['proId']=$proId ;
                $area_name['city_name']=$city_name;
                $area_name['cityId']=$cityId;
                break;
        }
        return $area_name;
    }

    /**
     * 根据身份证计算年龄
     * @return string
     */
    private function howold($cid){
        if(empty($cid)){
            return false;
        }
        $sub_str = substr($cid,6,4);
        $now = date("Y",time());
        return $now-$sub_str;
    }
    /**
     * 根据身份证计算性别
     * @return string
     */
     private function get_xingbie($cid) {
        if(empty($cid)){
            return false;
        }
        $sexint = (int)substr($cid,16,1);
        return $sexint % 2 === 0 ? '女' : '男';
    }
}