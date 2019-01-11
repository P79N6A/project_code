<?php
/**
 * 宝付出款异步回调接口
 */
namespace app\controllers;

use app\common\Logger;
use app\models\baofoo\BfRemit;
use app\modules\api\common\ApiController;
use app\modules\api\common\baofoo\BaofooApi;
use app\modules\api\common\baofoo\BaofooRemit;
use Yii;
use yii\helpers\ArrayHelper;

class BfbackController extends ApiController {

    /**
     * 宝付代付
     */
    private $oBfApi;

  

    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
    }

    public function actionIndex() {
        
    }
    /**
     * Undocumented function
     * 宝付异步回调接口
     * @param string $env
     * @return void
     */
    public function actionNotify($env) {
        Logger::dayLog('baofoo/notify',$env,$this->post());
        $params = Yii::$app->request->post();    
        // $params = array (
        //     'member_id' => '1177707',
        //     'terminal_id' => '35243',
        //     'data_type' => 'XML',
        //     'data_content' => '40aa7544784057de36e31f648fe152c7d84dda7e6cf632be6b5cd64863860823b0701ee3043656b4e332a6e73231980e26a5640402b63a91ee39011055fe9d439a4bf23668f2a4b70cc405f1f5892d633f192933959157892442d3f91b6c646cef8bfb7e7bf6efcb1a4f15a1d74dbdb7eb9175bdc8ae2e9b4c9d88497ab97107d12c0445afd1589cc1519b65b6045d6ef53b7d47cac568b17893969ae9f687cc076f510fb25d9bd23caa6939118bebf8dcd0ae664d18d4937f5fd06685e73d12945923754e7a23e8aead0828e4937b81a5cedfac13853a3e5b774542a04c68e614a2f0a43000fa9ff473b6b8a7191e36fc74c5c2bff767f021c473341afefc155b54934691a4f8791cd1f10c16683c790c242998cd75981c7ee452d2adecde88d93519c5cf03f65e41969593b48b0de39343d39deab23c456c3e1b37cef0cc2c00066f6b9b88ce085091b8fd5b2f4c8bde3144badb7df5f4ef8d52d6ecd598145579b90344b2c88d04587bd5d1e07a35a2c2a79a5adc50bbbb5dedbcadfa3f412c3b32969ef0abeebb1dc4347efef90ab89fd853c2292499953783e464fae7bbe10fc4dd6b55322af9558ba05120deec98ad3b44b2561a6ca955f4993ae51f6f6408a461df526639988060b0c2583d92418ab5239f3681999bddc8a04c3f6079765ea7d3d21e0f17e61a0c56dd3322c208d821506309fb927e8a5343ef3e201e751ef90b0060a66b68850a9efb3eb8506c9c238366688bdd33a71817953e15edae88a3c01ad32f61fedaa6b5f77fbe8b34e08819a1fa54250e2c6dc05a5395e87f3b307a1f363a095d0873a8cf9da30de6fba9d6f2308a9e6884b5d669a2d492f1f44c738c88b9f8bdff5795a37d3fb53b0811bdc7b29f76aa997c84ce49a37fbfdc4bece480c43c39ad2e1ac56524a551d57a3e6cc1ac9adf250ede07635d098b28fa40fe7abd96da54a9952038ed4a2bc04cc1b805520c08776940fd94d130088bbdded9867f5c32455b90e7927a5c6745e0a65d1eef51a745f5cd2ea6e3a0d3212122ef91f8a98e3ac469d80ff1fae4f45385aa4321956fc6cad971eaa3bda1cf2545e6c14e46e6a3292dc15c86f5f99c70c5bf8d6b85c9fb97e3ec35cbea879a2909c1a6b7f7c3fccb899303c472ee6e51b4dbcc3397574b01ec0c0fb28ee9841705264e90da88da4333a346ccd334ac60df48cb46296c5a5374e0d64dd861980b3e7b71bc3e55021fc3803514106b1b6fcf05899d77eb2da0b53806ce4668c936a81111bad2d96a9edd141b1964590de2fc3e12cf1b32d91230e2f470156e6e3f8184e011eee867ab52af070ad6b269d252e6edd0c91937cc6a2800fd0f28e3241cd0554dfcee62e1862b32abaef9c734b432c9e178255d0449b73468a763c588a1dda56e2bdcae829b025bfc1bea3c3cb283a2777522673d071cc4f24c',
        // ); 
        if(empty($params['data_content'])) return false;   
        $this->oBfApi = new BaofooApi($env);
        $result = $this->oBfApi->bfNotify($params['data_content']);
        Logger::dayLog('baofoo/notify',$env,$result);
        if(empty($result)) return false;
        $reqData       = ArrayHelper::getValue($result,'trans_reqDatas.trans_reqData','');
        //$reqData = isset($result['trans_reqDatas']['trans_reqData'])?$result['trans_reqDatas']['trans_reqData']:"";
        if(empty($reqData)) return false;
        $trans_no       = ArrayHelper::getValue($reqData,'trans_no','');
        $trans_orderid  = ArrayHelper::getValue($reqData,'trans_orderid','');
        $trans_money    = ArrayHelper::getValue($reqData,'trans_money','0');
        $trans_remark   = ArrayHelper::getValue($reqData,'trans_remark','');
        $state          = ArrayHelper::getValue($reqData,'state','');
        if(empty($trans_no)){
            Logger::dayLog('baofoo/notify',$result,$trans_no,'商户订单号不存在');
            return false;
        }
        $orderInfo = (new BfRemit)->getByClientId($trans_no);
        if(empty($orderInfo)){
            Logger::dayLog('baofoo/notify',$result,$trans_no,'查询不到订单');
            return false;
        }
        if($orderInfo->settle_amount!=$trans_money){
            Logger::dayLog('baofoo/notify',$result,'交易金额',$trans_money,$orderInfo->attributes,'订单金额与交易金额不一致');
            return false;
        }
        //判断是否是终态
        if(in_array($orderInfo->remit_status,[BfRemit::STATUS_SUCCESS,BfRemit::STATUS_FAILURE])){
            echo 'OK'; die;
        }
        switch ($state) {
            case 1:
                $remit_status = BfRemit::STATUS_SUCCESS;
                break;
            case -1:
            case 2:
                $remit_status = BfRemit::STATUS_FAILURE;
                break;
            default:
                $remit_status = BfRemit::STATUS_DOING;
                break;
            }
        if(!in_array($remit_status,[BfRemit::STATUS_SUCCESS,BfRemit::STATUS_FAILURE])){
            Logger::dayLog('baofoo/notify',$result, $remit_status,'异步回调状态不是终态');
            return false;
        }
        // 保存查询表中
        $res = $orderInfo->saveRspStatus($remit_status,$state,$trans_remark,'',$trans_orderid, 3);
        if (!$res) {
            Logger::dayLog('baofoo/notify', 'BfRemit/saveRspStatus',$result, $orderInfo->id,'更新状态失败');
            return false;
        }
        // 发送通知
        $notifyRes = (new BaofooRemit)->InputNotify($orderInfo);
        if(!$notifyRes){
            Logger::dayLog('baofoo/notify', 'BaofooRemit/InputNotify',$result, $orderInfo->attributes,$notifyRes,'通知失败');
            return false;
        }
       echo 'OK';die;
       
    }
}
