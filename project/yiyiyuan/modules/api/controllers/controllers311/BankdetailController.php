<?php
namespace app\modules\api\controllers\controllers311;

use app\models\news\User_bank;
use app\modules\api\common\ApiController;
use Yii;

class BankdetailController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $bank_id = Yii::$app->request->post('bank_id');

        if (empty($version) || empty($bank_id)) {
            exit($this->returnBack('99994'));
        }
        $bank = User_bank::findOne($bank_id);
        if (empty($bank)) {
            exit($this->returnBack('99997'));
        } else {
            $array['cardbin']['bank_name'] = empty($bank['bank_name']) ? '银行卡' : $bank['bank_name'];
            $array['cardbin']['bank_abbr'] = $bank['bank_abbr'];
            $array['cardbin']['type'] = $bank['type'] == 0 ? 0 : 1;
            $array['cardbin']['card'] = substr($bank['card'], 0, 4) . ' **** **** ' . substr($bank['card'], strlen($bank['card']) - 4, 4);
            exit($this->returnBack('0000', $array));
        }
    }
}
