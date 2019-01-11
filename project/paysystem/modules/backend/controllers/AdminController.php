<?php

namespace app\modules\backend\controllers;

use app\common\Common;
use app\common\Func;
use app\models\Whitelist;
use Yii;
use yii\filters\AccessControl;

abstract class AdminController extends \app\common\BaseController {
    public $layout = 'main';
    public $vvars; // 模板变量
    public $aid = 1;//项目id
    private $allowIP = ['127.0.0.1', '121.69.71.58', '124.193.149.180', '124.200.104.130','121.69.104.10','124.207.196.42'];
    /**
	 * 初始化操作
	 */
	public function init(){
	    //限制访问ip
        $ipAllow = $this->chkIp();
        if( !$ipAllow ){
            echo '访问IP受限，请联系管理员！';die;
        }
		$aid = $this->getNowAid();
        //$aid = empty($aid)?1:$aid;
        if(!empty($aid) && is_numeric($aid)){
            $this->vvars['nav'] = $aid==1?'pay':'pay'.$aid;
        }else{
            $this->vvars['nav'] = 'pay0';
        }
        $this->aid = $aid;
	}
    public function getNowAid(){
         $filepath = Yii::$app->basePath.'/log/aid.txt';
         if(!file_exists($filepath)){
             touch($filepath,0775);

         }
         $aid =   file_get_contents($filepath);
         return $aid;
    }
    public function setNowAid($aid){
        $filepath = Yii::$app->basePath.'/log/aid.txt';
        file_put_contents($filepath,$aid);
    }
    /**
     * 返回session信息
     */
    public function getUser() {
        return Yii::$app->admin->identity;
    }
    /**
     * 只有登陆帐号才可以访问
     * 子类直接继承
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'user'  => 'admin',
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [],
                        'roles' => ['@'], //@代表授权用户
                    ],
                ],
            ],
        ];
    }

    // start adminsession表session操作
    //设置session
    public function setVal($key, $val) {
        Yii::$app->session->set($key, $val);
    }
    //获取session
    public function getVal($key) {
        return Yii::$app->session->get($key);
    }
    //删除session
    public function delVal($key) {
        return Yii::$app->session->remove($key);
    }

    /**
     * 验证访问IP
     * @return bool
     */
    private function chkIp(){
        $ip = Func::get_client_ip();
        if(empty($ip)){
            return false;
        }
        return in_array($ip,$this->allowIP);
    }

    // end

    /**
     * 显示结果信息
     * @param $res_code 错误码0 正确  | >0错误
     * @param $res_data      结果   | 错误原因
     */
    /**
     * 显示结果信息
     * @param  int $res_code  错误码0 正确  | >0错误
     * @param  str $res_data结果   | 错误原因
     * @param  str $type   json | redict
     * @param  str $redirect [description]
     * @return json | html
     */
    protected function showMessage($res_code, $res_data, $type = null, $redirect = null, $timeout = 3) {
        // 自动判断返回类型
        if (empty($type)) {
            $type = Yii::$app->request->getIsAjax() ? 'json' : 'html';
        }
        $type = strtoupper($type);
        // 返回结果: 统一json格式或消息提示代码
        switch ($type) {
        case 'JSON':
            return json_encode([
                'res_code' => $res_code,
                'res_data' => $res_data,
            ]);
            break;
        case 'HTML':
        default:
            $redirect = is_null($redirect) ? Yii::$app->request->getReferrer() : $redirect;
            $this->vvars['menu'] = '';
            return $this->render('/showmessage', [
                'res_code' => $res_code,
                'res_data' => $res_data,
                'redirect' => $redirect,
                'timeout' => $timeout,
            ]);
            break;
        }
    }
}
