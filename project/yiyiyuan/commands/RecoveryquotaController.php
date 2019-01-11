<?php

/**
 * 恢复过期的临时额度
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用
 *   linux : /data/wwwroot/yiyiyuan/yii getloanover > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii recoveryquota
 */

namespace app\commands;

use app\models\dev\User_temporary_quota;
use app\models\dev\User_quota;

use Yii;
use yii\console\Controller;

/**
 * 这个包含地址需要根据个人文件路径进行设置绝对路径
 */

class RecoveryquotaController extends Controller {
	
	// 命令行入口文件
	public function actionIndex()
    {
        $sucess = 0;
        $fails = 0;
        //查询过期的临时额度记录
        $now_time = date('Y-m-d H:i:s');
        $where = array(
            'AND',
            ['status' => [1,-1]],
            ['<=', 'end_time', $now_time],
        );
        $total = User_temporary_quota::find()->where($where)->count();
        $limit = 500;
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $data = User_temporary_quota::find()->where($where)->offset($i * $limit)->limit($limit)->all();
            if (!empty($data)) {
                foreach ($data as $key => $val) {
                    $transaction = Yii::$app->db->beginTransaction();
                    $result = $val->recoveryQuota(-1);
                    if($result){
                        $transaction->commit();
                        $sucess ++;
                    }else{
                        $transaction->rollBack();
                        $fails ++;
                        $this->log("修改失败: id={$val['id']}");
                    }
                }
            } else {
                break;
            }
        }
        $new_where = [
            'AND',
            ['status' => '-1'],
            ['<', 'end_time', $now_time],
        ];
        User_temporary_quota::updateAll(['status' => '2'], $new_where);
        $this->log("\n处理结果:成功{$sucess}条, 失败{$fails}条");
    }
	

	// 纪录日志
	private function log($message){
		echo $message."\n";
	}
}