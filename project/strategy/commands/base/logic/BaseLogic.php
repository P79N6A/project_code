<?php
/**
 *  智融决策逻辑
 */
namespace app\commands\base\logic;

use Yii;
use yii\helpers\ArrayHelper;

use app\common\Logger;
use app\common\YArray;
use app\modules\api\common\JavaCrif;
use app\modules\api\common\CloudApi;

class BaseLogic
{
    protected $oJavaCrif;
    protected $oCloudApi;
    protected $oYArray;
    public function __construct()
    {
        $this->oJavaCrif = new JavaCrif();
        $this->oCloudApi = new CloudApi();
        $this->oYArray = new YArray();
    }

    protected function getOriginData($params)
    {   
        # default       
        $default_arr = [
                'credit_score' => 0,
                'model_score_v2' => 0,
                'tianqi_score_v2' => -111,
                'is_black_tq' => 0,
            ];
        $orgin_res = $this->oCloudApi->queryCloud($params,'origin');
        if (!$orgin_res) {
            Logger::dayLog('CreditLogic/queryOriginCrif', '天启接口异常', $params);
            return $default_arr;
        }
        $data = ArrayHelper::getValue($orgin_res,'data',[]);
        if (empty($data)) {
            return $default_arr;
        }
        $data['is_black_tq'] = ArrayHelper::getValue($data,'is_black','0');
        unset($data['is_black']);
        return $data;
    }
}