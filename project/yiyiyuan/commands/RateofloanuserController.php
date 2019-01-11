<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/7
 * Time: 10:26
 */
namespace app\commands;
use app\commonapi\AES128;
use app\commonapi\Apihttp;
use app\commonapi\ImageHandler;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\commonapi\Http;
use app\models\news\Address;
use app\models\news\Black_list;
use app\models\news\Favorite_contacts;
use app\models\news\Thirdinformation;
use app\models\news\User;
use app\models\news\User_extend;
use app\models\news\User_history_info;
use app\models\news\User_password;
use app\models\own\Address_list;
use app\models\news\Card_bin;
use app\models\news\User_bank;
use app\models\news\ShuCheckUser;
use app\models\xs\XsApi;
use app\common\Areas as Areas1;
use yii\console\Controller;
use Yii;

class RateofloanuserController extends Controller
{
    private $success_code = 0;
    private $backFile;
    private $score = 30;
    private $secret_key = '55842d9a18fe5cf029d74aab1338bc76';
    /**
     * 入口
     */
    public function actionIndex()
    {
        $limit = 500;
        $where_config = [
            'AND',
            ['status'=>2]
        ];
        $sql = ShuCheckUser::find()->where($where_config)->orderBy("create_time ASC");
        $user_data = $sql->limit($limit)->asArray()->all();
        $http_url = 'http://dev.tianshenjr.com/Partner/flow/getUserAuthenticationInfo';
        if (!empty($user_data)){
            foreach($user_data as $key => $value){
                $val = [
                    "merchant_id" => "xianhua",
                    "id_number" => $value['identity'],
                    "token" => $value['token'],
                    "secret_key" => $this->secret_key,
                    //"sign" => ''
                ];
                $sign = $this->gen_sign($val,$this->secret_key);
                $val['sign'] = $sign;
                $val = json_encode($val);
                Logger::errorLog(print_r(array($value), true), 'Rateofloanuser', 'rateofloan');
                $ret = Http::interface_post($http_url, $val);
                Logger::errorLog(print_r(array($ret), true), 'Rateofloanuser_return', 'rateofloan');
                $ret = json_decode($ret,true);
                if (!empty($ret)) {
                    $returnData = $this->logicalProcessing($ret['data'],$value);
                    $returnInfo = $this->returnResponse($returnData);
                    Logger::errorLog(print_r(array($returnInfo), true), 'success_info', 'rateofloan');
                }     
            }
        }
    }

    /**
     * 生成签名
     * @param array $param
     * @param $secret_key
     * @return mixed
     */
    function gen_sign(array $param, $secret_key) {
        unset($param['sign']);
        ksort($param);
        foreach ($param as $key => $value) {
            if (is_array($value)) {
                $param[$key] = gen_sign($value, $secret_key);
            }
        }
        $paramString = urldecode(http_build_query($param));
        return md5($paramString . $secret_key);
    }

    /**
     * 返回信息
     * @param $data_code
     * @return mixed
     */
    public function returnResponse($data_code)
    {
        if ($data_code == 0) {
            $success_msg = [
                "code" => 0,
                "msg" => 'success',
            ];
        }else {
            $success_msg = [
                "code" => $data_code,
                "msg" => $this->returnCode($data_code),
            ];
        }
        return json_encode($success_msg);
    }

    /**
     * 逻辑处理
     * @param $data
     * @param $user_info
     * @return array|int
     */
    public function logicalProcessing($data,$user_info)
    {
        //验证手机号
        $is_mobile = $this->isMobile($data['carre_report']['user_info']['phone_no']);
        if (!$is_mobile){
            return 10019;
        }
        //验证电子邮箱
        // if (!preg_match('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/', trim($data['user_email']))) {
        //     return 10032;
        // }
        //插入用户信息获取最后一条插入的用户id;
        $returnData = $this->checkUser($data,$user_info);
        //var_dump($returnData);die;
        if ($returnData['code'] != 0){
            return $returnData['code'];
        }else{
            $user_id = $returnData['user_id'];
        }
        //插入user_extend表数据
        $user_extend_state = $this->checkUserExtend($data,$user_info,$user_id);
        if ($user_extend_state != 0){
            return $user_extend_state;
        }
        //插入user_password
        $user_password = $this->checkUserPassword($data, $user_info,$user_id);
        if ($user_password != 0){
            return $user_password;
        }

        //插入favorite_contacts
        $favorite_contacts = $this->checkFavoriteContacts($data, $user_id);
        if ($favorite_contacts != 0){
            return $favorite_contacts;
        }
        //更新手机通讯录
        $address_list = $this->checkAddressList($data, $user_id);
        if ($address_list != 0){
            return $address_list;
        }

        //设备信息
//        $thirdin_formation =  $this->checkThirdinformation($data, $user_info, $user_id);
//        if ($thirdin_formation != 0){
//            return $thirdin_formation;
//        }
        //gps更新
//        $address = $this->checkAddress($data, $user_id);
//        if ($address != 0){
//            return $address;
//        }
        //绑卡
        $bank_stat = $this->saveBankInfo($data, $user_id);
        if ($bank_stat['res_code'] != $this->success_code){
            return $bank_stat;
        }
        //注册
        $do_reg = $this->doReg($user_id);
        if ($do_reg != 0){
            return $do_reg;
        }
        //修改速达贷于一亿元关联表用户状态
        $su = $this->checkSu($user_info);
        if($su !=0){
            return $su;
        }
        //发送运营商详单
        $operator_list = $this->OperatorList($data);
        if ($operator_list['res_code'] != 0){
            return $operator_list['res_code'];
        }
        return $this->success_code;
    }

    /**
     * 向开放平台发送详单
     * @param $data
     * @param $user_info
     * @return mixed
     */
    private function OperatorList($data)
    {
        $calls = [];
        foreach ($data['carre_report']['call_history'] as $key=>$value){
            foreach ($value['details'] as $k=>$val){
                if($val['callType'] == 0){
                    $val['callType'] = "主叫";
                }else{
                    $val['callType'] = "被叫";
                }
                $calls[] = [
                    "start_time"=>  $val['startTime'],
                    "update_time"=>  date("Y-m-d H:i:s"),
                    "use_time"=>  $val['duration'],
                    "subtotal"=> $val['fee'],
                    "place"=>  $val['callLocation'],
                    "init_type"=>  $val['callType'],
                    "call_type"=>  $val['commType'],
                    "other_cell_phone"=>  $val['otherPhone'],
                    "cell_phone"=>  $data['carre_report']['user_info']['phone_no']
                ];
            }
        }
        $res = [
            "mobile" => $data['carre_report']['user_info']['phone_no'],
            "datasource"=> '123',//todo
            "from"=> 'sdd',
            "calls"=> $calls
        ];
        Logger::errorLog(print_r(array(json_encode($res)), true), 'OperatorList', 'rateofloan');
        $returnData = (new Apihttp())->postRateloaninfo(json_encode($res));
        return $returnData;
    }
    /**
     * 插入user数据
     * @param $data
     * @param $user_info
     * @return int
     */
    private function checkUser($data, $user_info)
    {
        $user_in = User::find()->where(['identity'=>$user_info['identity'], 'status'=>3]) -> one();
        //用户存在
        if (!empty($user_in)){
            $returnData['code'] = 10015;
        }else{
            //验证身份证是否合法
            if (!Http::checkIdenCard($user_info['identity'])) {
                return 10021;
            }
            //$invite_code = $this->getCode();
            $user_info_data = [
                'mobile' => $data['carre_report']['user_info']['phone_no'],
                //'invite_code' => $invite_code, //我的邀请码
                'last_login_time' => date('Y-m-d H:i:s'),
                'come_from' => 2,
                'realname' => $user_info['realname'],
                'identity' => $user_info['identity'],
                'birth_year' => intval(substr($user_info['identity'], 6, 4)),
                'identity_valid' => 2,
                'address' => $data['customer_info']['company_address'],
                'position' => '',
                'telephone' => $data['customer_info']['company_phone'],
                'industry' => '',
                'pic_identity' => $data['id_card_info']['front_idCard_url'],
                'pic_self' => $data['id_card_info']['face_url'],
                'status' => 1,
            ];
            //插入用户
            $user_ret = (new User())->addUser($user_info_data);
            if (!$user_ret){
                $returnData['code'] = 10016;
            }else{
                $returnData['code'] = $this->success_code;
                $returnData['user_id'] = Yii::$app->db->getLastInsertID();
            }
        }
        return $returnData;
    }

    /**
     * 插入user_extend数据
     * @param $data
     * @param $user_info
     * @param $user_id
     * @return array|int
     */
    private function checkUserExtend($data, $user_info, $user_id)
    {
        //验证单位电话
        // if (!preg_match('/^0\d{2,3}\-?\d{7,8}\-\d{0,3}$/', $data['customer_info']['company_phone'])) {
        //     if (!preg_match('/^1(([3578][0-9])|(47))\d{8}$/', $data['customer_info']['company_phone'])) {
        //         return 10013;
        //     }
        // }    
        /* //验证电子邮箱
        if (!preg_match('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/', trim($data['user_email']))) {
            return 10032;
        }
       
        $area_map = new Areas1();
        //验证常住省市县编码
        if (empty($area_map->getAreaCode($data['addr_detail_zone_code']))) {
            return 10033;
        }
        //验证公司常住省市县编码
        if (empty($area_map->getAreaCode($data['company_addr_detail_zone_code']))) {
            return 10034;
        }
        */
        //验证婚姻
        /*$marriage_data = [
            '1' => 1, //未婚
            '2' => 2, //已婚，无子女
            '3' => 2, //已婚，有子女
            '4' => 4, //离异
            '5' => 3, //丧偶
            '6' => 5, //复婚
            '7' => 6, //其他
        ];
        if (empty($marriage_data[$data['user_marriage']])){
            return 10035;
        }*/
        $user_extend_info = User_extend::find()->where(['user_id'=>$user_id])->one();
        $user_extend_source = [];
        if (!empty($user_extend_info)){
            $user_extend_source = [
                'user_id' =>$user_extend_info->user_id,
                //'uuid' => $user_extend_info->uuid,//app设备编号
                'home_address' => $user_extend_info->home_address, //居住地址
                //'marriage' => $user_extend_info->marriage , //婚姻状况
                'company' => $user_extend_info->company, //公司名称
                'telephone' => $user_extend_info->telephone, //公司电话
                //'company_area' => $user_extend_info->company_area, //公司地址
                'company_address' => $user_extend_info->company_address,
                'email' => $user_extend_info->email, //常用邮箱地址
                'edu' => $user_extend_info->edu,
                'position' =>$user_extend_info->position,
                /*
                'reg_ip' => $user_extend_info->reg_ip,
                'home_area' => $user_extend_info->home_area,
                'industry' => $user_extend_info->industry,
                'profession' => $user_extend_info->profession,
                'income' => $user_extend_info->income,
                'company_area' => $user_extend_info->company_area,
                */
            ];
        }
        //对比user_extend表中的数据，数据相同就不处理（uesr_extend,history_info），不相同就插入(history,)再更新user_extend
        $user_extend_object = new User_extend();
        $user_extend = $this->formatUserExtend($data, $user_info, $user_id);
        //对比是否存在改新字段
        $diff_user_extend = array_diff($user_extend, $user_extend_source);
        if (empty($diff_user_extend)){
            return $this->success_code;
        }
        $extend_state = $user_extend_object->addRecord($user_extend);
        if (!$extend_state){
            return 10022;
        }
        //更新history_info
        $history_info = $this->checkHistoryInfo($data, $user_info, $user_id);
        if ($history_info != $this->success_code){
            return $history_info;
        }
        return $this->success_code;
    }

    /**
     * 格式接收的数据
     * @param $data
     * @param $user_info
     * @param $user_id
     * @return array
     */
    private function formatUserExtend($data, $user_info, $user_id)
    {
        //验证婚姻
        /*$marriage_data = [
            '1' => 1, //未婚
            '2' => 2, //已婚，无子女
            '3' => 2, //已婚，有子女
            '4' => 4, //离异
            '5' => 3, //丧偶
            '6' => 5, //复婚
            '7' => 6, //其他
        ];
        $user_marriage = 1;
        if (in_array($data['user_marriage'], $marriage_data)){
            $user_marriage = $marriage_data[$data['user_marriage']];
        }*/
        $user_extend = [
            'user_id' =>$user_id,
            //'uuid' => empty($data['device_info_all']['uuid']) ? "" : $data['device_info_all']['uuid'],// varchar(32) DEFAULT NULL COMMENT 'app设备编号',
            'home_address' => empty($data['customer_info']['user_address']) ? "" : $data['customer_info']['user_address'], //居住地址
            //'marriage' => $user_marriage , //婚姻状况
            'company' => $data['customer_info']['company_name'], //公司名称
            'telephone' => empty($data['customer_info']['company_phone'])? '' : $data['customer_info']['company_phone'], //公司电话
            //'company_area' => $data['customer_info']['company_address_city'], //公司地址
            'company_address' => $data['customer_info']['company_address'],
            //'email' => $data['user_email'], //常用邮箱地址
            //'edu' => empty($user_info->edu)?"":$user_info->edu,
            //'position' => empty($user_info->position)?"":$user_info->position, //职位
            //'industry' => empty($data['work_industry ']) ? '' : $data['work_industry '], //行业
            //'profession' => empty($data['work_position']) ? "" : $data['work_position'],//职业
            /*
            'reg_ip' => $ip,
            'home_area' => !empty($home_area_arr) ? $home_area_arr['code'] : '110101',
            'industry' => $data['basicInfo']['industry'],
            'profession' => $data['basicInfo']['profession'],
            'income' => $data['basicInfo']['income'],
            'company_area' => !empty($company_area_arr) ? $company_area_arr['code'] : '110101',
            */
        ];
        return $user_extend;
    }

    /**
     * 插入user_password
     * @param $data
     * @param $user_info
     * @param $user_id
     * @return int
     */
    private function checkUserPassword($data, $user_info, $user_id)
    {
        //获取照片信息
        $idInfoArr = [
            'imgUrls' => [
                //身份证反面照片url
                'backFile' => $data['id_card_info']['front_idCard_url'],
                //身份证正面照片url
                'frontFile' => $data['id_card_info']['back_idCard_url'],
                //生活照url
                'natureFile' => $data['id_card_info']['face_url']
            ],
            'project' => 'yiyiyuan'
        ];
        $result = (new Apihttp())->postImg($idInfoArr);
        $result = json_decode($result, true);
        if (empty($result) || $result['res_code'] != '0') {
            return 10036;
        }
        //调用人脸识别接口
        $facedata = array(
            'identity' => $user_info['identity'],
            'pic_identity' => ImageHandler::getUrl($result['res_data']['frontFile']), //身份证正面照片
            'identity_url' => ImageHandler::getUrl($result['res_data']['natureFile'])//自拍照
        );
        //身份证背面照
        $this->backFile = $result['res_data']['natureFile'];
        $openApi = new Apihttp;
        $result_face = $openApi->faceValid($facedata);
        if ($result_face['res_code'] != '0000') {
            //return 10038;
        }
        //Logger::errorLog(print_r(array($result_face), true), 'result_face_chkdifuser', 'rateofloan');
        $user_password_object = new User_password();
        $user_password = [
            "user_id" =>$user_id,
            //"device_tokens" => $data['contacts']['device_num'], //设备编号
            //"device_type" => $data['contacts']['platform'], //设备类型
            //"iden_address" => $data['ID_Address_OCR'], //身份证地址
            //"nation" => $data['ID_Ethnic_OCR'], //民族
            "pic_url" => $result['res_data']['natureFile'], //活体图片地址
            "iden_url" => $result['res_data']['frontFile'], //身份证照片
            "score" => $result_face['res_msg']['score'], //活体验证返回分数
        ];
        $password_state = $user_password_object->addUserpassword($user_password);
        if (!$password_state){
            return 10023;
        }
        return $this->success_code;
    }

    /**
     * 插入favorite_contacts
     * @param $data
     * @param $user_id
     * @return int
     */
    private function checkFavoriteContacts($data, $user_id)
    {
        //验证直系亲属联系
        $lineal_relative = [
            '1' => 1, //配偶
            '2' => 2, //父母
            '3' => 3,
            '4' => 3,
            '5' => 3,
        ];
        if (empty($lineal_relative[$data['extro_contacts'][0]['type']])){
            return 10030;
        }
        //常用联系人关系
        $urgent_contact = [
            '1' => 5,
            '2' => 5,
            '3' => 3, // 直系
            '4' => 1, // 朋友
            '5' => 2, // 同事
        ];
        if (empty($urgent_contact[$data['extro_contacts'][1]['type']])){
            return 10030;
        }
        $favorite_contacts_object = new Favorite_contacts();
        $favorite_contacts = [
            'user_id' => $user_id,
            'relation_common' => $urgent_contact[$data['extro_contacts'][1]['type']], //常用联系人关系
            'contacts_name' => $data['extro_contacts'][1]['contact_name'], //常用联系人姓名
            'mobile' => $data['extro_contacts'][1]['contact_phone'], //常用联系人电话
            //======
            'relation_family' => $lineal_relative[$data['extro_contacts'][0]['type']], //紧急联系人A关系
            'relatives_name' => $data['extro_contacts'][0]['contact_name'], //紧急联系人A姓名
            'phone' => $data['extro_contacts'][0]['contact_phone'],//紧急联系人A电话
        ];
        $favorite_stat = Favorite_contacts::find()->where(['user_id'=>$user_id])->one();
        if (empty($favorite_stat)){
            $state = $favorite_contacts_object->addFavoriteContacts($favorite_contacts);
        }else{
            $state = $favorite_stat->updateFavoriteContacts($favorite_contacts);
            //需要记历史记录，只有修改记录yi_contacts_flows
        }
        if (!$state){
            return 10024;
        }
        return $this->success_code;
    }

    /**
     * 手机通讯录
     * @param $data
     * @param $user_id
     * @return array
     */
    private function checkAddressList($data, $user_id)
    {
        Logger::errorLog(print_r(array($data['contacts']), true), 'address_info', 'rateofloan');
        if (!empty($data['contacts'])){
            $mobiles = [];
            foreach ($data['contacts'] as $v){
                if(empty($v) || empty($v['contact_name']) || !isset($v['contact_phone']) || !isset($v['contact_name']))continue;
                $mobiles[] = json_encode(['number' => $v['contact_phone'],'name' => $v['contact_name']]);
            }

            $ret = (new Address_list())->saveMobilesLoanCheck($user_id, $mobiles);
        }
        return $this->success_code;
    }

    /**
     * 设备信息
     * @param $data
     * @param $user_info
     * @param $user_id
     * @return int
     */
    private function checkThirdinformation($data, $user_info, $user_id)
    {
        $user_password = User_password::find()->where(['user_id'=>$user_id])->one();
        $info = [
            "uid" => $data['user_id'], //用户在对方平台的id',
            "user_id" => $user_id,
            "gender" => $data['ID_Sex_OCR'] == '女' ? 1 : 0, //性别
            "housestate" => $data['addr_detail'], // 居住情况
            "careertype" => $user_info->position, //职业类型
            //"yearlimit" => "", //经营年限
            //"professional"=> '', //专业
            "backfile" => $this->backFile, //身份证反面照
            "frontfile" => $user_password->iden_url, //身份证正面照
            "naturefile" => $user_password->pic_url, //生活照
            "address" => $data['ID_Address_OCR'],//身份证地址
            "issuedby" => $data['ID_Issue_Org_OCR'],//身份证发证处
            "validdate" => $data['ID_Due_time_OCR'],//身份证有效期
            //`resolution` varchar(20) DEFAULT NULL COMMENT '分辨率',
            "osversion" => empty($data['device_info_all']['android_ver'])? '' : $data['device_info_all']['android_ver'],//操作系统版本号
            "model"=> empty($data['contacts']['device_info']) ? '' : $data['contacts']['device_info'],//手机型号,
            "totalmemory"=> empty($data['device_info_all']['mem_size'])? 0 : $data['device_info_all']['mem_size'],//总内存
            "wifi" => empty($data['device_info_all']['tele_name'])? '' : $data['device_info_all']['tele_name'],// wifi名字,
            //"networktype" => $data['is_simulator'],// 网络环境
            "manufacturer" =>empty($data['device_info_all']['phone_brand'])? '' : $data['device_info_all']['phone_brand'],//制造商
            "imeiordeviceid" => empty($data['device_info_all']['seria_no'])? '' : $data['device_info_all']['seria_no'], //设备号
            "isios" => empty($data['device_info_all']['mac'])? '' : $data['device_info_all']['mac'], //是否是ios
            "gid" => empty($data['device_info_all']['udid'])? (empty($data['device_info_all']['udid'])?'':$data['device_info_all']['udid']) : $data['device_info_all']['uuid'], //全局唯一标识
            "ip" => empty($data['ip_address']) ? "" : $data['ip_address'],//ip
            //`country` varchar(20) DEFAULT NULL COMMENT '国家',
            "latitude" => $data['contacts']['app_location']['lat'],//纬度
            "longitude" => $data['contacts']['app_location']['lon'],//经度
            "site" =>$data['contacts']['app_location']['address'], // 住址
            "come_from" => 7,
        ];
        $state = (new Thirdinformation())->addThirdinformation($user_info->user_id, $info);
        if (!$state){
            return 20025;
        }
        return $this->success_code;
    }

    /**
     * gps更新
     * @param $data
     * @param $user_id
     * @return array
     */
    private function checkAddress($data, $user_id)
    {
        $gps = $data['contacts']['app_location'];
        $come_from = ['android'=>1, 'IOS'=>2, 'Wap'=>3];
        $address_gps = empty($gps['address'])? "北京-默认" : $gps['address'];
        $address = new Address();
        $come_from = empty($come_from[$data['contacts']['platform']]) ? 3: $come_from[$data['contacts']['platform']];
        $address_info = Address::find()->where(['user_id'=>$user_id])->one();
        if (empty($address_info)){
            $state = $address->addAddress($user_id, $gps['lat'],  $gps['lon'], $address_gps, $come_from);
        }else{
            $state = $address_info->addAddress($user_id, $gps['lat'],  $gps['lon'], $address_gps, $come_from);
        }
        if (!$state){
            return 10026;
        }
        return $this->success_code;
    }

    /**
     * 更新yi_user_history_info
     * @param $data
     * @param $userInfo
     * @param $user_id
     * @return int
     */
    private function checkHistoryInfo($data, $userInfo, $user_id)
    {
        //user_history_info信息比对
        $newHistoryCondition = $this->getHistoryCondition($userInfo, $data, $user_id);
        $userHistory = new User_history_info();
        $history_info = $userHistory->newestHistory($user_id);
        if (!empty($history_info)) {
            $oldHistoryCondition = [
                'user_id' => $user_id,
                'company_school' => $history_info->company_school, //公司名称
                //'income' => $history_info->income, //月收入
                //'email' => $history_info->email, //邮箱
                //'industry_edu' => $history_info->industry_edu, //学历
                'telephone' => $history_info->telephone, //公司电话
                //'marriage' => $history_info->marriage, //婚姻
                'area' => $history_info->area,//公司所在区域
                'address' => $history_info->address, //公司地址
                //'profession' => $history_info->profession, //职业类型
                'user_type' => $history_info->user_type,
                'data_type' => $history_info->data_type,
            ];
            $dif_arr = array_diff($newHistoryCondition, $oldHistoryCondition);
            if (!empty($dif_arr)) {
                $history_ret = $this->addHistory($user_id, $userHistory, $dif_arr, $newHistoryCondition);
                if ($history_ret !== true) {
                    return 10027;
                }
            }
        } else {
            $upUserHistory = $userHistory->addHistoryInfo($user_id, $newHistoryCondition);
            if (!$upUserHistory) {
                return 10027;
            }
        }
        return $this->success_code;
    }

    /**
     * history表根据不同修改添加不同记录
     * @param $user_id
     * @param $userHistory
     * @param $dif_arr
     * @param $newHistoryCondition
     * @return array|bool
     */
    private function addHistory($user_id, $userHistory, $dif_arr, $newHistoryCondition)
    {
        $data_arr = $newHistoryCondition;
        $work_arr = ['company_school', 'industry_edu', 'position_schooltime', 'telephone', 'area', 'address', 'profession'];
        $user_arr = ['marriage', 'email', 'income'];
        $flag_work = 1;
        $flag_work_arr = [];
        $flag_user = 1;
        $flag_user_arr = [];
        foreach ($dif_arr as $k => $v) {
            if (in_array($k, $work_arr)) {
                $flag_work = 2;
                $flag_work_arr[$k] = $v;
            }
            if (in_array($k, $user_arr)) {
                $flag_user = 2;
                $flag_user_arr[$k] = $v;
            }
        }
        if ($flag_work == 2 && $flag_user == 1) {
            $newHistoryCondition['data_type'] = 2;
            $upUserHistory = $userHistory->addHistoryInfo($user_id, $newHistoryCondition);
            if (!$upUserHistory) {
                return 10027;
            }
        } elseif ($flag_work == 1 && $flag_user == 2) {
            $newHistoryCondition['data_type'] = 3;
            $upUserHistory = $userHistory->addHistoryInfo($user_id, $newHistoryCondition);
            if (!$upUserHistory) {
                return 10027;
            }
        } elseif ($flag_work == 2 && $flag_user == 2) {
            $flag_work_arr['data_type'] = 2;
            $work_new_arr = array_replace($data_arr, $flag_work_arr);
            $upWorkHistory = $userHistory->addHistoryInfo($user_id, $work_new_arr);
            $flag_user_arr['data_type'] = 3;
            $work_user_arr = array_replace($newHistoryCondition, $flag_user_arr);
            $upUserHistory = $userHistory->addHistoryInfo($user_id, $work_user_arr);
            if (!$upWorkHistory || !$upUserHistory) {
                return 10027;
            }
        }
        return TRUE;
    }

    /**
     * 组装生成history信息的数组
     * @param $user_info
     * @param $data
     * @param $user_id
     * @return array
     */
    private function getHistoryCondition($user_info, $data, $user_id)
    {
        //验证婚姻
        /*$marriage_data = [
            '1' => 1, //未婚
            '2' => 2, //已婚，无子女
            '3' => 2, //已婚，有子女
            '4' => 4, //离异
            '5' => 3, //丧偶
            '6' => 5, //复婚
            '7' => 6, //其他
        ];
        $user_marriage = 1;
        if (in_array($data['user_marriage'], $marriage_data)){
            $user_marriage = $marriage_data[$data['user_marriage']];
        }*/
        $contacts_condition = [
            'user_id' => $user_id,
            'company_school' => $data['customer_info']['company_name'], //公司名称
            //'income' => $user_info->extend->income, //月收入
            //'email' => $data['user_email'], //邮箱
            //'industry_edu' => $user_info->extend->edu, //学历
            'telephone' => $data['customer_info']['company_phone'], //公司电话
            //'marriage' => $user_marriage, //婚姻
            //'area' => $data['customer_info']['company_address_city'],//公司所在区域
            'address' => $data['customer_info']['company_address'], //公司地址
            //'profession' => isset($data['basicInfo']['profession']) ? $data['basicInfo']['profession'] : "", //职业类型
            'user_type' => 2,
            'data_type' => 3
        ];
        return $contacts_condition;
    }

    /**
     * 保存银行卡信息
     * @param $data
     * @param $user_id
     * @return int
     */
    private function saveBankInfo($data, $user_id)
    {
        $bankInfo = (new User_bank())->find()->where(['card' => $data['bank_card_info']['card_num']])->one();
        $error_arr = [];
        if ($bankInfo) {
            if ($bankInfo->user_id != $user_id) {//银行卡已经被其他用户绑定
                return 10009;
            }
            if ($bankInfo->status == 0 && $bankInfo->user_id == $user_id) {//银行卡是此用户并且解绑
                $up_res = $bankInfo->updateUserBank(['status' => 1]);
                return $this->success_code;
            }
            if ($bankInfo->status == 1 && $bankInfo->user_id == $user_id) {//银行卡是此用户并且绑定
                return $this->success_code;
            }
        }
        $cardbin = (new Card_bin())->getCardBinByCard($data['bank_card_info']['card_num'], "prefix_length desc");
        $condition['user_id'] = $user_id;
        $condition['type'] = $cardbin['card_type'];
        $condition['bank_abbr'] = $cardbin['bank_abbr'];
        $condition['bank_name'] = $cardbin['bank_name'];
        $condition['card'] = $data['bank_card_info']['card_num'];
        $condition['bank_mobile'] = $data['bank_card_info']['reserved_mobile'];
        //$verify = $this->bankFourElements($data);
        $verify['res_code'] = $this->success_code;
        if ($verify['res_code'] == $this->success_code) {
            $condition['verify'] = 1;
        } else {
            return $verify;
        }
        $UserBankModel = new User_bank();
        $ret_userbank = $UserBankModel->addUserbank($condition);
        if (!$ret_userbank) {
            return 10006;
        }
        //默认卡
//        $upDefBank = $UserBankModel->updateDefaultBank($user_id, $UserBankModel->id);
//        if (empty($upDefBank)){
//            return 10008;
//        }
        return $this->success_code;
    }

    /**
     * 银行卡四要素认证
     * @param $data
     * @return int
     */
    private function bankFourElements($data)
    {
        //绑卡之前先做银行卡四要素验证
        //调用银行卡验证接口
        $postinfo = array(
            'username' => $data['bank_card_info']['card_user_name'],
            'idcard'   => $data['carre_report']['user_info']['identity'],
            'cardno'   => $data['bank_card_info']['card_num'],
            'phone'    => $data['bank_card_info']['reserved_mobile']
        );
        $openApi = new Apihttp;
        Logger::errorLog(print_r(array($postinfo), true), 'bank_postinfo', 'rateofloan');
        $result = $openApi->bankInfoValid($postinfo);
        if ($result['res_code'] != '0000') {
            switch ($result['res_msg']) {
                case 'DIFFERENT':
                    $result['res_msg'] = '请优先确认您输入的手机号码与办理银行卡时预留手机号码一致<br>请确认您的银行卡号是否填写正确';
                    break;
                case 'ACCOUNTNO_INVALID':
                    $result['res_msg'] = '请核实您的银行卡状态是否有效';
                    break;
                case 'ACCOUNTNO_NOT_SUPPORT':
                    $result['res_msg'] = '暂不支持此银行，请更换您的银行卡';
                    break;
                default:
                    $result['res_msg'] = $result['res_msg'];
            }
            $array['rsp_code'] = '10007';
            $array['rsp_msg'] = $result['res_msg'];
            Logger::errorLog(print_r($array, true), 'bank_result_arr', 'rateofloan');
            return 10007;
        }
        return $this->success_code;
    }
    
    /**
     * 注册
     * @param $user_id
     * @return int
     */
    private function doReg($user_id)
    {
        $user_info = User::find()->where(['user_id'=>$user_id])->one();

        $user_password = User_password::find()->where(['user_id'=>$user_id])->one();
/*
        if ($user_password->score < $this->score || $user_info->status == 5) {
            return 10038;
        }*/

        $user_upstatus_result = $user_info->updateUser($user_id, ['status' => 3]);
        if (!$user_upstatus_result) {
            return 10039;
        }
        //判断黑名单
        $status = 3;

        $black_list = (new Black_list())->getInBlack($user_info['identity']);
        if ($black_list) {
            $res = (new XsApi())->setBlack($user_info->mobile, $user_info->identity);
            $status = 5;
        } else {
            $status = 1;
        }

        //查询是否已存在用户信息
        $user_objecgt = new User();
        $user_update = [
            'status' => $status,
            'last_login_time' => date('Y-m-d H:i:s'),
            'birth_year' => intval(substr($user_info->identity, 6, 4)),
            'identity_valid' => 2,
            'pic_identity'=>$user_password->iden_url,
        ];
        $ret_user_state = $user_objecgt->updateUser($user_id, $user_update);
        if (!$ret_user_state){
            return 10037;
        }
        //第一次完善工作信息走注册决策引擎
        $history_count = User_history_info::find()->where(['user_id' => $user_id, 'data_type' => 2])->count();
        if ($history_count == 0) {
            $regrule = $user_info->getRegrule($user_info, 1);
            if ($regrule == 1) {
                $user_info->setBlack();
                return 10028;
            }
        }
        return $this->success_code;
    }

    /**
     * 修改速达贷关联表用户状态
     * @param $user_info
     * @return int
     */
    private function checkSu($user_info){
        $update_info = ShuCheckUser::find()->where(['identity'=>$user_info['identity']])->one();
        $update_info->status = 1;
        $res = $update_info->save();
        if(!$res){
            return 10040;
        }
        return $this->success_code;
    }
    /**
     * 状态码
     * @param $code
     * @return mixed|string
     */
    public function returnCode($code)
    {
        $code_data =  [
            '10006' => '银行卡添加失败',
            "10007" => "验证失败！",
            "10008" => "绑卡失败",
            "10009" => "银行卡已经被其他用户绑定",
            "10013" => "单位电话不合法",
            "10015" => "用户信息存在",
            "10016" => "存量用户失败",
            "10019" => "请输入正确的手机号！",
            "10021" => "身份证不正确",
            "10022" => "用户信息更新失败",
            "10023" => "用户评分更新失败",
            "10024" => "常用联系人更新失败",
            "10026" => "地址更新失败",
            "10027" => "添加附属信息失败",
            "10028" => "注册决策",
            "10030" => "联系人关系编码不合法",
            "10032" => "电子邮箱不合法",
            "10033" => "常住省市县编码不合法",
            "10034" => "公司省市县编码不合法",
            "10035" => "婚姻编码不合法",
            "10036" => "身份证件图片保存失败",
            "10037" => "用户信息更新失败",
            "10038" => "用户暂不符合借款要求",
            "10039" => "注册失败",
            "10040" => "修改中间表用户状态失败",

        ];
        return empty($code_data[$code]) ? "网系出错！" : $code_data[$code];
    }
    /**
     * 验证手机号是否正确
     * @param number $mobile
     * @return bool true | false
     */
    public function isMobile($mobile) {
        if (!is_numeric($mobile)) {
            return false;
        }
        return preg_match('#^((1(([3578][0-9])|(47)))\d{8})|((0\d{2,3})\-?\d{7,8}(\-?\d{4})?)$#', $mobile) ? true : false;
    }

}