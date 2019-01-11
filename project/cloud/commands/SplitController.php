<?php
/**
 * 分位值统计   每天零点执行一次
 */
namespace app\commands;

use Yii;
use yii\web\Controller;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\yyy\YiUserCreditList;
use app\models\xs\XsFmRuleDetail;
use app\models\xs\XsSplitValue;
/**
 *  /usr/local/bin/php /data/wwwroot/cloud/yii  split runsplit
 *  D:\phpstudy\php55\php.exe  D:\phpstudy\WWW\cloud_sync\yii split runsplit
 */

ini_set('memory_limit','-1');
class SplitController extends BaseController
{
	private $first_type = 1;
	private $reloan_type = 2;
	private static $first_all = [];
	private static $reloan_all = [];
	private $chunk = 2000;
	private $num = 20;
	private static $db_cloud_new;
	private $limit = 2000;
	public function init(){
		self::$db_cloud_new = Yii::$app->db_cloudnew;
	}
	
    public function runSplit(){
    	$starttime = explode(' ',microtime());
    	# get 
    	$start_time = date('Y-m-d', strtotime('-7 days'));
    	$end_time = date('Y-m-d');
    	// if (SYSTEM_PROD) {
	    	$where = [
	    		'and',
	    		['>=', 'create_time', $start_time],
	    		['<', 'create_time', $end_time],
	    	];
    	// } else {
    	// 	$where = [
	    // 		'and',
	    // 		['>=', 'id', '178222'],
	    // 		['<=', 'id', '188888'],
	    // 	];
	    // 	$where = null;
    	// }
    	$select = 'user_id,res_info';
    	
    	$oYiUserCreditList = new YiUserCreditList();
    	# get count
    	$all_count = $oYiUserCreditList->getCount($where);
    	if ($all_count == 0) {
    		Logger::dayLog('split','no data to deal with');
    		die("no data to deal with");
    	}
    	# page query
    	$credit_list = [];
    	$first_loan = []; #初贷
    	$reloan = []; #复贷
    	$offset = 0;
    	while ($offset <= $all_count) {
    		$credit_list = $oYiUserCreditList->getAllbyPage($where,$select,$this->limit,$offset);
    		if (empty($credit_list)) {
    			continue;
    		}
    		foreach ($credit_list as $credit) {
    			$user_id = ArrayHelper::getValue($credit,'user_id','');
    			$res_info = ArrayHelper::getValue($credit,'res_info',null);
    			if (empty($res_info)) {
    				continue;
    			}
    			$res_array = json_decode($res_info,true);
    			if (empty($res_array)) {
    				continue;
    			}
    			$credit_subject = ArrayHelper::getValue($res_array,'credit_subject',null);
    			if (empty($credit_subject)) {
    				continue;
    			}
    			$credit_subject_array = json_decode($credit_subject,true);
    			#get type  1 first_loan；2 reloan
    			$success_num = ArrayHelper::getValue($credit_subject_array,'success_num',null);
    			if ($success_num == 0) {
    				$first_loan[] = $user_id;
    			} else {
                    # die new reloan
                    $is_testcd = ArrayHelper::getValue($credit_subject_array,'is_testcd',0);
                    $testcd_point = ArrayHelper::getValue($credit_subject_array,'testcd_point',0);
                    if ($is_testcd == 1 || $testcd_point == 1) {
                        continue;
                    }
    				$reloan[] = $user_id;
    			}
    		}
    		$offset += $this->limit;
    	}
    	// set self::$first_all
    	$first_user_ids = array_keys(array_flip($first_loan));
    	$this->setAllDetail($first_user_ids,$this->first_type);
    	// set self::$reloan_all
    	$reloan_user_ids = array_keys(array_flip($reloan));
    	$this->setAllDetail($reloan_user_ids,$this->reloan_type);
    	// Analysis first_all
    	$first_res = $this->AnalysisSplit(self::$first_all);
    	// Analysis reloan_all
    	$reloan_res = $this->AnalysisSplit(self::$reloan_all);
    	// save Analysis result
    	$seve_res = $this->saveAnalysisResult($first_res,$reloan_res);
    	$endtime1 = explode(' ',microtime());
        $thistime1 = $endtime1[0]+$endtime1[1]-($starttime[0]+$starttime[1]);
        $thistime1 = round($thistime1,3);
        echo "use_time：".$thistime1." S\n";
        Logger::dayLog('split/time','use_time is '.$thistime1,$all_count,$where);
    	return true;
    }

    private function saveAnalysisResult($first_res,$reloan_res){
    	$saveData = [
    		'first_split' => json_encode($first_res),
    		'reloan_split' => json_encode($reloan_res),
    	];
    	$oXsSplitValue = new XsSplitValue();
    	$result = $oXsSplitValue->saveData($saveData);
    	return $result;
    }
    /**
     * [AnalysisSplit description]
     * @param [type] &$analysis_data [description]
     */
    private function AnalysisSplit(&$analysis_data){
    	if (empty($analysis_data)) {
    		return false;
    	}
    	$split_value = [];
    	foreach ($analysis_data as $name => $data) {
    		if (empty($data) || !is_array($data)) {
    			continue;
    		}
    		// 分组
    		$split_value[$name] = $this->chunkAndSplit($data,$this->num);
    	}
    	return $split_value;
    }
    /**
     * [chunk 分割成num个二维数组并取出分位值]
     * @param  [type] $list [description]
     * @param  [type] $num  [description]
     * @return [type]       [description]
     */
    private function chunkAndSplit($list, $num)
    {
        $temp = [];
        if (!is_array($list)) {
            return [];
        }
        // 排序
        sort($list);
        // 判断数量是否小于列数   小于 直接返回第一列
        if (count($list) < $num) {
            return $temp[] = $list;
        }
        // 向上取整
        $argv = ceil(count($list) / $num);
        // 循环切片
        for ($i = 1; $i <= $num; $i++) {
        	$son_array = array_slice($list, $argv * ($i - 1), $argv);
        	if (!$son_array) {
        		continue;
        	}
        	$key = $i*(100/$num);
            $temp[$key] = end($son_array);
        }
        # check lastkey是否=100 
        $last_value = end($temp);
        $last_key = key($temp);
        if ($last_key < 100) {
            $temp[100] = $last_value;
        }
        krsort($temp);
        $temp = array_unique($temp);
        ksort($temp);
        return $temp;
    }
    /**
     * [setAllDetail description]
     * @param [type] $user_ids [description]
     * @param [type] $type     [description]
     */
    private function setAllDetail($user_ids,$type){
    	$user_ids_list = array_chunk($user_ids,$this->chunk);
    	foreach ($user_ids_list as $u_ids) {
    		$fm_ids = $this->getFmId($u_ids);
    		if (empty($fm_ids)) {
    			continue;
    		}
    		$multi_detail_res = $this->setMultiDetail($fm_ids,$type);
    	}
    	return true;
    }
    /**
     * [getFmId description]
     * @param  [type] $user_ids [description]
     * @return [type]           [description]
     */
    private function getFmId($user_ids){
    	$ids = "'".implode($user_ids,"','")."'";
    	$sql = "SELECT identity_id,max(id) as max_id FROM `dc_fraudmetrix` WHERE `identity_id` IN(".$ids.") AND `event`= 'loan_web' GROUP BY `identity_id`";
    	$command = self::$db_cloud_new->createCommand($sql);
        $dataList = $command->queryAll();
        if (empty($dataList)) {
        	return [];
        }
        $fm_ids = array_column($dataList,'max_id');
        return $fm_ids;
    }
    /**
     * [setMultiDetail description]
     * @param [type] $fids [description]
     * @param [type] $type [description]
     */
    private function setMultiDetail($fids,$type){
    	$where = ['in','fid',$fids];
    	$select = 'mid_fm_seven_d_detail,mid_fm_one_m_detail';
    	$oXsFmRuleDetail = new XsFmRuleDetail();
    	$multi_detail_list = $oXsFmRuleDetail->getFmRuleDetail($where,$select);
    	if (empty($multi_detail_list)) {
    		Logger::dayLog('splitvalue','no FmRuleDetail to deal with',$fids);
    		return false;
    	}
    	foreach ($multi_detail_list as $multi_detail) {
    		$seven_detail_json = ArrayHelper::getValue($multi_detail,'mid_fm_seven_d_detail','');
            $seven_detail_arr = json_decode($seven_detail_json,true);
            $seven_detail_arr = isset($seven_detail_arr[0]) ? $seven_detail_arr[0] : $seven_detail_arr;
            # get seven every detail
            $seven_id_all = ArrayHelper::getValue($seven_detail_arr,'借款人身份证个数',0);
            $seven_id_p2p = ArrayHelper::getValue($seven_detail_arr,'借款人身份证详情.0.P2P网贷',0);
            $seven_id_small = ArrayHelper::getValue($seven_detail_arr,'借款人身份证详情.0.小额贷款公司',0);
            $seven_id_big = ArrayHelper::getValue($seven_detail_arr,'借款人身份证详情.0.大型消费金融公司',0);
            $seven_id_common = ArrayHelper::getValue($seven_detail_arr,'借款人身份证详情.0.一般消费分期平台',0);
            $seven_ph_all = ArrayHelper::getValue($seven_detail_arr,'借款人手机个数',0);
            $seven_ph_p2p = ArrayHelper::getValue($seven_detail_arr,'借款人手机详情.0.P2P网贷',0);
            $seven_ph_small = ArrayHelper::getValue($seven_detail_arr,'借款人手机详情.0.小额贷款公司',0);
            $seven_ph_big = ArrayHelper::getValue($seven_detail_arr,'借款人手机详情.0.大型消费金融公司',0);
            $seven_ph_common = ArrayHelper::getValue($seven_detail_arr,'借款人手机详情.0.一般消费分期平台',0);
            
            $this->setData($seven_id_all,$seven_ph_all,'seven_all',$type);
            $this->setData($seven_id_p2p,$seven_ph_p2p,'seven_p2p',$type);
            $this->setData($seven_id_small,$seven_ph_small,'seven_small',$type);
            $this->setData($seven_id_big,$seven_ph_big,'seven_big',$type);
            $this->setData($seven_id_common,$seven_ph_common,'seven_common',$type);
            # 一个月
            $one_mouth_detail_json = ArrayHelper::getValue($multi_detail,'mid_fm_one_m_detail','');
            $one_mouth_detail_arr = json_decode($one_mouth_detail_json,true);
            $one_mouth_detail_arr = isset($one_mouth_detail_arr[0]) ? $one_mouth_detail_arr[0] : $one_mouth_detail_arr;
            # get one -mouth every detail
            $one_mouth_id_all = ArrayHelper::getValue($one_mouth_detail_arr,'借款人身份证个数',0);
            $one_mouth_id_p2p = ArrayHelper::getValue($one_mouth_detail_arr,'借款人身份证详情.0.P2P网贷',0);
            $one_mouth_id_small = ArrayHelper::getValue($one_mouth_detail_arr,'借款人身份证详情.0.小额贷款公司',0);
            $one_mouth_id_big = ArrayHelper::getValue($one_mouth_detail_arr,'借款人身份证详情.0.大型消费金融公司',0);
            $one_mouth_id_common = ArrayHelper::getValue($one_mouth_detail_arr,'借款人身份证详情.0.一般消费分期平台',0);
            $one_mouth_ph_all = ArrayHelper::getValue($one_mouth_detail_arr,'借款人手机个数',0);
            $one_mouth_ph_p2p = ArrayHelper::getValue($one_mouth_detail_arr,'借款人手机详情.0.P2P网贷',0);
            $one_mouth_ph_small = ArrayHelper::getValue($one_mouth_detail_arr,'借款人手机详情.0.小额贷款公司',0);
            $one_mouth_ph_big = ArrayHelper::getValue($one_mouth_detail_arr,'借款人手机详情.0.大型消费金融公司',0);
            $one_mouth_ph_common = ArrayHelper::getValue($one_mouth_detail_arr,'借款人手机详情.0.一般消费分期平台',0);
            $this->setData($one_mouth_id_all,$one_mouth_ph_all,'one_mouth_all',$type);
            $this->setData($one_mouth_id_p2p,$one_mouth_ph_p2p,'one_mouth_p2p',$type);
            $this->setData($one_mouth_id_small,$one_mouth_ph_small,'one_mouth_small',$type);
            $this->setData($one_mouth_id_big,$one_mouth_ph_big,'one_mouth_big',$type);
            $this->setData($one_mouth_id_common,$one_mouth_ph_common,'one_mouth_common',$type);
    	}
    	return true;
    }
    /**
     * [setData description]
     * @param [type] $id_data [description]
     * @param [type] $ph_data [description]
     * @param [type] $name    [description]
     * @param [type] $type    [description]
     */
    private function setData($id_data,$ph_data,$name,$type){
    	$data = $id_data >= $ph_data ? $id_data : $ph_data;
    	if ($type == 1) {
    		self::$first_all[$name][] = (int)$data;
    	} else {
    		self::$reloan_all[$name][] = (int)$data;
    	}
    	return true;
    }
}
