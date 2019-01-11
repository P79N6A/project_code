<?php
namespace app\modules\api\controllers\controllers312;

use app\models\news\Card_bin;
use app\modules\api\common\ApiController;
use Yii;

class BankcardbinController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $card = Yii::$app->request->post('card');

        if (empty($version) || empty($card)) {
            exit($this->returnBack('99994'));
        }
        $cardbinModel = new Card_bin();
        $bin = $cardbinModel->getCardBinByCard($card);
        $array['cardbin']['bank_name'] = '';
        $array['cardbin']['bank_abbr'] = '';
        $array['cardbin']['card_type'] = '';
        if (empty($bin)) {
            exit($this->returnBack('10041', $array));
        } else {
            $array['cardbin']['bank_name'] = isset($bin['bank_name']) ? $bin['bank_name'] : '';
            $array['cardbin']['bank_abbr'] = isset($bin['bank_abbr']) ? $bin['bank_abbr'] : '';
            $array['cardbin']['card_type'] = isset($bin['card_type']) ? $bin['card_type'] : '';
            exit($this->returnBack('0000', $array));
        }
    }
}
