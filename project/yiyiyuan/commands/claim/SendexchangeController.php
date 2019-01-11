<?php
namespace app\commands\claim;

/**
 * 刚兑金额推送
 */
use app\commands\BaseController;
use app\commonapi\ApiSign;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\news\Exchange_money;
use Yii;

class SendexchangeController extends BaseController
{
    public function actionIndex()
    {
        $data = date('Y-m-d 00:00:00', strtotime('+1 days'));
        $where = [
            'AND',
            ['type' => 4],
            ['cur_date' => $data]
        ];
        $info = Exchange_money::find()->where($where)->one();
        if (empty($info)) {
            exit('no money,刚兑金额未设置');
        }
        $this->send($info);
    }

    private function send($info)
    {
        if (empty($info->money) || empty($info->cur_date)) {
            return false;
        }
        $data = [
            'amount' => $info->money,
            'set_date' => $info->cur_date,
        ];
        $signData = (new ApiSign)->signData($data);
        $signData['_sign'] = base64_encode($signData['_sign']);
        $api = 'against/setamount';
        $url = Yii::$app->params['exchange_url'] . $api;
        $result = Http::interface_post($url, $signData);
        Logger::dayLog('depository/claim/sendexchange', $result);
        $res = json_decode($result, TRUE);
        if ($res) {
            if ($res['rsp_code'] == '0000') {
                Logger::dayLog('depository/claim/sendexchange_success', $signData, $res);
            } else {
                Logger::dayLog('depository/claim/sendexchange_error', $signData, $res);
            }
        }
    }
}