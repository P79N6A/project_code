<?php
namespace app\modules\sfapi\logic;

use Yii;
use yii\helpers\ArrayHelper;

use app\models\Request;

use app\modules\sfapi\common\PublicFunc;
use app\modules\sfapi\common\JavaCrif;
use app\modules\sfapi\common\CloudApi;

class CreditLogic extends BaseLogic
{
    private $publicFunc;
    private $javaCrif;
    private $cloudApi;

    public function __construct()
    {
        $this->publicFunc = new PublicFunc();
        $this->javaCrif = new JavaCrif();
        $this->cloudApi = new CloudApi();
    }
    
    /**
     * [applyreg 注册决策]
     * @return [type] [description]
     */
    public function applyreg($data)
    {   
        $data['from'] = Request::REG;//申请借款决策
        //记录请求
        $func = $this->publicFunc;
        $request = $func->addRequest($data);
        if (!$request) {
            return $this->returnInfo(false, '请求记录失败');
        }
        $data['request_id'] = $request;
        //请求cloud获取同盾数据
        $cloud_res = $this->cloudApi->cloudApi($data,'reg'); // 注册请求借款同盾 数据更全
        if (empty($cloud_res)) {
            return $this->returnInfo(false, '请求cloud失败');
        }
        //标准化用户参数
        $normal_data = $func->normalRegData($data);
        //百度信用评级
        $baidu_risk = $this->cloudApi->getBaiduRiskInfo($normal_data);
        $reg_data = array_merge($normal_data,$cloud_res,$baidu_risk);
        // var_dump($reg_data);die;
        //记录用户决策数据
        $loan_res = $func->saveReg($reg_data);
        if (!$loan_res) {
            return $this->returnInfo(false, '保存用户信息失败');
        }
        return $this->returnInfo(true, $reg_data);
    }

    //请求决策系统并返回结果
    public function queryCrif($process_code,$reg_data)
    {
        $request = ArrayHelper::getValue($reg_data, 'request_id');
        $crif_res = $this->javaCrif->queryCrif($request,$reg_data,$process_code);
        if (empty($crif_res)) {
            return $this->returnInfo(false, '决策异常');
        }
        //记录决策结果
        $save_res = $this->publicFunc->saveRes($reg_data,$crif_res);
        if (!$save_res) {
            return $this->returnInfo(false, '结果记录异常');
        }
        $res_data = ArrayHelper::getValue($crif_res, 'RESULT');
        return $this->returnInfo(true, $res_data);
    }
}