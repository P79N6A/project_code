<?php
namespace app\commands\mall;
use app\commands\BaseController;
use app\models\news\ActivityFalseData;
use app\models\news\User;
class ActivityintegralincreaseController extends BaseController
{
    /**
     * @return mixed|void
     * 用户积分增加
     */
    public function actionIndex()
    {
        $data = [];  //空数组
        $ActivityFalseData = new ActivityFalseData();
        $count = User::find()->select(['count(from_code) as fromCode','from_code'])->where(['come_from'=>5])->andWhere(['NOT', ['from_code' => '']])->groupBy(['from_code'])->asArray()->all();
        foreach ($count as $k=>$v) {
            $arr = User::find()->select(['mobile', 'invite_code', 'user_id'])->where(['invite_code'=>$v['from_code']])->asArray()->one();
            $sel = ActivityFalseData::find()->where(['mobile'=>$arr['mobile']])->one();
            if(!empty($sel)){
                $adm = ['integral'=>$v['fromCode']*10];
                $sel->save_address($adm);
            }else{
                $data[] = [
                    'mobile'=>$arr['mobile'],
                    'status'=>1,
                    'integral'=>$v['fromCode']*10,
                    'create_time'=>date('Y-m-d H:i:s'),
                    'last_modify_time'=>date('Y-m-d H:i:s'),
                ];
            }
        }
        $res = 0;
        $countNum = count($data);
        if (!empty($data)) {
            //总条数
            $res = $ActivityFalseData->insertBatch($data);
        }
        $this->log("\n all:{$countNum},SUCCESS:{$res}\n");
    }

    // 纪录日志
    private function log($message)
    {
        echo $message . "\n";
    }
}