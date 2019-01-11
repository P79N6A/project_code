<?php
namespace app\commands;
use Yii;
use app\common\Logger;
class SmsWorkerController extends BaseWorkerController {
    
    public function runnsqtest(){
        $this->runNsq(self);
    }

    public function customer($msg){
        echo "READ\t" . $msg->getId() . "\t" . $msg->getPayload(). "\n";		
    }

    public function producer(){
        $runId = md5(microtime(TRUE));
        $data = [
            '0' => ['message'=>'0','run'=>$runId],
            '1' => ['message'=>'1','run'=>$runId],
            '2' => ['message'=>'2','run'=>$runId],
        ];
        $succ = 0;
        foreach ($data as $key => $value) {
            $res = $this->addNsq($value);
            if($res) 
                $succ ++;
            else 
                Logger::dayLog('command', 'producer', $value);
        }
        echo $succ;
    }

}
