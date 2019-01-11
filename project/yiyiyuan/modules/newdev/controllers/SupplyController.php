<?php
/**
 * Created by PhpStorm.
 * User: wangyongqiang
 * Date: 2017/4/26
 * Time: 15:56
 */
namespace app\modules\newdev\controllers;

use app\commands\SubController;
use app\models\dev\User;
use app\models\dev\Userwx;
use app\models\dev\UserImages;

use app\commonapi\Common;
use Yii;


class SupplyController extends NewdevController
{
    public $layout = 'supply';
    public $enableCsrfValidation = false;

    public function actionIndex() {
        //1 帐号检查
//        $mobile = $this->getVal('mobile');
//        $userinfo = User::find()->select(['user_id'])->where(['mobile' => $mobile])->one();
        $userinfo = $this->getUser();
        if(!$userinfo){
            return $this->redirect('/dev/site/error');
        }
        $user_id = $userinfo->user_id;

        //2 判断是浏览还是提交
        $isPost = Yii::$app->request->isPost;
        $saveMsg='';
        if($isPost){
            $count = UserImages::find() -> where(['user_id'=>$user_id]) ->count();
            if($count<3){
                $saveResult = $this->uploadimg($user_id);
                $saveMsg = $saveResult ? '' : '保存失败';
            }else{
                $saveMsg = "只能上传3张图片";
            }
        }

        //3 显示图片列表
        $imgList = UserImages::find() -> where(['user_id'=>$user_id]) ->orderBy('id ASC') -> all();
        $imgList = empty($imgList) ? [] : $imgList;
        foreach($imgList as $v){
            $v->img = $v->img;
        }
        $this->getView()->title = "补充资料";
        return $this->render('index', [
            'imgList' => $imgList,
            'imgDefault' => "/images/dev/bczil_photo.png",
            'isPost' => $isPost,
            'saveMsg' => $saveMsg,
            'encrypt' => \app\commonapi\ImageHandler::encryptKey($user_id, 'supply'),
        ]);
    }
    /**
     * 上传图片
     */
    protected function uploadimg($user_id){
        //1 参数验证
        if(!$user_id){
            return false;
        }

        //2 保存图片
        /*$to_path = '/upload/supply/' . date('Y/m/d/') . $user_id; //上传文件的目标路径
        $images = [];
		foreach ($_FILES as $name => $up_info) {
	        $file_info = Common::Uploadfun($up_info, '.'.$to_path); //调用单文件上传函数
	        if (!empty($file_info)){
	        	$imgPath = $to_path .'/'.$file_info;
				if(file_exists('.'.$imgPath)){
					$images[$name] = $imgPath;
				}
	        }
		}*/

        //3 组合数据
        $ids = Yii::$app->request->post('supplyId');
        $urls = Yii::$app->request->post('supplyUrl');
        $imgData = [];
        foreach($urls as $k=>$url){
            if(!$url){
                continue;
            }
            $id = isset($ids[$k]) ? $ids[$k] : '';
            $imgData[] = [
                'user_id' => $user_id,
                'id' => $id,
                'img' => $url,
            ];
        }

        //3 返回结果  保存所有图片
        $oUserImages = new UserImages();
        $result = $oUserImages -> saveImages($imgData);
        return $result ;
    }

    public function actionError() {
        return $this->render('error');
    }
}