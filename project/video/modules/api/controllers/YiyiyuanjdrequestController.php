<?php
/**
 * 记录榕树京东url相关数据,需要一亿元接口访问
 * @author
 */
namespace app\modules\api\controllers;

use app\common\Crypt3Des;
use app\common\Logger;
use app\models\BrYunyingshang;
use app\modules\api\common\ApiController;
use app\modules\api\common\chanpay\ChanpayOnline;
use Yii;

class YiyiyuanjdrequestController extends ApiController {


    protected $server_id = 10;
    public $chanpay;
    /**
     * 初始化
     */
    public function init(){
        /*
        $a = [
            'mobile'=>'13269311057',
            'resourceUrl'=>'url',
            '_sign'=>'14cc8e923ef95d8f6eb757476bc5eb70',
        ];
        echo Crypt3Des::encrypt(json_encode($a), "24BEFILOPQRUVWXcdhntvwxy");;exit;
        */
        parent::init();
        $env = YII_ENV_DEV ? 'dev' : 'prod';
        $this->chanpay = new ChanpayOnline($env);
    }

    public function actionIndex()
    {
        $params = $this->reqData;
        $ret = $this->addList($params);
        if (!$ret) {
            Logger::dayLog('shang/error', $params);
            return $this->resp(16003, "新增失败");
        }
        return $this->resp('0', $ret);
    }

    /**
     * 格式数据
     * @param $params
     * @return array
     */
    private function formatData($params)
    {
        $params = json_decode($params['data'], true);
        return [
            'mobile'=>$params['mobile'],
            'resourceUrl'=>$params['resourceUrl'],
            'status'=>'INIT',
            'down_time'=>'',
            'last_modify_time'=>date('Y-m-d H:i:s', time()),
            'create_time'=>date('Y-m-d H:i:s', time()),
        ];

    }
    private function addList($params)
    {
        $condition = $this->formatData($params);
        $yingshang_info = new BrYunyingshang();
        return $yingshang_info->addList($condition);
    }



}
