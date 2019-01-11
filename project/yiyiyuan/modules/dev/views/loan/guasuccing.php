<?php
$status = array(
    '1' => '资料审核中',
    '2' => '等待担保人投资',
    '3' => '您的资料没有通过审核导致借款失败',
    '4' => '暂时没有人投资你',
    '5' => '待审核',
    '6' => '申请提现通过',
    '7' => '借款申请被驳回',
    '8' => '已还款',
    '9' => '待还款',
    '10' => '待审核',
    '11' => '还款确认中',
    '12' => '已逾期',
    '13' => '已逾期',
    '14' => '还款完成',
    '15' => '银行卡限制出款失败',
    '16' => '担保人拒绝投资',
    '17' => '您已经取消了借款',
);
$description = array(
    '1' => '待审核',
    '2' => '',
    '3' => '不要失望，重新填写资料再次发起借款吧~',
    '4' => '别灰心，快去试试其他借款方式吧~',
    '5' => '审核通过后会以短信形式进行通知',
    '6' => '静等收钱吧~',
    '7' => '不要失望，试试其他借款方式吧~',
    '8' => '好借好还 再借不难~',
    '9' => '记得要按时还款哦~',
    '10' => '审核通过后会以短信形式进行通知',
    '11' => '等待系统确认还款',
    '12' => '珍视信用 马上还款~',
    '13' => '珍视信用 马上还款~',
    '14' => '好借好还 再借不难~',
    '15' => '客服正在尝试与您联系，您也可以主动向客服反馈此问题',
    '16' => '不要失望，试试其他借款方式吧~',
    '17' => '',
);
?>
<div class="Hcontainer nP">
    <header class="header white">
        <p class="n26">状态：</p>
        <?php if ($loaninfo->status == 2 && $loaninfo->current_amount < $loaninfo->amount): ?>                
            <p class="n36 mb20 text-center"><?php echo $status[$loaninfo->status]; ?></p>
        <?php elseif ($loaninfo->status == 12 && time()<strtotime($loaninfo->end_date) && empty($loaninfo->chase_amount)): ?> 
            <p class="n36 mb20 text-center"><?php echo $status[9]; ?></p>
            <p class="n26 text-right"><?php echo $description[9]; ?></p>
        <?php elseif ($loaninfo->status == 17): ?>                
            <p class="n36 mb20 text-center"><?php echo $status[$loaninfo->status]; ?></p>
        <?php else: ?>
            <p class="n36 mb20 text-center"><?php echo $status[$loaninfo->status]; ?></p>
            <p class="n26 text-right"><?php echo $description[$loaninfo->status]; ?></p>
        <?php endif; ?>
    </header>
    <img src="/images/title.png" width="100%"/>
    <div class="con">
        <div class="details">
            <div class="adver <?php if ($loaninfo->status != 17 && $loaninfo->status != 15): ?>border_bottom_1<?php endif;?>">
                <div class="row mb30">
                    <div class="col-xs-4 cor n26">借款理由：</div>
                    <div class="col-xs-8 text-right n26"><?php echo $loaninfo->desc; ?></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-4 cor n26">借款金额：</div>
                    <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo sprintf('%.2f', $loaninfo->amount); ?></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-4 cor n26">借款期限：</div>
                    <div class="col-xs-8 text-right n26"><?php echo $loaninfo->days; ?>天</div>
                </div>
                <?php if ($loaninfo->status != 4 && $loaninfo->status != 15 && $loaninfo->status != 17): ?>
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">服务费：</div>
                        <div class="col-xs-8 text-right n26"><span class="red">&yen;
                                <?php echo sprintf('%.2f', $loaninfo->withdraw_fee + $loaninfo->interest_fee); ?>
                            </span></div>
                    </div>
                    <?php if ($loaninfo->status != 3): ?>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">担保人：</div>
                            <div class="col-xs-8 text-right n26"><?php echo $guater->guater->realname; ?></div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php if ($loaninfo->status != 4 && $loaninfo->status != 3 && $loaninfo->status != 15 && $loaninfo->status != 17): ?>
                <div class="adver <?php if ($loaninfo->status >= 8 && $loaninfo->status <= 13 && $loaninfo->status != 10): ?>border_bottom_1<?php endif; ?>">
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">提现卡：</div>
                        <div class="col-xs-8 text-right n26">
                            <?php
                            $card = $loaninfo->bank->card;
                            echo substr($card, 0, 4) . '***********' . substr($card, strlen($card) - 4, 4);
                            ?></div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($loaninfo->status == 9 || $loaninfo->status == 11): ?>
                <div class="adver">
                    <div class="row">
                        <div class="col-xs-5 cor n26">应还款日期：</div>
                        <div class="col-xs-7 text-right n26"><?php echo date('Y-m-d', strtotime('-1 day',  strtotime($loaninfo->end_date))); ?></div>
                    </div>
                </div>
                <div class="adver">
                    <div class="row">
                        <div class="col-xs-5 cor n26">应还款金额：</div>
                        <div class="col-xs-7 text-right n26"><span class="red n36 lh">&yen;<?php echo sprintf('%.2f', $loaninfo->amount + $loaninfo->withdraw_fee + $loaninfo->interest_fee); ?></span></div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($loaninfo->status == 8): ?>
                <div class="adver">
                    <div class="row">
                        <div class="col-xs-5 cor n26">还款日期：</div>
                        <div class="col-xs-7 text-right n26"><?php echo date('Y-m-d', strtotime($loaninfo->repay_time)); ?></div>
                    </div>
                </div>
                <div class="adver">
                    <div class="row">
                        <div class="col-xs-5 cor n26">还款金额：</div>
                        <div class="col-xs-7 text-right n26"><span class="red n36 lh">&yen;<?php if ($loaninfo->chase_amount): ?><?php echo sprintf('%.2f', $loaninfo->chase_amount + $loaninfo->collection_amount); ?><?php else: ?><?php echo sprintf('%.2f', $loaninfo->amount + $loaninfo->withdraw_fee + $loaninfo->interest_fee); ?><?php endif; ?></span></div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (($loaninfo->status == 12 && time()>strtotime($loaninfo->end_date) && !empty($loaninfo->chase_amount)) || $loaninfo->status == 13): ?>
                <div class="adver">
                    <div class="row">
                        <div class="col-xs-5 cor n26">逾期天数：</div>
                        <div class="col-xs-7 text-right n26"><?php echo ceil((time() - strtotime($loaninfo->end_date)) / (24 * 3600)) ?>天</div>
                    </div>
                </div>
                <div class="adver">
                    <div class="row">
                        <div class="col-xs-5 cor n26">逾期罚息：</div>
                        <div class="col-xs-7 text-right n26"><span class="red">&yen;<?php echo sprintf('%.2f', $loaninfo->chase_amount-($loaninfo->amount + $loaninfo->withdraw_fee + $loaninfo->interest_fee)); ?></span></div>
                    </div>
                </div>
                <div class="adver">
                    <div class="row">
                        <div class="col-xs-5 cor n26">应还款金额：</div>
                        <div class="col-xs-7 text-right n26"><span class="red n36 lh">&yen;<?php echo sprintf('%.2f', $loaninfo->chase_amount + $loaninfo->collection_amount); ?></span></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <img src="/images/bottom.png" width="100%" style="vertical-align:top"/>
        <?php if ($loaninfo->status == 2): ?>
        	<input type="hidden" id="user_id" value="<?php echo $loaninfo->user_id;?>"/>
            <button class="btn1 mt20" onclick="cancle(<?php echo $loaninfo->loan_id ?>)" style="width:100%">取消借款</button>
            <button class="btn mt20 mb40" onclick="sendmobile(<?php echo $loaninfo->loan_id ?>)" style="width:100%">短信通知担保人</button>
        <?php endif; ?>
        <?php if ($loaninfo->status == 7): ?>
            <a class="btn1 mt20" href="/dev/loan" style="width:100%">发起好友借款</a>
            <a class="btn mt20 mb40" href="/dev/loan/borrowing" style="width:100%">发起担保卡借款</a>
        <?php endif; ?>
        <?php if ($loaninfo->status == 3): ?>
            <a class="btn mt20 mb40" href="/dev/account/personal" style="width:100%">完善资料</a>
        <?php endif; ?>
        <?php if ($loaninfo->status == 4): ?>
            <a class="btn mt20" href="/dev/loan/borrowing" style="width:100%">发起担保卡借款</a>
            <a class="btn1 mt20 mb40" href="/dev/loan" style="width:100%">发起好友借款</a>
        <?php endif; ?>
        <?php if ($loaninfo->status == 8): ?>
            <a class="btn mt20 mb40" href="/dev/loan/guarantee" style="width:100%">再次借款</a>
        <?php endif; ?>
        <?php if ($loaninfo->status == 9): ?>
            <a class="btn mt20 mb40" href="/dev/repay/repaychoose?loan_id=<?php echo $loaninfo->loan_id; ?>" style="width:100%">我要还款</a>
        <?php endif; ?>

        <?php if ($loaninfo->status == 12 || $loaninfo->status == 13): ?>
            <a class="btn mt20 mb40" href="/dev/repay/cards?loan_id=<?php echo $loaninfo->loan_id; ?>" style="width:100%">马上还款</a>
        <?php endif; ?>
    </div>
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script src="/js/zebra_dialog.js"></script>
<script>
            function cancle(n) {
            	var user_id = $("#user_id").val();
                $.get("/dev/st/statisticssave", { type: 30,user_id:user_id },function(data){
                	
                });
                $.post('/dev/loan/cancle', {loan_id: n}, function(result) {
                    var data = eval("(" + result + ")");
                    $.Zebra_Dialog(data.msg, {
                        'type': data.ret == 0 ? 'information' : 'error',
                        'title': '取消担保人借款',
                        'buttons': [
                            {caption: '确定', callback: function() {
                                    if (data.url != '') {
                                        window.location = data.url;
                                    }
                                }},
                        ]
                    });
                    if (data.url != '') {
                        window.setTimeout("window.location='" + data.url + "'", 2000);
                    }
                });
            }
            function sendmobile(n) {
            	var user_id = $("#user_id").val();
                $.get("/dev/st/statisticssave", { type: 31,user_id:user_id },function(data){
                	
                });
                $.post('/dev/loan/sendmobile', {loan_id: n}, function(result) {
                    var data = eval("(" + result + ")");
                    $.Zebra_Dialog(data.msg, {
                        'type': data.ret == 0 ? 'information' : 'error',
                        'title': '给担保人发送短信',
                        'buttons': [
                            {caption: '确定', callback: function() {
                                    if (data.url != '') {
                                        window.location = data.url;
                                    }
                                }},
                        ]
                    });
                    if (data.url != '') {
                        window.setTimeout("window.location='" + data.url + "'", 2000);
                    }
                });
            }
            wx.config({
                debug: false,
                appId: '<?php echo $jsinfo['appid']; ?>',
                timestamp: <?php echo $jsinfo['timestamp']; ?>,
                nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
                signature: '<?php echo $jsinfo['signature']; ?>',
                jsApiList: [
                    'hideOptionMenu'
                ]
            });

            wx.ready(function() {
                wx.hideOptionMenu();
            });
</script>