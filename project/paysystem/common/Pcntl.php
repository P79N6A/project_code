<?php
namespace app\common;
use app\common\Logger;
class Pcntl{
    private $worker_num;
    private $data_count;
    private $callback;
    private $limit;
    private $startTime;
    private $endTime;
    public function __construct($data_count,$worker_num=5,$callback,$startTime='',$endTime='') {
        $this->data_count = $data_count;
        $this->worker_num = $worker_num;
        $this->limit = ceil($this->data_count/$this->worker_num);
        $this->callback = $callback;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }
    public function fork_process(){
        pcntl_signal(SIGCHLD, SIG_IGN);
        for($i=0;$i<$this->worker_num;$i++){
            $pid = pcntl_fork();
            if($pid==-1){
                die('error');
            }else if($pid>0){
                pcntl_wait($status,WNOHANG);
            }else if($pid==0){
                $offset = $i*$this->limit;
                $callback = $this->callback;
                // $limit = $this->getLimit($offset);
                $data = call_user_func($callback,$this->startTime,$this->endTime,$offset,$this->limit);
                Logger::dayLog('pcntl','callback',$callback,'offset',$offset,'limit',$limit,'datacount',count($data));
                return $data;
            }
        }
        
    }
    private function getLimit($offset){
        $limit = $this->limit;
        if($limit+$offset>$this->data_count){
            $limit = $this->data_count-$offset;
        }
        return $limit;
    }
}
?>