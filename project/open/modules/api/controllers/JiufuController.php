<?php
/**
 * 玖富接口
 * 内部错误码范围 16000-17000
 * @author lijin
 */
namespace app\modules\api\controllers;
use app\common\Logger;
use app\models\jiufu\JFOss;
use app\models\jiufu\JFRemit;
use app\modules\api\common\ApiController;
use app\modules\api\common\jiufu\JFApi;
use yii\helpers\ArrayHelper;

class JiufuController extends ApiController {
    /**
     * 服务id号
     */
    protected $server_id = 16;
    /**
     * 玖富接口
     * @var obj
     */
    private $oJFApi;

    public function init() {
        parent::init();

        $env = SYSTEM_PROD ? 'prod' : 'dev';
        $this->oJFApi = new JFApi($env);
    }

    // 出款接口
    public function actionRemit() {
        //1 字段检测, 重组参数
        $req_data = &$this->reqData;
        $req_data['guest_account_bank_branch'] = '中关村支行';
        $req_data['guest_account_province'] = '北京';
        $req_data['guest_account_city'] = '北京';
        $req_data['county_id'] = '1101';
        if (!isset($req_data['name'])) {
            return $this->resp(160001, "姓名不能为空");
        }
        if (!isset($req_data['idcard'])) {
            return $this->resp(160002, "身份证不能为空");
        }
        if (!isset($req_data['phone'])) {
            return $this->resp(160003, "手机号不能为空");
        }
        if (!isset($req_data['cardno'])) {
            return $this->resp(160004, "卡号不能为空");
        }
        if (!isset($req_data['req_id'])) {
            return $this->resp(160005, "请求req_id不能为空,且必须唯一");
        }
        if (!isset($req_data['img_url'])) {
            return $this->resp(160006, "img_url不能为空");
        }
        if (!isset($req_data['product_id'])) {
            return $this->resp(160007, "product_id 必须设置");
        }
        if (!in_array($req_data['product_id'], ['215', '253'])) {
            return $this->resp(160008, "product_id 设置不合法");
        }

        //2 查询request_id是否重复
        $oRemit = new JFRemit;
        $res = $oRemit->getByReqId($req_data['req_id']);
        if ($res) {
            return $this->resp(160009, "req_id:{$req_data['req_id']}已经存在了!");
        }

        //3 参数转换与验证
        $oss_data = $this->getOssData($req_data);
        $loan_data = $this->getLoanData($req_data);
        if (!$loan_data['bank_code']) {
            return $this->resp(160010, $loan_data['guest_account_bank'] . "不支持此卡");
        }
        if (!$loan_data['city_code']) {
            return $this->resp(160011, $loan_data['guest_account_city'] . "不支持该市/县");
        }

        //4 保存数据
        $result = $oRemit->saveData($loan_data);
        if (!$result) {
            Logger::dayLog("jiufu", 'remit', "oRemit::saveData", $loan_data, "错误原因", $oRemit->errors);
            return $this->resp(160021, "数据保存失败");
        }

        //4 获取图片信息
        $fileM = $this->getFileInfo($oss_data);
        if (!$fileM) {
            return $this->resp(160022, $this->errinfo ? $this->errinfo : '上传oss失败');
        }

        //5 调用出款接口
        $response = $this->oJFApi->recordLoan($oRemit, $fileM['img_name'], $fileM['file_id']);
        if ($response['res_code'] !== 0) {
            $oRemit->remit_status = JFRemit::STATUS_FAILURE;
            $result = $oRemit->saveRspStatus($response['res_code'], $response['res_data'], '', '');
            if ($response['res_code'] == '000021') {
                $res_code = 160013;//黑名单错误码
            } else {
                $res_code = 160023;//接口失败通用错误码
            }
            return $this->resp($res_code, "提交出款订单失败:" . $response['res_data']);
        }

        //6 解析响应结果
        //$res = $this->getTestRemit(); //@todo
        $order_id = ArrayHelper::getValue($response, 'res_data.appId', '');
        $order_status = ArrayHelper::getValue($response, 'res_data.appStatus', '');
        $res = $oRemit->saveRspStatus($response['res_code'], 'SUBMITOK', $order_status, $order_id);

        //7 成功时
        $response = [
            'req_id' => $oRemit['req_id'],
            'client_id' => $oRemit['client_id'],
            'order_id' => $oRemit['order_id'],
            'settle_amount' => $oRemit['settle_amount'],
            'remit_status' => $oRemit['remit_status'],
            'rsp_status' => $oRemit['rsp_status'],
            'rsp_status_text' => $oRemit['rsp_status_text'],
        ];
        return $this->resp(0, $response);
    }
    /**
     * 获取附件信息, 没有则添加
     * @param  [] $data
     * @return obj
     */
    private function getFileInfo($data) {
        //1 查询本地数据库是否存在
        $oss = new JFOss;
        $model = $oss->getByImgUrl($data['img_url']);
        if ($model) {
            return $model;
        }

        //2 调用oss接口
        $data['client_id'] = $oss->getClientId($data['aid'], $data['req_id']);
        $response = $this->oJFApi->oss($data['client_id'], $data['img_name'], $data['img_url']);
        if ($response['res_code'] !== 0) {
            return $this->returnError(false, "提交oss图片失败:" . $response['res_data']);
        }
        $data['file_id'] = $response['res_data'];

        //3 保存到数据库中
        $oss = new JFOss;
        $result = $oss->saveData($data);
        return $result ? $oss : null;
    }
    /**
     * 获取oss请求数据
     * @param  [] $req_data 参数类型
     * @return [] 重组参数
     */
    private function getOssData(&$req_data) {
        $img_name = basename($req_data['img_url']);
        return [
            'aid' => $this->appData['id'],
            'req_id' => $req_data['req_id'],
            'img_url' => $req_data['img_url'],
            'img_name' => $img_name,
        ];
    }
    /**
     * 获取请求参数 160001-100
     * @param  [] $req_data 参数类型
     * @return [] 重组参数
     */
    private function getLoanData(&$req_data) {
        $aid = $this->appData['id'];
        $tip = $this->extFields($req_data);
        $tip = json_encode($tip, JSON_UNESCAPED_UNICODE);

        $oRemit = new JFRemit;
        $client_id = $oRemit->getClientId($aid, $req_data['req_id']);
        $customer_sex = $oRemit->getCustomerSex($req_data['customer_sex']);
        $bank_code = $oRemit->getBankCode($req_data['guest_account_bank']);
        $city_code = $oRemit->getCityCode($req_data['county_id'], $req_data['city_id'], $req_data['province_id']);
        $purpose = $oRemit->getPurpose($req_data['loan_purpose']);
        return [
            'req_id' => $req_data['req_id'],
            'client_id' => $client_id,
            'aid' => $aid,
            'product_id' => $req_data['product_id'],
            'settle_amount' => $req_data['settle_amount'],
            'identityid' => $req_data['idcard'],
            'user_mobile' => $req_data['phone'],
            'guest_account_name' => $req_data['name'],
            'guest_account_bank' => $req_data['guest_account_bank'],
            'guest_account' => $req_data['cardno'],
            'guest_account_province' => $req_data['guest_account_province'],
            'guest_account_city' => $req_data['guest_account_city'],
            'guest_account_bank_branch' => $req_data['guest_account_bank_branch'],
            'customer_sex' => $customer_sex,
            'bank_code' => $bank_code,
            'city_code' => $city_code,
            'time_limit' => $req_data['time_limit'],
            'loan_purpose' => $purpose,
            'tip' => $tip,
            'callbackurl' => $req_data['callbackurl'],
        ];
    }
    /**
     * 扩展字段
     * @param  [] $req_data
     * @return []
     */
    private function extFields($req_data) {
        return [
            'email' => $req_data['email'],
            'img_url' => $req_data['img_url'],
            'img_name' => $req_data['img_name'],

            // 学校信息
            //'schoolName' => $req_data['schoolName'], // 学校名称
            //'schoolYear' => $req_data['schoolYear'], //入学年份

            // 公司信息
            'company' => $req_data['company'], // String  工作单位
            'companyPhone' => $req_data['companyPhone'], // String  单位电话
            'companyAdressprovince' => $req_data['companyAdressprovince'], // String  单位所在省
            'companyAdressCity' => $req_data['companyAdressCity'], // String  单位所在市
            'companyAdressDist' => $req_data['companyAdressDist'], // String  单位所在区
            'companyAdressRoad' => $req_data['companyAdressRoad'], // String  详细地址
            'companyType' => isset($req_data['companyType']) ? $req_data['companyType'] : 'B0908', // String  单位性质
            'beginCompanyDate' => isset($req_data['beginCompanyDate']) ? $req_data['beginCompanyDate'] : '', // String

            // 住宅信息
            'liveaddressProvince' => $req_data['liveaddressProvince'],
            'liveaddressCity' => $req_data['liveaddressCity'],
            'liveaddressDistinct' => $req_data['liveaddressDistinct'],
            'liveaddressRoad' => $req_data['liveaddressRoad'],

            // 联系人信息
            'contactName' => $req_data['contactName'],
            'contactRelation' => 'F1099', // 其它,暂写死
            'contactPhone' => $req_data['contactPhone'],

            // extendmap
            'contractCode' => $req_data['contractCode'],
            'phonePassword' => $req_data['phonePassword'],
        ];
    }
    private function getTestRemit() {
        return array('res_code' => 0,
            'res_data' => array(
                'appId' => '20204236',
                'appStatus' => 'F0235',
                'appayAmt' => '200',
                'appayDate' => '2016-08-31',
                'approveSuggestAmt' => '200',
                'beginCompanyDate' => '未知',
                'certId' => '130929199312011502',
                'certType' => 'B1301',
                'creator' => 'system',
                'customerName' => '李瑾',
                'customerProperty' => 'F2501',
                'customerSex' => 'N0201',
                'degree' => 'B0305',
                'duty' => 'B2910',
                'email' => '271846375@qq.com',
                'extendMap' => array('entry' => array(0 => array('key' => 'riskGrade', 'value' => 'A'), 1 => array('key' => 'phonePassword', 'value' => '123456'), 2 => array('key' => 'career', 'value' => 'F12324'), 3 => array('key' => 'score', 'value' => '80'), 4 => array('key' => 'fieldName9', 'value' => 'A'), 5 => array('key' => 'fieldName8', 'value' => '80'), 6 => array('key' => 'contractCode', 'value' => '1472624233'))),
                'initStats' => '1',
                'instCode' => '110841',
                'interestRule' => 'B2006',
                'intustry' => 'B1016',
                'isCalTotalAmount' => '1',
                'isCard' => '1',
                'isOpenCard' => '1',
                'isPayPlan' => '1',
                'isRepayMent' => '2',
                'isSignContact' => '2',
                'isSupportDeduction' => '2',
                'liveaddressCity' => '唐山',
                'liveaddressDistinct' => '河北唐山迁西',
                'liveaddressProvince' => '河北',
                'liveaddressRoad' => '河北唐山北京',
                'loanPurpose' => 'F1199',
                'loanTarget' => '1',
                'loanTerm' => '7',
                'marry' => 'B0506', 'orgCode' => 'JFB',
                'phone' => '13581524052',
                'productId' => '215',
                'productName' => '先花花',
                'receiveBankCard' => '6222020200016666410',
                'receiveBranch' => '中关村支行',
                'receiveCountry' => '北京',
                'receiveCountryCode' => '1000',
                'receiveName' => '李瑾',
                'receiveOpen' => '0102',
                'receiveProvince' => '北京',
                'recordLoanAttach' => array('attachName' => '201601110350612.jpeg',
                    'fileId' => '634cfd87-4a6d-4ccd-9a6c-b5376f0085a0'),
                'recordLoanContact' => array('contactName' => '小张', 'contactPhone' => '13581524055', 'contactRelation' => 'F1099'), 'repayBankCard' => '6222020200016666410', 'repayBranch' => '中关村支行', 'repayName' => '李瑾', 'repayOpen' => '0102', 'repaymentInitiator' => '2', 'saleChannel' => '1037', 'timeLimit' => '7'));
    }
}
