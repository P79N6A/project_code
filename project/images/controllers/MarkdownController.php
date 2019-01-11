<?php
/**
 * markdown
 */
namespace app\controllers;
use Yii;
use yii\helpers\Markdown;
use app\common\Curl;
use app\common\Crypt3Des;
class MarkdownController extends BaseController
{

    public function actionIndex(){
        $myText = file_get_contents(__DIR__.'/DOC/transfer.md'); 
        $myHtml = Markdown::process($myText,'extra');
        echo $myHtml;
    }

    // public function actionTest(){
    //     $str = [
    //         'imgUrls' => [
    //             '1'=>'http://www.xianhuahua.com/index/images/014.png',
    //             '2'=>'http://www.xianhuahua.com/index/images/012.png'
    //         ],
    //         'project' => 'yiyiyuan'
    //     ];
    //     $jsonStr = json_encode($str);
    //     $encrypt = Crypt3Des::encrypt($jsonStr,'013456GJLNVXZbdhijkmnprz');
    //     $curl = new Curl();
    //     $curl->setOption(CURLOPT_CONNECTTIMEOUT, 30);
    //     $curl->setOption(CURLOPT_TIMEOUT, 30);
    //     $content = '';
    //     $content = $curl->post($encrypt);
    //     var_dump($content);
    //     die();

}

