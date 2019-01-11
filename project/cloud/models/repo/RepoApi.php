<?php

namespace app\models\repo;

use app\models\anti\AfBase;
use app\models\anti\AfJacBase;
use app\models\anti\AfJcardMatch;
use app\models\yyy\YiUser;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;

class RepoApi
{
    private static $not_num_array; //非号码集合
    //垃圾号码
   // public static $illegal_num = ["18757141666","4008640166","02161136612","15277074012","13556354277","13246783148","13246783148","18489214062","13670497877","13670497877","037964159160","037964159160","4006272273","031186777590","031186777590","4000368876","18757143666","028962516","18757142666","18757144666","4008650682","4008624066","02138690588","02138690588","13502602872","013246783095","13246783095","01060778000","13502519977","15089636750","15089645512","04000113264","13502684219","18819439213","15277039560","4006259898","0537325420","4008653918","4008649191","13670369922","13682884567","15362314658","18489201319","02138695533","02138695533","4008621682","15089448205","02161195599","4008646611","18320033335","4008623016","13217551173","15018217275","15089368953","15218671442","18665506855","02138695888","02138695888","08313960354","18344584609","13184849963","13246783259","13502560899","13682806046","13423000932","13502510592","4000328876","15649173287","13246783260","4001508888","13028823453","18824565957","02138784988","02138784988","18344104211","18302049831","13927034995","13798352525","15018102211","18665509275","15018223323","15277074494","18320552000","13670561450","13682735830","13246689958","13229180663","15089444741","13682822482","13411073550","13903056782","18665505144","13903083423","15218630295","13246783156","4000353999","01060778888","15089449844","18489214073","13682716253","15392012347","13229186399","13502883203","18344139010","13585866135","15813737415","14778207322","18824481203","13927084249","14778207596","02161136683","95217194","01060641668"];
    public static $illegal_num = [];

	public function __construct()
	{
        // num array
        self::$not_num_array = ['400',];
        $garb_num_jsdon = Yii::$app->ssdb_detail->get('garb_num');
        if (empty($garb_num_jsdon)) {
            Logger::dayLog('runJac','garb_num is empty.');
            die;
        }
        self::$illegal_num = json_decode($garb_num_jsdon,true);
	}

	//获取jaccard数据
    public function getJcardMatch()
    {
        $time = date('Y-m-d H:i:s', strtotime("-7 day"));
        // $oBase = new AfBase();
        $oBase = new AfJacBase();
        $where = [
            'jac_status' => 0,
            'create_time' => $time,
        ];
        $field = 'id, aid, user_id, loan_id, mobile,request_id';
        $data = $oBase->getJaccardData($where, $field);
        if(empty($data)){
            Logger::dayLog('runJac','no jcard-match data.');
            return [];
        }
        $baseIds = ArrayHelper::getColumn($data, 'id');
        # 锁定状态
        $res = $oBase->lockJcards($baseIds);
        if(!$res){
            Logger::dayLog('runJac','jcard-match data update fail.');
            return [];
        }
        echo "there's ".count($baseIds)." data to deal with. \n";
        Logger::dayLog('runJac',"there's ".count($baseIds)." data to deal with");
        return $data;
    }
	
	//查找某人的通讯录
    public function getAddressPhone($user_phone)
    {
        $phoneStrs = Yii::$app->ssdb_address->get($user_phone);
        $phoneArr = [];
        # 去除短号、自身以及垃圾号码
        if(!empty($phoneStrs)){
            $phoneArr = json_decode($phoneStrs, true);
            foreach ($phoneArr as $key =>$phone) {
                if($this->isIllegal($phone) || $phone == $user_phone  || in_array($phone, self::$illegal_num)){
                    unset($phoneArr[$key]);
                }
            }
        }
        return $phoneArr;
    }

    //反查某人的通讯录数量
    public function getCountByPhones($phones)
    {
        if (empty($phones)) {
            return 0;
        }
        $oRevAddress = new ReverseAddressList();
        $count = $oRevAddress->getCountByPhones($phones);
        return $count;
    }

    //反查某人的通讯录
    public function getAddrUserPhones($phones)
    {
        if (empty($phones)) {
            return [];
        }
        $oRevAddress = new ReverseAddressList();
        $addressList = $oRevAddress->getByPhones($phones, ['user_phone']);

        $user_phones = ArrayHelper::getColumn($addressList, 'user_phone', '');
        return $user_phones;
    }

    //批量获取通讯录
    public function getAllAddressPhone($addr_user_phones)
    {
        if (empty($addr_user_phones)) {
            return [];
        }
        $ssdb = Yii::$app->ssdb_address;
        $all_addr_phones = $ssdb->multi_get($addr_user_phones);
        return $all_addr_phones;
    }

    //获取用户信息
    public function getUser($data)
    {
        if (empty($data)) {
            Logger::dayLog('runJac',"data is empty!:",$data);
            return [];
        }
        $user_id = ArrayHelper::getValue($data, 'user_id', '');
        if (empty($user_id)) {
            Logger::dayLog('runJac',"user_id does not exist:",$data);
            return [];
        }
        $oUser = new YiUser();
        $userInfo = $oUser->getByUserId($user_id, ['user_id','mobile']);

        return $userInfo;
    }

    //分成手机号及非手机号
    public function getPhoneAndTel($data)
    {
        $tel = [];
        $phone = [];
        foreach ($data as $k => $val) {
            // 去除短号和垃圾号码
            if($this->isIllegal($val) || in_array($val, self::$illegal_num)){
                unset($data[$k]);
                continue;
            }
            //分为手机号和固话
            $isTel = preg_match('/^0\d{2,3}-?\d{7,8}$/', $val, $matche_phone);//'^0\d{2,3}\d{7,8}$|^\d{7,8}$|^400'
            if ($isTel > 0) {
                $tel[] = $val;
            } else {
                if(strlen($val)==11 && $val>='13000000000' && $val<='19000000000'){
                    $phone[] = $val;
                }
            }
        }
        $tel_phone = ['tel' => array_unique($tel), 'phone' => array_unique($phone)];
        return $tel_phone;
    }

    //过滤非正常号码
    public function isIllegal($phone){
        if (strlen($phone)<=5) {
            return true;
        }
        if ( in_array(substr($phone, 0, 3),self::$not_num_array)  || substr($phone, 0, 4) == '0400') {
            return true;
        }
        return false;
    }

    //创建级联目录
    public function makeDir($dir){
        if (is_dir($dir)) {
            return true;
        }
        if (is_dir(dirname($dir))) {
            return mkdir($dir);
        } else {
            $this->makeDir(dirname($dir));
            return mkdir($dir);
        }
    }
    public function filterValue($arr, $field){
        $n_arr = array_column($arr, null, $field);
        return array_values($n_arr);
    }
    /**
     * csv_get_lines 读取CSV文件中的某几行数据
     * @param $csvfile csv文件路径
     * @param $lines 读取行数
     * @param $offset 起始行数
     * @return array
     * */
    public function csv_get_lines($csvfile, $lines, $offset = 0) {
        if(!$fp = fopen($csvfile, 'r')) {
            return false;
        }
        $i = $j = 0;
        while (false !== ($line = fgets($fp))) {
            if($i++ < $offset) {
                continue;
            }
            break;
        }
        $data = array();
        while(($j++ < $lines) && !feof($fp)) {
            $data[] = fgetcsv($fp);
        }
        fclose($fp);
        return $data;
    }

}
