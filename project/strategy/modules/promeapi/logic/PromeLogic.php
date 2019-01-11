<?php
namespace app\modules\promeapi\logic;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;

use app\models\yyy\YyyApi;
use app\modules\promeapi\common\SaveFunc;
use app\modules\promeapi\common\JavaCrif;
use app\modules\promeapi\common\Analysis;
use app\modules\promeapi\common\UserApi;
use app\modules\promeapi\common\CloudApi;
use app\models\Request;

class PromeLogic extends BaseLogic
{

    private $saveFunc;
    private $javaCrif;
    private $analysis;
    private $userApi;

    public function __construct()
    {
        $this->saveFunc = new SaveFunc();
        $this->javaCrif = new JavaCrif();
        $this->analysis = new Analysis();
        $this->userApi = new UserApi();
        $this->cloudAapi = new CloudApi();
    }
    
    /**
     * [applyreg 普罗米模型数据]
     * @return [type] [description]
     */
    public function promeData($data)
    {   
        $data['from'] = Request::PROMEV4;//申请借款决策识别码
        //记录请求
        $req = new Request();
        $request = $req->addRequest($data);       
        if (!$request) {
            return $this->returnInfo(false, '请求记录失败');
        }
        $data['request_id'] = $request;
        //获取用户数据
        $user_data = $this->userApi->getUserData($data);
        if (empty($user_data)) {
            return $this->returnInfo(false, '用户数据异常');
        }
        $user_data = array_merge($user_data,$data);
        $loan_extend = $this->getLoanExtend($user_data);
        //请求analysis获取运营商数
        $anti_res = $this->analysis->getAntiInfo($data);
        if (!$anti_res) {
            return $this->returnInfo(false, '运营商数据异常');
        }
        // 获取用户历史天启数据
        $org_res = $this->getOriginData($user_data);
        $prome_data = array_merge($user_data,$anti_res,$loan_extend,$org_res);
        // 记录用户决策数据
        $func = $this->saveFunc;
        // $data = $this->readCsv($prome_data);
        $loan_res = $func->saveProme($prome_data);
        if (!$loan_res) {
            return $this->returnInfo(false, '保存用户信息失败');
        }
        return $this->returnInfo(true, $prome_data);
    }
    // 获取天启数据
    private function getOriginData($user_data)
    {
        $idcard = ArrayHelper::getValue($user_data,'identity','');
        $phone = ArrayHelper::getValue($user_data,'mobile','');
        $org_res = ['last_create_time_tq' => ''];
        if (!$idcard || !$phone)
            return $org_res;

        $data['idcard'] = $idcard;
        $data['phone'] = $phone;
        $org_res = $this->cloudAapi->getOrigin($data);
        return $org_res;
    }
    //请求决策系统并返回结果
    public function queryCrif($process_code,$res_data)
    {
        $func = $this->saveFunc;
        $request = ArrayHelper::getValue($res_data, 'request_id');
        $crif_res = $this->javaCrif->queryCrif($request,$res_data,$process_code);
        if (empty($crif_res)) {
            return $this->returnInfo(false, '决策异常');
        }
        //记录决策结果
        $save_res = $func->saveRes($res_data,$crif_res);
        if (!$save_res) {
            return $this->returnInfo(false, '结果记录异常');
        }
        $res_status = ArrayHelper::getValue($crif_res, 'RESULT');
        return $this->returnInfo(true, $res_status);
    }

    public function readCsv($prome_data)
    {
        $file = fopen('D:\phpstudy\WWW\peanut\strategy_b714\web\data(0-99).csv','r'); 
        $n = 0;
        while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
            if ($n === 0) {
                $key = $data;
            } else {
                $value[] = $data;
            }
            $n++;
        }
        $count = count($value);
        $l = 0;//用户
        $c = 0;//决策
        $s = 0;//结果
        foreach ($value as $k =>$val) {
            $test_data = [
                'mobile' => (string)$val[0],
                'indentity_risk_index' => (float)(sprintf('%.4f',$val[1])),
                'social_stability_index' => (float)(sprintf('%.4f',$val[2])),
                'consume_fund_index' => (float)(sprintf('%.4f',$val[3])),
                'score' => (float)(sprintf('%.4f',$val[4])),
                'source' => 1,
                'report_type' => 4,
            ];
            $prome_data = array_merge($prome_data,$test_data);
            $func = $this->saveFunc;
            $loan_res = $func->saveProme($prome_data);
            if (!$loan_res) {
                Logger::dayLog('111', '保存失败', $prome_data);
                $l++;
            }
            $request = ArrayHelper::getValue($prome_data, 'request_id');
            $process_code = JavaCrif::PROME_CODE;
            $crif_res = $this->javaCrif->queryCrif($request,$prome_data,$process_code);
// var_dump($crif_res);die;
            if (empty($crif_res)) {
                $c++;
                Logger::dayLog('222', '请求决策失败', $prome_data);
            }
            $crif_res['RESULT'] = $crif_res['SS_SCORE'];
            //记录决策结果
            $save_res = $func->saveRes($prome_data,$crif_res);
            var_dump($save_res);die;
            if (!$save_res) {
                $c++;
                Logger::dayLog('222', '请求决策失败', $prome_data);
            }
        }
        fclose($file);
        echo '共'.$l.'条用户失败';
        echo '共'.$c.'条决策失败';
        echo '共'.$s.'条结果失败';die;
        die;
    } 

    private function getLoanExtend($data)
    {
        $yyyApi = new YyyApi();
        $extend_select = 'loan_total,success_num';
        $loan_extend = $yyyApi->getLoanExtend($data,$extend_select);
        if (empty($loan_extend)) {
           $loan_extend['loan_total'] = '';
           $loan_extend['success_num'] = '';
        }
        return $loan_extend;
    }
}