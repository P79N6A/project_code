<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace app\commands;

use app\commonapi\Http;
use app\models\dev\Accesstoken;
use Yii;
use yii\web\Controller;
use yii\web\DbSession;
$session = new DbSession;
$session->open();

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AdminController extends Controller
{
	
	//获取access_token值
	public function getAccessToken()
	{
		        $appId = \Yii::$app->params['AppID']; //，需要在微信公众平台申请自定义菜单后会得到
		        $appSecret = \Yii::$app->params['AppSecret']; //需要在微信公众平台申请自定义菜单后会得到


		//先查询对应的数据表是否有token值
		$accesstoken = Accesstoken::find()->where(['type' => 1])->one();
		if( isset( $accesstoken->access_token ))
		{
			//判断当前时间和数据库中时间
			$time = time();
			$gettokentime = $accesstoken->time;
			if(($time - $gettokentime) > 7000)
			{
				//重新获取token值然后替换以前的token值
				$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appId."&secret=".$appSecret;
				$data = Http::getCurl($url);//通过自定义函数getCurl得到https的内容
				$resultArr = json_decode($data, true);//转为数组
				$accessToken = $resultArr["access_token"];//获取access_token
	
				//替换以前的token值
				$sql = "update yi_access_token set access_token = '$accessToken',time=$time where type=1";
				$result = Yii::$app->db->createCommand($sql)->execute();
	
				return $accessToken;
			}
			else
			{
				return $accesstoken->access_token;
			}
		}
		else
		{
			//获取token值并把token值保存在数据表中
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appId."&secret=".$appSecret;
			$data = Http::getCurl($url);//通过自定义函数getCurl得到https的内容
			$resultArr = json_decode($data, true);//转为数组
			$accessToken = $resultArr["access_token"];//获取access_token
				
			$time = time();
			$sql = "insert into ".Accesstoken::tableName()."(access_token,time) value('$accessToken','$time')" ;
			$result = Yii::$app->db->createCommand($sql)->execute();
				
			return $accessToken;
		}
	}
	
	//调用模板推送接口推送消息
	public function sendTemplatetouser($data)
	{
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$this->getAccessToken();
		$result = Http::dataPost($data,$url);
		return $result;
	}
	
	public function getCode($num,$w,$h) {
	// 去掉了 0 1 O l 等
	$str = "23456789abcdefghijkmnpqrstuvwxyz";
	$code = '';
	for ($i = 0; $i < $num; $i++) {
		$code .= $str[mt_rand(0, strlen($str)-1)];
	}
	//将生成的验证码写入session，备验证页面使用
	$_SESSION["code_char"] = $code;
	//创建图片，定义颜色值
	Header("Content-type: image/PNG");
	$im = imagecreate($w, $h);
	$black = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
	$gray = imagecolorallocate($im, 118, 151, 199);
	$bgcolor = imagecolorallocate($im, 235, 236, 237);

	//画背景
	imagefilledrectangle($im, 0, 0, $w, $h, $bgcolor);
	//画边框
	imagerectangle($im, 0, 0, $w-1, $h-1, $gray);
	//imagefill($im, 0, 0, $bgcolor);



	//在画布上随机生成大量点，起干扰作用;
	for ($i = 0; $i < 80; $i++) {
		imagesetpixel($im, rand(0, $w), rand(0, $h), $black);
	}
	//将字符随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
	$strx = rand(3, 8);
	for ($i = 0; $i < $num; $i++) {
		$strpos = rand(1, 6);
		imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black);
		$strx += rand(8, 14);
	}
	imagepng($im);
	imagedestroy($im);
	}

    protected function get($name = null, $defaultValue = null) {
        $v = Yii::$app->request->get($name, $defaultValue);
        $v = $v ? $this->new_trim($v) : $v;
        return $v;
    }

    protected function post($name = null, $defaultValue = null) {
        $v = Yii::$app->request->post($name, $defaultValue);
        $v = $this->new_trim($v);
        return $v;
    }

    protected function new_trim($string) {
        if (!is_array($string))
            return trim($string);
        foreach ($string as $key => $val) {
            $string[$key] = $this->new_trim($val);
        }
        return $string;
    }
}
