<?php

namespace app\modules\borrow\controllers;

use app\commonapi\ImageHandler;
use app\commonapi\Logger;
use app\models\news\Areas;
use app\models\news\Coupon_list;
use app\models\news\Goods_attribute_value;
use app\models\news\Goods_list;
use app\models\news\Goods_pic;
use app\models\news\Loan_pic;
use app\models\news\User;
use app\models\news\User_loan;
use yii\web\Response;

if (!class_exists('PHPExcel_IOFactory')) {
    include '../phpexcel_new/PHPExcel/IOFactory.php';
}

class LoanpicController extends BorrowController {

    public function behaviors() {
        return [];
    }

    public function actionPagepic() {
        $this->layout = 'buypic/main';
        $loan_id = $this->get('loan_id', 0);
        $page = $this->get('page', 1);
        if (empty($loan_id)) {
            exit;
        }
        if($loan_id<150000){
            $loan = \app\models\news\User_loan_cg::findOne($loan_id);
        }else{
            $loan = User_loan::findOne($loan_id);
        }
        $user = $loan->user;
        $loanPic = (new Loan_pic())->getByLoanId($loan_id);
        if ($loanPic->template_id == 1) {
            return $this->template_one($user, $loanPic, $loan);
        } else if ($loanPic->template_id == 2) {
            return $this->template_two($user, $loanPic, $loan);
        } else {
            return $this->template_three($user, $loanPic, $loan);
        }
    }

    private function template_one($user, $loanPic, $loan) {
        $userextend = $user->extend;
        $areaModel = new Areas();
        $address = '';
        if (!empty($userextend) && !empty($userextend->home_area)) {
            $pro = $areaModel->getProCityArea($userextend->home_area);
            sort($pro);
            foreach ($pro as $key => $val) {
                $address .= $areaModel->getName($val) . ' ';
            }
            $address .= $userextend->home_address;
        } else if (!empty($userextend) && !empty($userextend->company_area)) {
            $pro = $areaModel->getProCityArea($userextend->company_area);
            sort($pro);
            foreach ($pro as $key => $val) {
                $address .= $areaModel->getName($val) . ' ';
            }
            $address .= $userextend->company_address;
        } else {
            $identity = substr($user->identity, 0, 6);
            $identity_address = \app\models\news\Iden_address::find()->where(['code' => $identity])->one();
            $pass = $user->password;
            $address = $identity_address->address . ' ' . $pass->iden_address;
        }
        $oGoodModel = new \app\models\news\Goods_shop();
        $goods = $oGoodModel->getByPrice($loan->amount);
        if (empty($loanPic->order_number)) {
            $order_number = rand(22, 23) . rand(1, 9) . rand(10000, 99999) . rand(10000, 99999) . rand(10000, 99999);
            $trade_date = date('Y-m-d', strtotime($loanPic->start_date) + 86400 * rand(1, 3));
            $trade_number = date('Ymd', strtotime($trade_date)) . rand(21, 22) . '001' . rand(10000, 99999) . rand(10000, 99999) . rand(10000, 99999);
            $h = rand(6, 23);
            $hour = $h < 10 ? '0' . $h : $h;
            $m = rand(0, 59);
            $miniute = $m < 10 ? '0' . $m : $m;
            $s = rand(0, 59);
            $second = $s < 10 ? '0' . $s : $s;
            $order_time = $trade_date . ' ' . $hour . ':' . $miniute . ':' . $second;
            $pay_time = date("Y-m-d H:i:s", strtotime($order_time) + rand(1, 5 * 3600));
            $loanPic->order_number = $order_number;
            $loanPic->trade_number = $trade_number;
            $loanPic->order_time = $order_time;
            $loanPic->pay_time = $pay_time;
            $loanPic->save();
        }
        return $this->render('pagepic', [
                    'user' => $user,
                    'address' => $address,
                    'loanPic' => $loanPic,
                    'goods' => $goods,
        ]);
    }

    private function template_two($user, $loanPic, $loan) {
        $this->layout = 'buypic/moban2';
        $userextend = $user->extend;
        $areaModel = new Areas();
        $address = '';
        if (!empty($userextend) && !empty($userextend->home_area)) {
            $pro = $areaModel->getProCityArea($userextend->home_area);
            sort($pro);
            foreach ($pro as $key => $val) {
                $address .= $areaModel->getName($val) . ' ';
            }
            $address .= $userextend->home_address;
        } else if (!empty($userextend) && !empty($userextend->company_area)) {
            $pro = $areaModel->getProCityArea($userextend->company_area);
            sort($pro);
            foreach ($pro as $key => $val) {
                $address .= $areaModel->getName($val) . ' ';
            }
            $address .= $userextend->company_address;
        } else {
            $identity = substr($user->identity, 0, 6);
            $identity_address = \app\models\news\Iden_address::find()->where(['code' => $identity])->one();
            $pass = $user->password;
            Logger::dayLog('pic', $pass, $user->user_id);
            $address = $identity_address->address . $pass->iden_address;
        }
        $oGoodModel = new \app\models\news\Goods_shop();
        $goods = $oGoodModel->getByPrice($loan->amount, 2);
        if (empty($loanPic->order_number)) {
            $order_number = rand(1, 9) . rand(10000, 99999) . rand(10000, 99999);
            $trade_date = date('Y-m-d', strtotime($loanPic->start_date) + 86400 * rand(1, 3));
            $trade_number = date('Ymd', strtotime($trade_date)) . rand(21, 22) . '001' . rand(10000, 99999) . rand(10000, 99999) . rand(10000, 99999);
            $h = rand(6, 23);
            $hour = $h < 10 ? '0' . $h : $h;
            $m = rand(0, 59);
            $miniute = $m < 10 ? '0' . $m : $m;
            $s = rand(0, 59);
            $second = $s < 10 ? '0' . $s : $s;
            $order_time = $trade_date . ' ' . $hour . ':' . $miniute . ':' . $second;
            $pay_time = date("Y-m-d H:i:s", strtotime($order_time) + rand(1, 5 * 3600));
            $loanPic->order_number = $order_number;
            $loanPic->trade_number = $trade_number;
            $loanPic->order_time = $order_time;
            $loanPic->pay_time = $pay_time;
            $loanPic->save();
        }
        $cityname = substr($address, strpos($address, '省') + 3, strpos($address, '市') - strpos($address, '省') - 3);
        return $this->render('pagepic2_1', [
                    'user' => $user,
                    'address' => $this->trimall($address),
                    'loanPic' => $loanPic,
                    'cityname' => $cityname,
                    'goods' => $goods,
        ]);
    }

    private function trimall($str) {
        $qian = array(" ", "　", "\t", "\n", "\r");
        return str_replace($qian, '', $str);
    }

    public function actionTest() {
        $goods_list = Goods_list::find()->all();
        foreach ($goods_list as $k => $v) {
            if (empty($v->attr->value)) {
                continue;
            }
            $goods[$k]['title'] = $v->goods_name;
            $goods[$k]['tag'] = $v->attr->value;
            $goods[$k]['pic'] = $v->pic->pic_url;
            $goods[$k]['price'] = $v->goods_price;
            $goods[$k]['shop'] = '猫王京东自营旗舰店';
            $goods[$k]['type'] = 2;
            $goods[$k]['create_time'] = $v->create_time;
            $goods[$k]['last_modify_time'] = $v->last_modify_time;
        }
        $head = [
            'title' => 'goods_name',
            'tag' => 'tag',
            'pic' => 'pic',
            'price' => 'price',
            'shop' => 'shop',
            'type' => 'type',
            'create_time' => 'create_time',
            'last_modify_time' => 'modify_time',
        ];
        $this->logCsv($head, $goods, $file_name = date('YmdHis') . '.csv');
    }

    /**
     * csv导出
     * @param array $head
     * @param $obj
     * @param string $file_name
     * @return bool
     * @author 王新龙
     * @date 2018/7/17 15:53
     */
    private function logCsv(array $head, $obj, $file_name = 'test.csv') {
        if (empty($obj)) {
            return false;
        }
        set_time_limit(0);
        $file_name = iconv('utf-8', 'GB18030', $file_name);
        ;
        header('Content-Type:application/vnd.ms-excel;charset=utf-8');
        header("Content-Disposition:filename=" . $file_name);
        header('Cache-Control: max-age=0');

        $fp = fopen('php://output', 'a');

        if (!empty($head)) {
            foreach ($head as $i => $v) {
                $head[$i] = iconv('utf-8', 'GB18030', $v);
            }
            fputcsv($fp, $head);
        }

        $sql_count = count($obj);
        $sql_limit = 500;
        $limit = 100000;
        $cnt = 0;
        for ($i = 0; $i < ceil($sql_count / $sql_limit); $i++) {
            foreach ($obj as $a) {
                $cnt++;
                if ($limit == $cnt) {
                    ob_flush();
                    flush();
                    $cnt = 0;
                }
                $data = $this->csvArray($head, $a);
                fputcsv($fp, $data);
            }
        }
        fclose($fp);
        echo '生成完成';
        exit();
    }

    /**
     * 导出数组预处理
     * @param $head
     * @param $data
     * @return array
     * @author 王新龙
     * @date 2018/7/17 17:33
     */
    private function csvArray($head, $data) {
        if (empty($head)) {
            return $data;
        }
        $list = [];
        foreach ($head as $key => $val) {
            if (!isset($data[$key])) {
                $list[] = '';
                continue;
            }
            $item = (string) $data[$key];
            switch ($key) {
                case 'status':
                    $item = $data[$key] == 'INIT' ? '待确认' : ($data[$key] == 'SUCCESS' ? '已确认' : '异常');
                    break;
            }
            $list[] = iconv('utf-8', 'GB18030', $item);
        }
        return $list;
    }

}
