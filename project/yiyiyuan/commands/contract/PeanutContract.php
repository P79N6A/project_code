<?php

namespace app\commands\contract;

use app\commonapi\ApiSign;
use app\commonapi\Common;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\news\User_loan;

class PeanutContract {

    public function make($loan_id, $temp, $pdfpath) {
        $data = [
            [
                'loan_id' => $loan_id,
            ],
        ];
        $result = $this->api($data);
        if (!$result) {
            $contributorarr = [];
            Logger::errorLog($loan_id . "生成失败" . "\n", 'contract', 'crontab');
        } else {
            $contributorarr = !empty($result['resData']) ? $result['resData'][$loan_id] : [];
        }
        if (empty($contributorarr)) {
            $contributorarr[] = $this->getCont();
        }
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

    /**
     * 债匹接口
     * @param $data
     * @return mixed
     */
    private function api($data) {
        $signData = (new ApiSign)->signData($data);
        $signData['_sign'] = base64_encode($signData['_sign']);
        if (SYSTEM_ENV == 'prod') {
            $url = "http://10.139.35.146:8085/api/match/queryinvest";
        } elseif (SYSTEM_ENV == 'dev') {
            $url = "http://182.92.80.211:8009/api/match/queryinvest";
        } else {
            $url = "http://182.92.80.211:8009/api/match/queryinvest";
        }
        $result = Http::interface_post($url, $signData);
        Logger::errorLog(print_r($result, true), 'contract', 'crontab');
        if (!$result) {
            return false;
        }
        $investdata = json_decode($result, true);
        if (!$investdata['data']) {
            return false;
        }

        return json_decode($investdata['data'], true);
    }

    /**
     * 获取随机投资人
     * @return mixed
     */
    private function getCont() {
        $cont = array(
            array('realname' => '谷鹏', 'identity' => '152122199202013619', 'mobile' => '18612749102'),
            array('realname' => '朱丽雅', 'identity' => '370683198804147926', 'mobile' => '13810414909'),
            array('realname' => '孙清', 'identity' => '372330199410303313', 'mobile' => '13701065634'),
            array('realname' => '李东林', 'identity' => '13062619940201121X', 'mobile' => '13701104965'),
            array('realname' => '梅双双', 'identity' => '110111199001044066', 'mobile' => '13439461882'),
            array('realname' => '扈晓峰', 'identity' => '110102198902082712', 'mobile' => '15010583802'),
            array('realname' => '付志鹏', 'identity' => '210902199101115010', 'mobile' => '18641819677'),
            array('realname' => '李江', 'identity' => '140624199509135019', 'mobile' => '13681256072'),
            array('realname' => '秦广学', 'identity' => '211322199011220777', 'mobile' => '13311588428'),
            array('realname' => '史钰琳', 'identity' => '231084199110173714', 'mobile' => '15201130751'),
            array('realname' => '张龙', 'identity' => '230281198804133537', 'mobile' => '13159121092'),
            array('realname' => '赵帅', 'identity' => '411381199207014550', 'mobile' => '18210405181'),
            array('realname' => '任锡垚', 'identity' => '142402199507160934', 'mobile' => '13203547570'),
            array('realname' => '谷春雷', 'identity' => '130423198902010710', 'mobile' => '13522657853'),
            array('realname' => '尹家繁', 'identity' => '130429199508097312', 'mobile' => '13263134899'),
            array('realname' => '杨志成', 'identity' => '131024199111190719', 'mobile' => '18910574815'),
            array('realname' => '王蕙', 'identity' => '110228199409044628', 'mobile' => '15210169306'),
            array('realname' => '焦志远', 'identity' => '110108199808287116', 'mobile' => '13681540895'),
            array('realname' => '李婷婷', 'identity' => '622621199003110022', 'mobile' => '18210220010'),
            array('realname' => '李彦芳', 'identity' => '622623199304011848', 'mobile' => '15710083098'),
            array('realname' => '张晓莹', 'identity' => '110108198608270016', 'mobile' => '15110218426'),
            array('realname' => '匡志娟', 'identity' => '371121198909240785', 'mobile' => '18101078924'),
            array('realname' => '刘冬杰', 'identity' => '41282319921118512X', 'mobile' => '13598916143'),
            array('realname' => '刘佳倩', 'identity' => '130627199511161221', 'mobile' => '15132230094'),
            array('realname' => '李书光', 'identity' => '410222198910251012', 'mobile' => '18860393609'),
            array('realname' => '荣珍珍', 'identity' => '342221198906170567', 'mobile' => '15122051731'),
            array('realname' => '张家楠', 'identity' => '230121199505030821', 'mobile' => '18811357270'),
            array('realname' => '马文臣', 'identity' => '130221199505166317', 'mobile' => '15233332367'),
            array('realname' => '李晓飞', 'identity' => '37148119910208271X', 'mobile' => '13801202581'),
            array('realname' => '侯丹丹', 'identity' => '140881199211100049', 'mobile' => '18612818481'),
            array('realname' => '王翔', 'identity' => '110108199104061434', 'mobile' => '13621369146'),
            array('realname' => '郑灿灿', 'identity' => '412827199412107641', 'mobile' => '18336305236'),
            array('realname' => '岳昌银', 'identity' => '411524198610144717', 'mobile' => '18610945029'),
            array('realname' => '孙沛', 'identity' => '130981199009116059', 'mobile' => '13521014832'),
            array('realname' => '李闻天', 'identity' => '130981199002236613', 'mobile' => '17710366103'),
            array('realname' => '郭兰喆', 'identity' => '130433199404150013', 'mobile' => '17703305444'),
            array('realname' => '韩阳', 'identity' => '110229199501091319', 'mobile' => '15120043546'),
            array('realname' => '程晓刚', 'identity' => '370687199601140070', 'mobile' => '17310619775'),
            array('realname' => '武梅', 'identity' => '130723199203023222', 'mobile' => '18310197924'),
            array('realname' => '张佩佩', 'identity' => '372928198905056867', 'mobile' => '18510707472'),
            array('realname' => '崔玉潭', 'identity' => '210882198912082410', 'mobile' => '15911142767'),
            array('realname' => '陈建婷', 'identity' => '622323199002287228', 'mobile' => '13671353319'),
            array('realname' => '岳中秋', 'identity' => '142226199308151234', 'mobile' => '18500741190'),
            array('realname' => '许晓敏', 'identity' => '130722199210055744', 'mobile' => '13552540955'),
            array('realname' => '李耀宗', 'identity' => '370685199404130612', 'mobile' => '18612054555'),
            array('realname' => '潘庚', 'identity' => '13082219900228453X', 'mobile' => '13121362359'),
            array('realname' => '常冲冲', 'identity' => '130423199310100717', 'mobile' => '15131059254'),
            array('realname' => '何美英', 'identity' => '142226199307263146', 'mobile' => '15910467702'),
            array('realname' => '谷路路', 'identity' => '130423198302050719', 'mobile' => '15100087521'),
            array('realname' => '高建', 'identity' => '130406199002031856', 'mobile' => '15311927191'),
        );
        $k = array_rand($cont, 1);
        return $cont[$k];
    }

}
