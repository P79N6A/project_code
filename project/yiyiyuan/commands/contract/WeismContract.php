<?php

namespace app\commands\contract;

use app\commonapi\ApiSign;
use app\commonapi\Common;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\news\User_loan;

class WeismContract {

    public function make($loan_id, $temp, $pdfpath) {
        $data = [
            [
                'loan_id' => $loan_id,
            ],
        ];
        $contributorarr = !empty($loan_id) ? $loan_id : [];
        $loanInfo = User_loan::findOne($loan_id);
        $endamount = $loanInfo->getRepaymentAmount($loanInfo);
        $loan_amount = number_format($loanInfo->current_amount, 2, '.', '');
        $daxie_loan_amount = Common::get_amount($loan_amount);
        $daxie_endamount = Common::get_amount($endamount);
        $daxie_loan_amount_num = Common::get_amount_num($loan_amount);
        $daxie_endamount_num = Common::get_amount_num($endamount);
        $huankuandate = date('Y-m-d', (strtotime($loanInfo->withdraw_time) + $loanInfo->days * 24 * 3600));

        $data = [
            'loanInfo' => $loanInfo,
            'daxie_loan_amount' => $daxie_loan_amount,
            'daxie_endamount' => $daxie_endamount,
            'daxie_loan_amount_num' => $daxie_loan_amount_num,
            'daxie_endamount_num' => $daxie_endamount_num,
            'endamount' => $endamount,
            'huankuandate' => $huankuandate,
            'contributorarr' => $contributorarr,
        ];
        $pdfRender = new PdfRender();
        $result = $pdfRender->pdfRender($temp, $pdfpath, $data);

        return $result;
    }
}
