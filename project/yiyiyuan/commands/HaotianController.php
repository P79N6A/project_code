<?php

namespace app\commands;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

use app\commonapi\Jxldown;
use app\models\dev\Noah_test;
use app\models\dev\Noah_test_5k;

class HaotianController extends BaseController
{
    public function actionIndex()
    {
    	$total = Noah_test_5k::find()->count();
    	$limit = 1;
		$pages = ceil( $total / $limit );
		
		for( $i=0; $i < $pages; $i++ ){
			$this->log("用户" . ($i * $limit). ' -- ' . ($i * $limit + $limit) );

			$data = Noah_test_5k::find()->offset($i * $limit)->limit($limit)->all();
			if( empty($data) ){
				break;
			}
			if(!is_array($data) || empty($data)){  
			// echo  "123";
			return false;
			}
			foreach ($data as $key => $value) {
	    		$obj = new Jxldown($value->mobile);
		        $res = $obj->getRes();
		        $detail = $obj->detail();
		        if(empty($detail)){
		        	break;
		        }
		        // echo "<pre>";
		        // print_r($detail);
		        // die;
		        if(empty($detail['raw_data']['members']['transactions'])){
		        	break;
		        }
		        $arr = $detail['raw_data']['members']['transactions'][0]['calls'];
		        // print_r($arr);
		        // die;
		        $total_arr = count($arr);
		        $o = 0 ;
		        $j = 1 ;
				foreach ($arr as $key => $val) {
					$time = date("Y-m-d H:i:s");
					$o++;
					$j++;
					$getdata[] = array($value->user_id,$value->loan_id,$value->ty,$value->mobile,$val['update_time'],$val['start_time'],$val['init_type'],$val['use_time'],$val['place'],$val['other_cell_phone'],$val['cell_phone'],$val['subtotal'],$val['call_type']);
					if($o==200){
					 	//清空数组  批量插入 变量归零 
					 	$this->log("插入数据总数:".$total_arr.",剩余数量:".$total_arr-$j);
		        		Yii::$app->db->createCommand()->batchInsert(Noah_test::tableName(), 
		        		['user_id', 'loan_id', 'type', 'mobile', 'update_time', 'start_time', 'init_type', 'use_time', 'place', 'other_cell_phone', 'cell_phone', 'subtotal', 'call_type'],$getdata)->execute();
		        		$o=0;
		        		$getdata=[];
					}
				}
				if(!empty($getdata))
				{
					// $this->log("插入数据总数:".$total_arr.",剩余数量:".$total_arr-$j);
	        		Yii::$app->db->createCommand()->batchInsert(Noah_test::tableName(), 
		        		['user_id', 'loan_id', 'type', 'mobile', 'update_time', 'start_time', 'init_type', 'use_time', 'place', 'other_cell_phone', 'cell_phone', 'subtotal', 'call_type'],$getdata)->execute();
        		}

    		}
		}
		// if (!$detail) {
        //     return '无报告或获取失败!';
        // }
        // if (!$this->chkReport($detail)) {
        //     return '报告内容为空, 请尝试聚信立后台查询!';
        // }
    }


    // 纪录日志
	private function log($message){
		echo $message."\n";
	}
}
