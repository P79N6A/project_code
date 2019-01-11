<?php
namespace app\modules\api\controllers\controllers314;

use app\commonapi\Crypt3Des;
use app\models\news\User;
use app\models\news\Juxinli;
use app\models\news\Favorite_contacts;
use app\modules\api\common\ApiController;
use app\commonapi\Apihttp;
use app\commonapi\Logger;
use Yii;

class JuxinliController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $type = Yii::$app->request->post('type');
        $get_type = Yii::$app->request->post('get_type');
        $password = Yii::$app->request->post('password');
        $user_name = Yii::$app->request->post('user_name');
        $get_type = empty($get_type) ? 1 : $get_type;
        if (empty($version) || empty($user_id)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }

        if ($get_type == 2 && empty($user_name) && empty($type)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $user = User::findOne($user_id);
        if (empty($user)) {
            $array = $this->returnBack('10001');
            echo $array;
            exit;
        } elseif ($user->status != 3) {
            $array = $this->returnBack('10073');
            echo $array;
            exit;
        }
        $postData = Yii::$app->request->post();
        if($get_type == 2){
            $key = Yii::$app->params['app_3des_key'];
            $pwd = $password;
            $password = Crypt3Des::decrypt($password, $key);
            $postData['pwd'] = $pwd;
            $postData['password'] = $password;
        }
        if ($get_type == 1){
            //日志统计 
            $info = $_SERVER;
            if(!empty($info) && isset($info['HTTP_USER_AGENT'])){
                $user_agent = $info['HTTP_USER_AGENT'];
            }else{
                $user_agent = '';
            }
            Logger::dayLog('app/juxinli', $user['user_id'], $user_agent);
            $this->juxinliApi($user);
        }elseif ($get_type == 2){
            $juxinliModel = new Juxinli();
            $array = $juxinliModel->juxinli($user ,$postData);
            $array = $this->returnBack($array['rsp_code']);
            echo $array;
        }
    }
    private function juxinliApi($user){
        //运营商风控
        $report_data = [
            'user_id' => $user->user_id,
            'loan_id' => '',
            'mobile' => $user->mobile,
            'aid' => 1,
        ];
        $report_result = (new Apihttp())->postReport($report_data);
        $report_result = json_decode($report_result, true);
        $source = '';
        if (!empty($report_result) && $report_result['rsp_code'] == '0000' && !empty($report_result['result'])) {
            Logger::dayLog('app/juxinli','运营商风控',$report_result['result']);
//            $source = $report_result['result'];
        }

        $postData = [
            'phone' => $user->mobile,
            'name' => $user->realname,
            'idcard' => $user->identity,
            'from' => 2,
            'callbackurl' => Yii::$app->params['webcallback'],
            'source' => $source
        ];

        $fav = (new Favorite_contacts())->getFavoriteByUserId($user->user_id);
        $contacts = '';
        if (!empty($fav)) {
            $contacts = json_encode(array(
                array(
                    'contact_tel' => $fav->phone,
                    'contact_name' => $fav->relatives_name,
                    'contact_type' => '0', //亲属
                ),
                array(
                    'contact_tel' => $fav->mobile,
                    'contact_name' => $fav->contacts_name,
                    'contact_type' => '6', //常用联系人
                )
            ));
        }
        if (!empty($contacts)) {
            $postData['contacts'] = $contacts;
        }

        $result = (new Apihttp())->postGrabRouteNew($postData);
        if($result['res_code'] != 0){
            $array['process_msg'] = $result['res_data'];
            $array = $this->returnBack($result['res_code'], $array, $result['res_data']);
            echo $array;
            exit;
        }else{
            if($result['res_data']['status'] == 1 || $result['res_data']['status'] == 4){
                $res = $this->addJxl($result, $user, '10008',$source);
                if(!$res){
                    $array = $this->returnBack('99995');
                    echo $array;
                    exit;
                }
                $array['status'] = $result['res_data']['status'];
                $array['process_msg'] = '采集成功';
                $array = $this->returnBack('0000', $array);
                echo $array;
                exit;
            }elseif ($result['res_data']['status'] == 0){
                $res = $this->addJxl($result, $user, '', $source);
                if(!$res){
                    $array = $this->returnBack('99995');
                    echo $array;
                    exit;
                }
                $array['status'] = $result['res_data']['status'];
                $array['process_msg'] = '开始认证';
                $array['url'] = $result['res_data']['url'];
                $array = $this->returnBack('0000', $array);
                echo $array;
                exit;
            }elseif ($result['res_data']['status'] == 3){
                $res = $this->addJxl($result, $user, '30000', $source);
                if(!$res){
                    $array = $this->returnBack('99995');
                    echo $array;
                    exit;
                }
                $array['status'] = $result['res_data']['status'];
                $array['process_msg'] = '采集失败';
                $array = $this->returnBack('0000', $array);
                echo $array;
                exit;
            }
        }
    }

    private function addJxl($result, $user, $process_code = null, $source = ''){
        $condition = [
            'requestid' => !empty($result['res_data']['requestid'])?$result['res_data']['requestid']:'',
            'user_id' => $user->user_id,
            'source' => !empty($result['res_data']['source']) ? $result['res_data']['source'] : $source,
            'type' => 1
        ];
        if(!empty($process_code)){
            $condition['process_code'] = $process_code;
        }
        if($process_code == '10008'){
            $condition['status'] = '1';
        }
        $res = (new Juxinli())->save_juxinli($condition);
        return $res;
    }
}
