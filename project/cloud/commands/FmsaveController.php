<?php 
namespace app\commands;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\xs\XsApi;
use app\models\yyy\YiUser;
use app\models\yyy\YiAddress;
use app\models\yyy\YiUserCreditList;
use app\models\yyy\YiFavoriteContacts;

/**
 * 同盾数据恢复接口
 * 本地测试：/usr/local/bin/php /data/wwwroot/test/cloud/yii  tellab tagtest
 */
class FmsaveController extends BaseController
{
	private $file_path;
    public function init() {
        $this->file_path = Yii::$app->basePath . '/commands/data/';
    }

    # 读取CSV
    private function readCsv($key)
    {
        $path_arr = ['1'=>'fm.csv'];
        $path = $path_arr[$key];
        $file_path = $this->file_path.$path;
        $file = fopen($file_path,'r');
        $n = 0;
        $value = [];
        while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
            if ($n === 0) {
                $key = $data;
            } else {
                $arr = [
                    $key['0'] => $data['0'],
                    $key['1'] => $data['1'],
                ];
                $value[] = $arr;
            }
            $n++;
        }
        $count = count($value);
        return $value;
    }
    // 号码标签
    public function runFm($key = 1)
    {   
    	$oXsApi  = new XsApi();
        # 获取需要跑标签的用户
        $userList = $this->readCsv($key);
        if (empty($userList)) {
            Logger::dayLog('tellab/runTag', 'nothing to deal with');
            die('nothing to deal with');
        }
        // if (!SYSTEM_PROD) {
        // 	$userList = [0=>[
        // 		'req_id'=> 472,
        // 		'user_id' => 2599989,
        // 	]];
        // }
        $n = 0;
        foreach ($userList as $user) {
        	try {
        		$user['aid'] = 1;
	            # user data
	           	$user_data = $this->getYyyInfo($user);
	            # 请求 FM
	            $res = $oXsApi->getLoanFrau($user_data,[]);
	            Logger::dayLog('fmsave', 'user_data', $user_data,'res', $res);
	            $n++;
        	} catch (\Exception $e) {
        		Logger::dayLog('fmsave/error', 'error', $e->getMessage(), $user);
        	}
            
        }
        echo $n;
        Logger::dayLog('fmsave', 'num', $n);
        return true;
    }
    public function getYiUserCreditListByReqid($data)
    {
        $oUserCreditList = new YiUserCreditList();
        $req_id = ArrayHelper::getValue($data, 'req_id');
        $where = ['req_id'=>$req_id];
        $user_credit = $oUserCreditList->getUserCredit($where);
        return $user_credit;
    }
	/**
     * [getYyyInfo 一亿元用户数据]
     * @param  [type] $data_set [description]
     * @return [type]           [description]
     */
    public function getYyyInfo($data_set){
        if (empty($data_set)) {
            return [];
        }
        $user_id = ArrayHelper::getValue($data_set, 'user_id');
        //user表信息
        $oUser = new YiUser();
        $user_info = $oUser->getUser(['user_id' => $user_id]);
        if (empty($user_info)) {
            Logger::dayLog('YyyApi/getUserInfoAll', $data_set, '用户不存在');
            return [];
        }
        $user_extend = $user_info->userExtend;
        //地址
        $oAddress = new YiAddress();
        $address_info = $oAddress->getAddressByUserId($user_id);
        // yi_user_credit(token_id,source)
        $user_credit = $this->getYiUserCreditListByReqid($data_set);
        // user_loan_extend(uuid,userIp)
        $source = ArrayHelper::getValue($user_credit, 'device_type','000');
        // yi_favorite_contacts
        $relation = $this->getYyyRelation($user_id);
        $device_map = ['1'=> 'web','3'=> 'ios','4'=>'android','5'=> 'android'];
        $uesr_data = [
            'credit_id' => ArrayHelper::getValue($data_set, 'req_id',0),// 测评ID st_strategy_request
            'user_id' => ArrayHelper::getValue($user_info, 'user_id'),// 一亿元 user_id
            'identity' => ArrayHelper::getValue($user_info, 'identity'),
            'mobile' => ArrayHelper::getValue($user_info, 'mobile'),
            'realname' => ArrayHelper::getValue($user_info, 'realname'),
            'telephone' => ArrayHelper::getValue($user_info, 'telephone'),
            'reg_time' => ArrayHelper::getValue($user_info, 'create_time'),
            'query_time' => date('Y-m-d H:i:s'),
            // 同盾所需数据
            'identity_id' => ArrayHelper::getValue($user_info, 'user_id'),// 一亿元 user_id
            'idcard' => ArrayHelper::getValue($user_info, 'identity'),
            'phone' => ArrayHelper::getValue($user_info, 'mobile'),
            'name' => ArrayHelper::getValue($user_info, 'realname'),
            'ip' => ArrayHelper::getValue($user_credit, 'device_ip'), //ip地址
            'device' => ArrayHelper::getValue($user_credit, 'uuid'), // 设备号
            'source' => (string)$source, //
            'xhh_apps' => (string)ArrayHelper::getValue($device_map, $source,'web'), //来源ios,android,web,....
            'token_id' => ArrayHelper::getValue($user_credit, 'device_tokens'),// app编号
            'black_box' => ArrayHelper::getValue($user_credit, 'black_box'),// 设备指纹
            'aid' => ArrayHelper::getValue($data_set, 'aid'),
            'req_id' => ArrayHelper::getValue($data_set, 'req_id'),
            'come_from' => ArrayHelper::getValue($user_info, 'come_from'),
            // 公司与学校信息
            'company_name' => ArrayHelper::getValue($user_extend, 'company'),
            'company_industry' => (string)ArrayHelper::getValue($user_extend, 'industry'), // 选填 行业
            'company_position' => ArrayHelper::getValue($user_extend, 'position'), // 选填 职位
            'company_phone' => ArrayHelper::getValue($user_extend, 'telephone'), // 选填 公司电话
            'company_address' => ArrayHelper::getValue($user_extend, 'company_address'), // 选填 公司地址
            'school_name' => ArrayHelper::getValue($user_extend, 'school'), // 选填 学校名称
            'school_time' => ArrayHelper::getValue($user_extend, 'school_time'), // 选填 入学时间
            'edu' => ArrayHelper::getValue($user_extend, 'edu'), // 选填 本科,研究生
            'latitude' => ArrayHelper::getValue($address_info, 'latitude'), // 维度
            'longtitude' => ArrayHelper::getValue($address_info, 'longitude'), // 经度
            'accuracy' => "", // 精度
            'speed' => "", //速度
            'location' => ArrayHelper::getValue($address_info, 'address'), //地址
            'relation' => $relation,
        ];
        return array_merge($data_set, $uesr_data);
    }

    private function getYyyRelation($user_id){
        $contact = (new YiFavoriteContacts)->getFavorite($user_id);
        if (empty($contact)) {
            return '';
        }
        $relation = [
                'mobile' => ArrayHelper::getValue($contact,'mobile',''),
                'phone' => ArrayHelper::getValue($contact,'phone',''),
            ];
        return json_encode($relation);
    }
}