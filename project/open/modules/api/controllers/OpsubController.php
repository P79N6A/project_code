<?php
/**
 * 运营商聚合路由
 * @author zhangfei
 */

namespace app\modules\api\controllers;

use Yii;
use app\models\JxlRequestModel;
use app\models\JxlStat;
use app\modules\api\common\ApiController;
use app\modules\api\common\juxinli\Clientjxl714;
use app\modules\api\common\rong\RongApi;
use app\modules\api\common\yidun\ClientYd714;
use app\modules\api\common\sjt\ClientSjt;
use app\common\Logger;
use app\common\Crypt3Des;

class OpsubController extends ApiController {
    private $env;
    protected $server_id = 8;

    public function init() {
        // parent::init();
        $this->env = YII_ENV_DEV ? 'dev' : 'prod';
    }

    public function actionServicepwd() {//提交服务密码请求运营商
        $postData = $this->post();
        //参数校验
        $reqId = isset($postData['requestid']) ? $postData['requestid'] : '';
        $pwd = isset($postData['password']) ? $postData['password'] : '';
        if(empty($postData)){
			return $this->resp('25022', '无效的请求');
		}
        if (!$reqId) {
            return $this->resp('28001', '参数异常');
        }
        if (!$pwd) {
            return $this->resp('25002', '服务密码不能为空');
        }
        
        $requestid = $this->opDecrypt($reqId);
        $request = new JxlRequestModel();
        $reqData = $request->getById($requestid);
		if(empty($reqData)){
			return $this->resp('25024', '无效的请求');
        }
        $reqData->password = $pwd;
        $reqData->save();
        $data = [
            'id' =>$reqData['id'],
            'user_id' => $reqData['id'],
            'aid' =>$reqData['aid'],
            'name' =>$reqData['name'],
            'idcard' =>$reqData['idcard'],
            'phone' =>$reqData['phone'],
            'account' =>$reqData['account'],
            'password' =>$reqData['password'],
            'website' =>$reqData['website'],
            'process_code' =>$reqData['process_code'],
            'source' =>$reqData['source'],
            'from' =>$reqData['from'],
            'contacts' =>$reqData['contacts'],
            'callbackurl' =>$reqData['callbackurl']
        ];
        $resData = $this->getAlleywayData($data); //结果返回业务端
       
        if (empty($resData)) {
            return $this->resp(25027, '返回结果异常');
        }
        return $this->resp($resData['res_code'], $resData['res_data']);
    }
    /**
     * 运营商各个通道请求
     * 融、聚信立
     * @return requestRes
     */
    private function getAlleywayData($data) {
        if (empty($data)) {
            return $this->resp('25026', '参数错误');
        }
        switch ($data['source']) {
        case '1':
        case '2':
            $crawler = new Clientjxl714();
            break;
        case '4':
            $crawler = new ClientYd714($this->env);
            break;
        case '6':
            $crawler = new ClientSjt();
            break;
        default:
            $crawler = null;
            break;
        }
        if (!$crawler) {
            return $this->resp('25025', 'source参数错误');
        }
        $returnData = $crawler->returnResdata($data);
        return $returnData;
    }

    private function opDecrypt($requestid){//解密
		$requestid = Crypt3Des::decrypt($requestid, Yii::$app->params['trideskey']);
		return $requestid;
	}
}
