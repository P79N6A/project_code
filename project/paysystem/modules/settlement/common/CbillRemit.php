<?php
/**
 * 清结算
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/17
 * Time: 14:48
 */
namespace app\modules\settlement\common;

use app\models\open\BfRemit;
use app\models\open\CjRemit;
use app\models\open\JfRemit;
use app\models\open\Rbremit;
use app\models\open\SinaRemit;
use app\models\wsm\WsmRemit;
use app\models\xn\XnRemit;

class CbillRemit
{
    /**
     * 实例化对账类
     * @param $channel_id
     * @return BfRemit|CjRemit|JfRemit|Rbremit|WsmRemit|bool|string
     */
    public function createObject($channel_id)
    {
        if (empty($channel_id)){
            return false;
        }
        switch ($channel_id){
            case 1://1:融宝
                $oRemit = new Rbremit();
                break;
            case 2://2:宝付
                $oRemit = new BfRemit();
                break;
            case 3://3:畅捷
                $oRemit = new CjRemit();
                break;
            case 4://4:玖富
                $oRemit = new JfRemit();
                break;
            case 5:////5:微神马
                $oRemit =  new WsmRemit();
                break;
            case 6://6:新浪
                $oRemit = new SinaRemit();
                break;
            case 7://7:小诺理财
                $oRemit = new XnRemit();
                break;
            default:
                $oRemit = '';
                break;
        }
        return $oRemit;
    }

    /**
     * 获取支付通道出款状态
     * @param $status_int
     * @return int 0失败 2成功
     */
    public function payPassagewayStatus($status_int)
    {
        if ($status_int == 6){
            return 2;
        }
        return 0;
    }

    /**
     * 获取一亿元通道出款状
     * @param $status_str
     * @return int
     */
    public function yiPassagewayStatus($status_str)
    {
        if (strtolower($status_str) == 'success' ){
            return 4;
        }
        return 0;
    }
}