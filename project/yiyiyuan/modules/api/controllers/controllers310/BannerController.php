<?php
namespace app\modules\api\controllers\controllers310;

use app\common\ApiClientCrypt;
use app\commonapi\Crypt3Des;
use app\commonapi\ImageHandler;
use app\models\news\Activitynew;
use app\models\news\Banner;
use app\models\news\User;
use app\modules\api\common\ApiController;
use Yii;

class BannerController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $status = Yii::$app->request->post('status');
        $type = Yii::$app->request->post('type');
        $user_id = Yii::$app->request->post('user_id');

        if (empty($version) || empty($status) || !isset($type)) {
            exit($this->returnBack('99994'));
        }
        if ($type == 1 && !isset($user_id)) {
            exit($this->returnBack('99994'));
        }
        $bannerModel = new Banner();
        $condition = array(
            'status' => $status,
            'type' => $type,
        );
        $bannerlist = $bannerModel->getBanner($condition);
        foreach ($bannerlist as $k => $v) {
            if ($v->type == 1) {
                if (strpos($v->click_url, '?') !== false) {
                    $bannerlist[$k]->click_url = $v->click_url . '&user_id=' . $user_id;
                } else {
                    $bannerlist[$k]->click_url = $v->click_url . '?user_id=' . $user_id;
                }
            }
        }
        $array = $this->reback($bannerlist, $type);
        //合并进行中的活动banner
        $userObj = (new User())->getUserinfoByUserId($user_id);
        $activityList = (new Activitynew())->getActivity();
        if (!empty($activityList) && in_array($type, [1, 2]) && !empty($userObj)) {
            $mobile = urlencode(Crypt3Des::encrypt($userObj->mobile, (new ApiClientCrypt())->getKey()));
            foreach ($activityList as $item) {
                if (!empty($item->banner_url)) {
                    $activityArray = [];
                    $activityArray['url'] = ImageHandler::getUrl($item->banner_url);
                    $activityArray['click_url'] = yii::$app->request->hostInfo . '/new/lottery?activity_id=' . $item->id . '&mobile=' . $mobile;
                    array_unshift($array['bannerlist'], $activityArray);
                }
            }
        }
        exit($this->returnBack('0000', $array));
    }

    private function reback($bannerlist, $type = 1)
    {
        $array['bannerlist'] = array();
        if (!empty($bannerlist)) {
            if ($type == 1 || $type == 2) {
                foreach ($bannerlist as $key => $val) {
                    $array['bannerlist'][$key]['url'] = $val->url;
                    $array['bannerlist'][$key]['click_url'] = $val->click_url;
                }
            } else {
                $pic_name = [];
                foreach ($bannerlist as $key => $val) {
                    $pic_name[$key] = basename($val->url);
                }
                switch ($type) {
                    case 3:
                        $map = ['640_960', '1242_2208'];
                        foreach ($pic_name as $k => $v) {
                            foreach ($map as $m => $n) {
                                if (strpos($v, $n) !== FALSE) {
                                    $array['bannerlist'][$m]['url'] = ImageHandler::getUrl($bannerlist[$k]->url);
                                    $array['bannerlist'][$m]['click_url'] = ImageHandler::getUrl($bannerlist[$k]->click_url);
                                    break;
                                }
                            }
                        }
                        break;
                    case 4:
                        $map = ['1080_1920'];
                        foreach ($pic_name as $k => $v) {
                            foreach ($map as $m => $n) {
                                if (strpos($v, $n) !== FALSE) {
                                    $array['bannerlist'][$m]['url'] = ImageHandler::getUrl($bannerlist[$k]->url);
                                    $array['bannerlist'][$m]['click_url'] = ImageHandler::getUrl($bannerlist[$k]->click_url);
                                    break;
                                }
                            }
                        }
                        break;
                }
            }
        }
        return $array;
    }
}
