<script>
    $(window).load(function() {
        var lineH = $('.col-xs-2 img').height();
        $('.col-xs-4').css('lineHeight', lineH + 'px');
        var height = $('.details_list').height();
        if ($(window).width() < 640) {
            $('.details_list .db00').css('height', height + 10 + 'px');
            $('.details_list .db11').css('height', height + 10 + 'px');
        } else {
            $('.details_list .db00').css('height', height + 30 + 'px');
            $('.details_list .db11').css('height', height + 30 + 'px');
        }
        var height2 = $('.col-xs-6').height();
        $('.col-xs-6').css('marginTop', (lineH - height2) / 2)
    });
</script>
<div class="Hcontainer nP">
    <?php if (!empty($loanlist)): ?>
        <?php foreach ($loanlist as $loan): ?>
            <div class="details_list">
                <?php if ($loan->business_type == 3): ?>
                    <div class="db11"></div>
                <?php elseif ($loan->business_type == 2): ?>
                    <div class="db00"></div>
                <?php endif; ?>
                <a href="/dev/loan/succ?l=<?php echo $loan->loan_id; ?>">                    
                <div class="row">
                    <div class="col-xs-2 nPad relative">
                            <img src="<?php
                            if ($loan->user->userwx) {
                                echo $loan->user->userwx->head;
                            } else {
                                echo '/images/dev/face.png';
                            }
                            ?>"  class="face2"/>
                            <img src="/images/<?php echo $sex%2==0?'icon_girl':'icon_boy';?>.png" class="gender2">
                        </div>
                        <div class="col-xs-6 pd">
                            <span class="red n28"><?php echo sprintf('%.2f', $loan->amount); ?></span><br/>
                            <span class="n22 cor"><?php echo date('m月d日 H:i', strtotime($loan->create_time)); ?></span>
                        </div>
                        <div class="col-xs-4 text-right nPad">
                            <div class="n22 blue" style="display:inline-block;width:85%;">
                                <?php if ($loan->status == '1') { ?>
                                    审核中
                                <?php } else if ($loan->status == '2') { ?>
                                    筹款中
                                <?php } else if ($loan->status == '3') { ?>
                                    审核驳回
                                <?php } else if ($loan->status == '4') { ?>
                                    已失效
                                <?php } else if ($loan->status == '5') { ?>
                                    申请提现
                                <?php } else if ($loan->status == '6') { ?>
                                    审核中
                                <?php } else if ($loan->status == '7') { ?>
                                    申请提现驳回
                                <?php } else if ($loan->status == '8' && $loan->settle_type == 2) { ?>
                                    已续期
                                <?php } else if($loan->status == '8' && $loan->settle_type == 0){ ?>
                                    已完成
                                <?php } else if ($loan->status == '9') { ?>
                                    待还款
                                <?php } else if ($loan->status == '10') { ?>
                                    待出款
                                <?php } else if ($loan->status == '11') { ?>
                                    待确认还款
                                <?php } else if ($loan->status == '12') { ?>
                                    <?php if (date('Y-m-d H:i:s') >= $loan->end_date): ?>
                                        已逾期
                                    <?php else: ?>
                                        还款失败
                                    <?php endif; ?>
                                <?php }else if ($loan->status == '13') { ?>
                                    <?php if (date('Y-m-d H:i:s') >= $loan->end_date): ?>
                                        已逾期
                                    <?php else: ?>
                                        还款失败
                                    <?php endif; ?>
                                <?php }else if ($loan->status == '15') { ?>
                                    申请提现驳回
                                <?php } else if ($loan->status == '16') { ?>
                                    担保人拒绝投资
                                <?php } else if ($loan->status == '17') { ?>
                                    已取消借款
                                <?php } ?>
                            </div>
                            <img src="/images/arrowRed.png" width="7.5%"/>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="container text-center empty mt20">
            <img src="/images/dev/empty.png" width="53.1%"/>
            <p class="n40">您还没有借款！</p>
        </div>
    <?php endif; ?>
</div>

<script>
    $(window).load(function() {
        var lineH = $('.col-xs-2 img').height();
        $('.col-xs-4').css('lineHeight', lineH + 'px');
        var height = $('.details_list').height();
        if ($(window).width() < 640) {
            $('.details_list .db00').css('height', height + 10 + 'px');
            $('.details_list .db11').css('height', height + 10 + 'px');
        } else {
            $('.details_list .db00').css('height', height + 30 + 'px');
            $('.details_list .db11').css('height', height + 30 + 'px');
        }
        var height2 = $('.col-xs-6').height();
        $('.col-xs-6').css('marginTop', (lineH - height2) / 2)
    });
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>       
<script>
    $('.Hmask').click(function() {
        $('.Hmask').toggle();
        $('.layer_border').toggle();
    });
    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'closeWindow',
            'hideOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.hideOptionMenu();
    });



</script>