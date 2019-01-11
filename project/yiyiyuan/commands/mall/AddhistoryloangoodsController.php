<?php

namespace app\commands\mall;

/**
 * 历史数据生成借款+商品订单
 * linux : sudo -u www /data/wwwroot/yiyiyuan/yii mall/addhistoryloangoods
 * windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii mall/addhistoryloangoods
 */
use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\news\Goods_list;
use app\models\news\Loan_goods;
use app\models\news\Loan_goods_pay;
use app\models\news\User_loan;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class AddhistoryloangoodsController extends BaseController {

    private $limit = 1000;

    public function actionAdd($start_date, $end_date) {
        $where = [
            'AND',
            ['>=', User_loan::tableName() . '.create_time', $start_date],
            ['<', User_loan::tableName() . '.create_time', $end_date],
            [Loan_goods::tableName() . '.loan_id' => null]
        ];
        $sql = User_loan::find()->joinWith('loangoods', 'TRUE', 'LEFT JOIN')->where($where);
        $total = $sql->count();
        $pages = ceil($total / $this->limit);

        $this->log("\n" . date('Y-m-d H:i:s') . "......................");
        $this->log("\n all:{$total},limit:{$this->limit},pages:{$pages}\n");

        for ($i = 0; $i < $pages; $i++) {
            $loanList = $sql->limit($this->limit)->all();
            if (!empty($loanList)) {
                $result = $this->addLoanGoods($loanList);
                $this->log("\n all:{$this->limit},SUCCESS:{$result},pages:{$i}\n");
            }
        }
    }

    public function addLoanGoods($loanList) {
        foreach ($loanList as $v) {
            if ($v->desc == '购买设备') {
                $cid = array(2);
            } else if ($v->desc == '购买家具或家电') {
                $cid = array(5);
            } else if ($v->desc == '消费') {
                $cid = array(1, 3, 4);
            } else {
                $cid = array(1, 3, 4);
            }
            $goodsModel = new Goods_list();
            $goodList = $goodsModel->getGoodsByLoanAmount($v->amount, $cid);
            $num = array_rand($goodList);
            $goodsInfo = $goodList[$num];
            $reduce_time = strtotime($v->create_time) - rand(300, 1800);
            $date = date('YmdHis', $reduce_time);
            $plus_time = strtotime($v->create_time) + rand(300, 1800);
            $create_time = date('Y-m-d H:i:s', $reduce_time);
            $last_modify_time = date('Y-m-d H:i:s', $plus_time);
            $condition = [
                'user_id' => $v->user_id,
                'loan_id' => $v->loan_id,
                'goods_id' => $goodsInfo['id'],
                'goods_order_no' => 'XHHFQSC' . $date,
                'goods_price' => $goodsInfo['goods_price'],
                'goods_name' => $goodsInfo['goods_name'],
                'goods_attribute_value' => $goodsInfo['attr']['value'],
                'loan_amount' => $v->amount,
                'loan_days' => $v->days,
                'loan_desc' => empty($v->desc) ? '消费' : $v->desc,
                'loan_create_time' => $v->create_time,
                'create_time' => $create_time,
                'last_modify_time' => $last_modify_time,
            ];
            $loanGoodsModel = new Loan_goods();
            $loanGoods = $loanGoodsModel->saveLoanGoodsInfo($condition);
            if (!$loanGoods) {
                Logger::dayLog('saveerror/loangoods', '商城借款添加失败：loan_id---' . $v->loan_id);
                continue;
            }
            $pay_bill = $reduce_time . rand(100000, 999999);
            $data[] = [
                'user_id' => $v->user_id,
                'loan_goods_id' => $loanGoodsModel->id,
                'loan_goods_no' => 'XG' . $pay_bill,
                'paybill' => $pay_bill,
                'loan_goods_amount' => $goodsInfo['goods_price'],
                'actual_money' => $goodsInfo['goods_price'],
                'buy_status' => 1,
                'buy_time' => date('Y-m-d H:i:s', strtotime('+' . rand(1000, 2000) . 'second')),
                'create_time' => date('Y-m-d H:i:s', strtotime('+' . rand(500, 1000) . 'second')),
                'last_modify_time' => date('Y-m-d H:i:s', strtotime('+' . rand(2000, 3000) . 'second')),
            ];
        }
        $res = 0;
        $loanGoodsPayModel = new Loan_goods_pay();
        if (!empty($data)) {
            $res = $loanGoodsPayModel->insertBatch($data);
        }
        return $res;
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}
