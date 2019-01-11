<?php
/**
 *  杰卡德关系
 */
namespace app\commands;

use app\models\anti\AfJcardMatch;
use app\models\anti\AfBase;
use app\models\anti\AfJacBase;
use app\models\repo\RepoApi;
use app\common\Logger;

use Yii;
use yii\db\Exception;
use yii\helpers\ArrayHelper;


// */1 * * * * /usr/local/bin/php /data/wwwroot/cloud/yii run-jac runJac >/dev/null 2>&1
// D:\phpstudy\php70n\php.exe  D:\phpstudy\WWW\cloud_ssdb\yii run-jac runJac
class RunJacController extends BaseController
{
    // variable 
    private static $user_phone;
    private static $addr_phones;
    private static $addr_phone_tel;
    private static $relation_list;
    private static $jac_data;
    private static $not_num_array; //非号码集合
    //db
    private static $db_yiyiyuan;
    private static $db_antifraud;
    private static $db_analysis_repertory;
    private static $maxNum;

    public function init()
    {
        // variable 
        self::$maxNum = 5000;
        self::$user_phone = '';
        self::$addr_phones = [];
        self::$jac_data = [];
        self::$addr_phone_tel = [];
        self::$relation_list = [];
        //db
        self::$db_yiyiyuan = Yii::$app->db_yiyiyuan;
        self::$db_antifraud = Yii::$app->db_anti;
        self::$db_analysis_repertory = Yii::$app->db_analysis_repertory;

        // num array
        self::$not_num_array = ['400',];
    }

    /**
     * @desc  
     * @param $startId
     * @param $endId
     */
    public function runJac() 
    {
        $repoApi = new RepoApi();
        //获取数据
        $jcardData = $repoApi->getJcardMatch();
        if (empty($jcardData)) {
            Logger::dayLog('runJac','there is nothing data to deal with');
            echo "there is nothing data to deal with. \n";
            return false;
        }

        // 计算间接关系
        foreach ($jcardData as $runData) {
            try{
                echo $runData['user_id']."is running.\n";
                //查询用户
                // $userInfo = $repoApi->getUser($runData);
                // if(empty($userInfo)){
                //     Logger::dayLog('runJac',"can not find the user:",$runData);
                //     continue;
                // }
                //分析关系
                $jacData = $this->analysisJac($runData);
                if(!$jacData){
                    continue;
                }

                // 合并数据
                $allData['jcard_match'] = $this->mergeData($runData);
                // 储存json文件
                $filePath = $this->saveJcardMatch($allData);
                if(!$filePath){
                    Logger::dayLog('runJac',"save jaccard relation fail:",$allData);
                    continue;
                }
                $runData['jcard_result'] = $filePath;
                // 保存到库中
                $save_id = $this->saveDbJac($runData);
                if(!$save_id){
                    Logger::dayLog('runJac',"save jcardMatch db fail:",$save_id);
                    continue;
                }
                # 更新为结束
                $oBase = new AfJacBase();
                $res = $oBase->finishJcard($runData['id'],$save_id);
            }catch (Exception $e){
                Logger::dayLog('runJac','indirect jaccard calculates fail，reason: '.$e->getMessage().':',$runData);
                return false;
            }
        }
    }
    //save jac in db
    private function saveDbJac($save_data)
    {
        $aid = ArrayHelper::getValue($save_data, 'aid', 0);
        $user_id = ArrayHelper::getValue($save_data, 'user_id', 0);
        $request_id = ArrayHelper::getValue($save_data, 'request_id', 0);

        if((!$aid)||(!$user_id)||(!$request_id)){
            Logger::dayLog('runJac','required parameter missing:',$save_data);
            return 0;
        }

        $oJcard = new AfJcardMatch();
        $res = $oJcard->saveJcard($save_data);
        if (!$res) {
            return 0;
        }
        return $res;
    }

    private function mergeData($marge_data)
    {
        $all_data= [
            'user_id' =>$marge_data['user_id'],
            'loan_id' =>$marge_data['loan_id'],
            'aid' => isset($marge_data['aid']) ? $marge_data['aid'] : '1',
            'jcard_relation' => self::$jac_data,
            'relation_list' => self::$relation_list,
        ];
        return $all_data;
    }

    private function analysisJac($jac_data)
    {
        $repoApi = new RepoApi();
        self::$user_phone = ArrayHelper::getValue($jac_data, 'mobile', '');
        if(empty(self::$user_phone)){
            Logger::dayLog('runJac',"the user's mobile is empty:",$jac_data);
            return false;
        }
        // 查出该用户通讯录
        self::$addr_phones = $repoApi->getAddressPhone(self::$user_phone);
        if(empty(self::$addr_phones)){
            Logger::dayLog('runJac',"the user has no address:".(self::$user_phone));
            return false;
        }

        // 目标用户分组
        self::$addr_phone_tel = $repoApi->getPhoneAndTel(self::$addr_phones);
//        Logger::dayLog('testJac2',self::$user_phone,(self::$addr_phone_tel));
        if(empty(self::$addr_phone_tel['tel']) && empty(self::$addr_phone_tel['phone'])){
            Logger::dayLog('runJac',"the address's numbers is illegal:",(self::$addr_phones));
            return false;
        }

        // 间接
        $indirect_res = $this->indirectJac();
        // 一级
        $first_res = $this->firstJac();
        // 二级
        $second_res = $this->secondJac();
        // 逆一级
        $reverse_res = $this->reverseJac();
        return true;
    }

    // 间接关系
    public function indirectJac()
    {
        self::$relation_list['indirect'] = [];
        self::$jac_data['indirect'] = [];
        $repoApi = new RepoApi();
        // 反查(如果反查数据量过大，则过滤该用户)
        $start = explode(' ',microtime());
        $addr_phones_count = $repoApi->getCountByPhones(self::$addr_phones);
        if($addr_phones_count > self::$maxNum){
            Logger::dayLog('runJac',"the user's reverse address is too large:".(self::$user_phone),$addr_phones_count);
            return false;
        }

        $addr_user_phones = $repoApi->getAddrUserPhones(self::$addr_phones);

        if(empty($addr_user_phones)){
            Logger::dayLog('runJac',"the reverse address is empty:",(self::$user_phone));
            return false;
        }

        //过滤非正常号码
//        foreach ($addr_user_phones as $key => $value){
//            if($repoApi->isIllegal($value)) {
//                unset($addr_user_phones[$key]);
//            }
//        }
        $end = explode(' ',microtime());
        $stime = $end[0]+$end[1]-($start[0]+$start[1]);
        $stime = round($stime,3);
        echo "addr_user_phones:".$stime." S." . "\n";

        // 获取反查用户的通讯录集合
        $start = explode(' ',microtime());

        $all_addr_phones = $repoApi->getAllAddressPhone($addr_user_phones);

        $end = explode(' ',microtime());
        $stime = $end[0]+$end[1]-($start[0]+$start[1]);
        $stime = round($stime,3);
        echo "all_addr_phones:".$stime." S." . "\n";

        //计算用户杰卡德关系
        if (empty($all_addr_phones)) {
            Logger::dayLog('runJac',"all address is empty:",($addr_user_phones));
            return false;
        }

        $time1 = explode(' ',microtime());
        foreach ($all_addr_phones as $key => $value) {
            if($key == self::$user_phone){
                continue;
            }
            $numbers = json_decode($value,true);
            //去除自身
            $keys = array_keys($numbers, $key);
            foreach($keys as $pk){
                unset($numbers[$pk]);
            }
            //分成手机号及非手机号
            $phone_and_tel = $repoApi->getPhoneAndTel($numbers);
            //判断是否为一级或逆一级关系
            if (in_array(self::$user_phone, $numbers) || in_array($key, self::$addr_phones)) {
                continue;
            }
            //计算用户杰卡德系数
            #save relation
            #calc Jaccard
            $jac_data = $this->calcJaccard($phone_and_tel,$key);
            if (!$jac_data) {
                continue;
            }
            self::$relation_list['indirect'][] = $key;
            self::$jac_data['indirect'][] = $jac_data;
        }
        self::$relation_list['indirect'] = array_unique(self::$relation_list['indirect']);
        self::$relation_list['indirect'] = array_values(self::$relation_list['indirect']);
        self::$jac_data['indirect'] = $repoApi->filterValue(self::$jac_data['indirect'],'phone');
        $time2 = explode(' ',microtime());
        $thistime1 = $time2[0]+$time2[1]-($time1[0]+$time1[1]);
        echo "calculate indirect jaccard relation:".$thistime1."S" . "\n";
        return true;
    }

    // 二级关系
    public function secondJac()
    {
        self::$relation_list['second'] = [];
        self::$jac_data['second'] = [];
        $repoApi = new RepoApi();
        if (empty(self::$addr_phones)){
            return false;
        }

        $all_addr_phones = $repoApi->getAllAddressPhone(self::$addr_phones);
        foreach ($all_addr_phones as $key => $value) {
            // 获取所有二级用户
            $numbers = json_decode($value,true);
            foreach ($numbers as $km =>$phone) {
                if($repoApi->isIllegal($phone)){
                    unset($numbers[$km]);
                }
            }
            $all_other_phones = $repoApi->getAllAddressPhone($numbers);
            foreach ($all_other_phones as $k => $v){
                if($k == self::$user_phone){
                    continue;
                }
                $v = json_decode($v,true);
//                过滤一级和逆一级
//                if(in_array($k, self::$addr_phones) || in_array(self::$user_phone, $v)){
//                    continue;
//                }
//                过滤一级
                if(in_array($k, self::$addr_phones)){
                    continue;
                }
                //去除自身
                $keys = array_keys($v, $k);
                foreach($keys as $pk){
                    unset($v[$pk]);
                }
                $phone_and_tel = $repoApi->getPhoneAndTel($v);
                #calc Jaccard
                $jac_data = $this->calcJaccard($phone_and_tel,$k);
                if (!$jac_data) {
                    continue;
                }
                self::$relation_list['second'][] = $k;
                self::$jac_data['second'][] = $jac_data;
            }
        }
        self::$relation_list['second'] = array_unique(self::$relation_list['second']);
        self::$relation_list['second'] = array_values(self::$relation_list['second']);
        self::$jac_data['second'] = $repoApi->filterValue(self::$jac_data['second'] ,'phone');
    }
    // 一级关系
    public function firstJac()
    {
        $repoApi = new RepoApi();
        $all_addr_phones = $repoApi->getAllAddressPhone(self::$addr_phones);

        self::$relation_list['first'] = [];
        self::$jac_data['first'] = [];
        foreach ($all_addr_phones as $key => $value) {
            if($key == self::$user_phone){
                continue;
            }
            $numbers = json_decode($value,true);
            //去除自身
            $keys = array_keys($numbers, $key);
            foreach($keys as $pk){
                unset($numbers[$pk]);
            }
            $phone_and_tel = $repoApi->getPhoneAndTel($numbers);
//            Logger::dayLog('testJac2',$key,($phone_and_tel));
            #计算jaccard系数
            $jac_data = $this->calcJaccard($phone_and_tel,$key);
            if (!$jac_data) {
                continue;
            }
            self::$relation_list['first'][] = $key;
            self::$jac_data['first'][] = $jac_data;
        }
        self::$relation_list['first'] = array_unique(self::$relation_list['first']);
        self::$relation_list['first'] = array_values(self::$relation_list['first']);
        self::$jac_data['first'] = $repoApi->filterValue(self::$jac_data['first'], 'phone');
    }
    // 逆一级
    public function reverseJac()
    {
        self::$relation_list['reverse'] = [];
        self::$jac_data['reverse'] = [];
        $repoApi = new RepoApi();
        // 反查
        $addr_other_phones = $repoApi->getAddrUserPhones([self::$user_phone]);

        //过滤非正常号码
//        foreach ($addr_other_phones as $key => $value){
//            if($repoApi->isIllegal($value) || in_array($value,self::$addr_phones)) {
//                unset($addr_other_phones[$key]);
//            }
//        }

        $all_addr_phones = $repoApi->getAllAddressPhone($addr_other_phones);
        foreach ($all_addr_phones as $key => $value) {
            if($key == self::$user_phone){
                continue;
            }
            //过滤一级
            if(in_array($key, self::$addr_phones)){
                continue;
            }
            $numbers = json_decode($value,true);
            //去除自身
            $keys = array_keys($numbers, $key);
            foreach($keys as $pk){
                unset($numbers[$pk]);
            }
            $phone_and_tel = $repoApi->getPhoneAndTel($numbers);
            #calc Jaccard
            $jac_data = $this->calcJaccard($phone_and_tel,$key);
            if (!$jac_data) {
                continue;
            }
            self::$relation_list['reverse'][] = $key;
            self::$jac_data['reverse'][] = $jac_data;
        }
        self::$relation_list['reverse'] = array_unique(self::$relation_list['reverse']);
        self::$relation_list['reverse'] = array_values(self::$relation_list['reverse']);
        self::$jac_data['reverse'] = $repoApi->filterValue(self::$jac_data['reverse'], 'phone');
    }

    private function saveJcardMatch($dictRes)
    {
        $repoApi = new RepoApi();
        $rootPath =  Yii::$app->basePath;
        $time = date('Ym/d');
        $sub_path = '/relation/jaccard/' . $time;
        $dir_path = $rootPath . '/..' . $sub_path;
        if (SYSTEM_PROD) {
            $dir_path = '/data/wwwroot' . $sub_path;
        }
        # 创建级联目录
        $dir_exists = $repoApi->makeDir($dir_path);

        if(!$dir_exists){
            Logger::dayLog('runJac','Failed to create directory');
            return '';
        }

        $jcardMatch = isset($dictRes['jcard_match'])?$dictRes['jcard_match']:[];
        $jcardResult = isset($jcardMatch['jcard_relation'])?$jcardMatch['jcard_relation']:[];
        $relationResult = isset($jcardMatch['relation_list'])?$jcardMatch['relation_list']:[];

        $user_id = isset($jcardMatch['user_id'])?$jcardMatch['user_id']:'';
        $loan_id = isset($jcardMatch['loan_id'])?$jcardMatch['loan_id']:'';
        $aid = isset($jcardMatch['aid'])?$jcardMatch['aid']:'';
        if(empty($relationResult)){
            Logger::dayLog('runJac',$user_id.' has not jcard-match relationship');
            return '';
        }
        $now = date('Y-m-d H:i:s');
        $jac_result = [
            'jaccard' => $jcardResult,
            'create_time' => $now,
            'modify_time' => $now
        ];
        $relation_list = [
            'relation' => $relationResult,
            'create_time' => $now,
            'modify_time' => $now
        ];
        $end = '_jaccard.json';
        # 将内容写入文件
        $jacardpath = '/' . $aid . '_' . $user_id . '_' . $loan_id;
        $retpath = '/' .$time . $jacardpath . $end;
        $rela_path = $dir_path . $jacardpath . str_replace('_jaccard','_jaccard_rela',$end);
        $jac_path = $dir_path . $jacardpath . str_replace('_jaccard','_jaccard_data',$end);

        $rela_json = json_encode($relation_list);
        $jac_json = json_encode($jac_result);
        file_put_contents($rela_path, $rela_json);
        file_put_contents($jac_path, $jac_json);
        return $retpath;
    }

    //计算jaccard关系
    private function calcJaccard($data,$phone)
    {
        $jac_phone = 0;
        $jac_tel = 0;
        $jac_all = 0;
        //手机号杰卡德系数
        $phone_instersect = count(array_intersect($data['phone'],self::$addr_phone_tel['phone'])); //交集
        $phone_union = count($this->getArrayUnion($data['phone'],self::$addr_phone_tel['phone'])); //并集
        if ($phone_union != 0) {
            $jac_phone = round($phone_instersect/$phone_union,4);
        }
        //非手机号杰卡德系数
        $tel_instersect = count(array_intersect($data['tel'],self::$addr_phone_tel['tel'])); //交集
        $tel_union = count($this->getArrayUnion($data['tel'],self::$addr_phone_tel['tel'])); //并集
        if ($tel_union != 0) {
            $jac_tel = round($tel_instersect/$tel_union,4);
        }
        //all_jac
        $all_addr = array_merge($data['tel'],$data['phone']);
        $user_phones = array_merge(self::$addr_phone_tel['tel'],self::$addr_phone_tel['phone']);
        $all_instersect = count(array_intersect($all_addr,$user_phones)); //交集
        $all_union = count($this->getArrayUnion($all_addr,$user_phones)); //并集

        if ($all_union != 0) {
            $jac_all = round($all_instersect/$all_union,4);
        }
        if ($jac_all + $jac_tel + $jac_phone == 0) {
            return [];
        }
        return ['user_phone'=>self::$user_phone,'phone'=>$phone,'jac_phone'=> $jac_phone,'jac_tel'=>$jac_tel,'jac_all'=>$jac_all];
    }

    private function getArrayUnion($array_a,$array_b)
    {
        $array_union = array_merge($array_a,$array_b);
        $array_union = array_unique($array_union);
        return $array_union;
    }
}