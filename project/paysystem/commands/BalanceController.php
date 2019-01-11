<?php
/**
 *  定时查询通道余额
 */
namespace app\commands;
use app\common\Logger;
use app\models\Balance;
use app\models\BalanceChannel;
use Yii;

// #通知回调
// */5 * * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/paysystem/yii  balance runQuery 1>/dev/null 2>&1
class BalanceController extends BaseController {

    private $rb_channel = [105,112,128];//融宝
    private $cj_channel = [115,116,119];//畅捷
    private $bf_channel = [113,114,123,124];//宝付
    private $ll_channel = [108];//连连
    public function runQuery() {  
        //查询通道列表
        $channel_list = BalanceChannel::getChannelList();   
        if(empty($channel_list)) return false;
        $count = count($channel_list);
        $success = 0;
        foreach($channel_list as $channel){
            $result = $this->doQuery($channel);
            if($result){
                $success++;
            }
        }
        Logger::dayLog('command', 'cjt/runQuery', '总共',$count,'成功',$success);
        echo json_encode(['count'=>$count,'success'=>$success]);
    }
    public function doQuery($channel){
       
        if(empty($channel) || empty($channel['channelid'])) return false;
        $channel_id = $channel['channelid'];
        switch($channel_id){
            case in_array($channel_id,$this->rb_channel):
                $model = new \app\modules\api\common\rongbao\CRongbao;
                $res = $model ->acctQuery($channel_id);
                break;
            case in_array($channel_id,$this->ll_channel):
                $model = new \app\modules\api\common\lianlian\CAuthlian;
                $res = $model ->acctQuery($channel_id);
                break;
            case in_array($channel_id,$this->cj_channel):
                $model = new \app\modules\api\common\cjt\CCjt;
                $res = $model ->getBalance($channel_id);
                break;
            case in_array($channel_id,$this->bf_channel):
                $model = new \app\modules\api\common\baofoo\CBaofooAuth;
                $res = $model ->getBalance($channel_id);
                break;
        }
        Logger::dayLog('balance','doQuery','查询余额',$res);
        if(empty($res) || $res['res_code']!=0){
            Logger::dayLog('balance','doQuery','查询余额失败',$res,$channel);
            return false;
        }
        $postdata = [
            'cp_name'       =>$channel['cp_name'],
            'type'          =>1,
            'aid'           =>$channel['aid'],
            'cid'           =>$channel['cid'],
            'account_name'  =>$channel['mechart_num'],
            'amt_balance'   =>$res['res_data'],
            'create_time'   =>date('Y-m-d H:i:s'),
            'balance_time'  =>date('Y-m-d H:i:s')
        ];
        $model = new Balance;
        $result = $model->addBalance($postdata);
        if(!$result){
            Logger::dayLog('balance','doQuery/addBalance','插入余额记录失败',$postdata);
            return false;
        }
        return true;
    }
}