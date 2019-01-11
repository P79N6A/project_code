<?php

namespace app\modules\api\common\xn;
use Yii;
use app\models\xn\XnRemit;
use app\models\xn\XnBank;
use app\models\ZFLimit;
use yii\helpers\ArrayHelper;
/**
 * 小诺出款规则
 */
class XnRemitRule {

    private $error_codes = [
            '0000'   => '成功',
            '104002' => '请求超频',
            '104003' => '单日出款总额超限',
            '104004' => '小诺出款总额超限',
            '104005' => '节假日不接受推送用户',
            '104006' => '推送用户不在有效时间范围内',
            '104007' => '出款金额需大于0',
            '104008' => '周末不接受推送用户',
        ];
    /**
     * Undocumented function
     * 获取出款规则
     * @param [type] $postData
     * @return void
     */
    public function getRemitRule($postData){
        // 进行防入侵验证
        $result = $this->intrusionPrevention($postData['idNumber']);
        if ($result['res_code'] != '0000') {
            return $result;
        }
        $settle_amount = ArrayHelper::getValue($postData,'loanAmount',0);
        if($settle_amount<=0){
            return $this->error('104007');
        }
        //查询资方出款限制
        $top_limit = (new ZFLimit)->getTopLimit(ZFLimit::XN_SOURCE);
        if(empty($top_limit['dayTopMoney'])){
            $top_limit['dayTopMoney'] = XnRemit::DAY_TOP_MONEY;
        }
        // if(empty($top_limit['totalMoney'])){
        //     $top_limit['totalMoney'] = XnRemit::TOTAL_MONEY;
        // }
        if(empty($top_limit['date_config'])){
            $top_limit['date_config'] = $this->getDateConfig();
        }
        if(empty($top_limit['start_time'])){
            $top_limit['start_time'] = XnRemit::STARTTIME;
        }
        if(empty($top_limit['end_time'])){
            $top_limit['end_time'] = XnRemit::ENDTIME;
        }
        // 判断当天出款总额是否超限
        //小诺每日出款
        $dayRemitMoney = (new XnRemit)->getDayTopMoney();       
        $isDayMax = $this->isDayMax($settle_amount,$dayRemitMoney,$top_limit['dayTopMoney']);
        if ($isDayMax) {
            return $this->error('104003');
        }
        //小诺总出款
        // $allRemitMoney = (new XnRemit)->getTotalMoney(); 
        // $isTotalMax = $this->isTotalMax($settle_amount,$allRemitMoney,$top_limit['totalMoney']); 
        // if ($isTotalMax) {
        //     return $this->error('104004');
        // }
        //节假日限制
        $isStopDate = $this->isStopDate($top_limit['date_config']);
        if($isStopDate){
            return $this->error('104005');
        }       
        //时间段限制 9点 -- 15点
        $isTimeLimit = $this->isTimeLimit($top_limit['start_time'],$top_limit['end_time']);
        if($isTimeLimit){
            return $this->error('104006');
        }
        //周末限制
        $isWeekend = $this->isWeekend();
        if($isWeekend){
            return $this->error('104008');
        }
        return $this->success('success');
    }
    
    /**
     * 防入侵
     * @return [type] [description]
     */
    private function intrusionPrevention($identityid) {
        // 判断是否是超频请求,超频则拒绝访问
        $ret = (new XnRemit)->isOften($identityid);
        if ($ret) {
            return $this->error('104002');
        }
        return $this->success('success');
    }
    
    /**
     * 返回成功json
     * @param $res_data
     * @return json
     */
    private function success($res) {
        return [
            'res_code' => '0000',
            'res_data' => $res,
        ];
    }
    /**
     * 返回错误json
     * @param $res_code
     * @param $res_data
     * @return json
     */
    private function error($res_code) {
        $res_data = $this->getcode($res_code);
        return [
            'res_code' => (string) $res_code,
            'res_data' => $res_data,
        ];
    }

    /**
     * 错误码
     * @param  str $error_code 
     * @return str
     */
    private function getcode($error_code) {
        return isset($this->error_codes[$error_code]) ? $this->error_codes[$error_code] : 'UNKNOWN';
    }
    /**
     * Undocumented function
     * 节假日限制
     * @return void
     */
    private function getDateConfig()
    {
        $arr = array(
            array('st'=>strtotime('2017-12-31'),'et'=>strtotime('2018-01-02')),
            array('st'=>strtotime('2018-02-15'),'et'=>strtotime('2018-02-22')),
            array('st'=>strtotime('2018-04-05'),'et'=>strtotime('2018-04-07')),
            array('st'=>strtotime('2018-05-01'),'et'=>strtotime('2018-05-04')),
            array('st'=>strtotime('2018-06-18'),'et'=>strtotime('2018-06-19')),
            array('st'=>strtotime('2018-09-24'),'et'=>strtotime('2018-09-25')),
            array('st'=>strtotime('2018-10-01'),'et'=>strtotime('2018-10-07'))
        );
        return json_encode($arr);
    }
    /**
     * Undocumented function
     * 是否超过当日限额
     * @param [type] $settle_amount
     * @param [type] $dayRemitMoney
     * @param [type] $dayTopMoney
     * @return boolean
     */
    private function isDayMax($settle_amount,$dayRemitMoney,$dayTopMoney){
        // 一日可出金额
        $rest_money = $this->getDayRestMoney($dayTopMoney,$dayRemitMoney);
        // $rest_money <= 0
        if( bccomp( $rest_money, 0, 4 ) !== 1  ){
            return true; // 达上限
        }

        //$settle_amount > $rest_money 表示达上限
        return  bccomp( $settle_amount, $rest_money, 4 ) === 1;
    
    }
    /**
     * 当日可出款金额
     * @param  [type] $aid [description]
     * @return [type]      [description]
     */
    private function getDayRestMoney($dayTopMoney,$dayRemitMoney){
        //1 一日最大限额
        if( bccomp( $dayTopMoney, 0, 4 ) !== 1  ){
            return 0;
        }
        //2 可出金额
        $rest_amount = $dayTopMoney - $dayRemitMoney;
        // $rest_amount <= 0
        if( bccomp( $rest_amount, 0, 4 ) !== 1  ){
            return 0;
        }
        return  $rest_amount;
    }
    /**
     * Undocumented function
     * 是否超过总限额
     * @param [type] $settle_amount
     * @param [type] $allRemitMoney
     * @param [type] $totalMoney
     * @return boolean
     */
    private function isTotalMax($settle_amount,$allRemitMoney,$totalMoney){
        // 可出金额
        $rest_money = $this->getRestMoney($totalMoney,$allRemitMoney);
        // $rest_money <= 0
        if( bccomp( $rest_money, 0, 4 ) !== 1  ){
            return true; // 达上限
        }

        //$settle_amount > $rest_money 表示达上限
        return  bccomp( $settle_amount, $rest_money, 4 ) === 1;
    
    }
    /**
     * 当日可出款金额
     * @param  [type] $aid [description]
     * @return [type]      [description]
     */
    private function getRestMoney($totalMoney,$allRemitMoney){
        //1 最大限额
        if( bccomp( $totalMoney, 0, 4 ) !== 1  ){
            return 0;
        }
        //2 可出金额
        $rest_amount = $totalMoney - $allRemitMoney;
        // $rest_amount <= 0
        if( bccomp( $rest_amount, 0, 4 ) !== 1  ){
            return 0;
        }
        return  $rest_amount;
    }
    /**
     * Undocumented function
     * 是否是节假日
     * @param [type] $date_config
     * @return boolean
     */
    private function isStopDate($date_config){
        $stopDate = json_decode($date_config,true);
        $time = time();
        foreach($stopDate as $k=>$v)
        {
            if($time>$v['st'] && $time<$v['et'])
            {
                return true;
            }
        }
        return false;
    }
    /**
     * Undocumented function
     * 是否是时间段限制 默认9点到15点可推送用户
     * @param [type] $start_time
     * @param [type] $end_time
     * @return boolean
     */
    private function isTimeLimit($start_time,$end_time){
        $checkDayStr = date('Y-m-d ',time());  
        $timeBegin = strtotime($checkDayStr.$start_time);  
        $timeEnd = strtotime($checkDayStr.$end_time);  
        $curr_time = time();  
        if($curr_time >= $timeBegin && $curr_time <= $timeEnd)  
        {  
            return false;
        }  
        return true;
    }
    //判断是否是周末
    private function isWeekend(){
        if((date('w') == 6) || (date('w') == 0)){
            return true;
        }
        return false;
    }
}