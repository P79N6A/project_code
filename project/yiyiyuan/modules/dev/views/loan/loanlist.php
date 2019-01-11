<script type="text/javascript">
    $(function(){
        $(".jiekmeg h3").click(function(){
            $( this ).addClass('changehover').siblings().removeClass('changehover');
            var show_text = $( this ).text();
            if (show_text == '信用借款'){
                $("#secured").hide();
                $("#credit").show();
            }
            if (show_text == '担保借款'){
                $("#credit").hide();
                $("#secured").show();
            }
        });
    });
</script>
<div class="jiekmeg">
    <h3 class="changehover">信用借款</h3>
    <h3>担保借款</h3>
</div>
<!--信用借口-->
<div class="jdyu" id="credit">
    <?php
    if (empty($loanlist['credit'])){
        ?>
        <div class="message">
            <div class="mesone" style="text-align: center">
                您还没有信用借款！
            </div>
        </div>
        <?php
    }else{
        foreach($loanlist['credit'] as $loan) {
            ?>
            <a href="/dev/loan/succ?l=<?=$loan['loan_id']?>">
                <div class="message">
                    <div class="mesone">
                        <div class="mesone2">
                            <p>借款金额 <em><?php echo sprintf('%.2f', $loan->amount); ?></em>元</p>
                            <span><?php echo date('Y年m月d日 H:i', strtotime($loan->create_time)); ?></span>
                        </div>
                        <div class="mesone3">
                            <p class="green">
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
                            </p>
                        </div>
                    </div>
                </div>
            </a>
            <?php
        }
    }
    ?>
</div>

<!-- 担保借款 -->
<div class="jdyu" id="secured" hidden>
    <?php
    if (empty($loanlist['secured'])){
        ?>
        <div class="message">
            <div class="mesone" style="text-align: center">
                您还没有担保借款！
            </div>
        </div>
        <?php
    }else{
        foreach($loanlist['secured'] as $loan) {
            ?>
            <a href="/dev/loan/succ?l=<?=$loan['loan_id']?>">
                <div class="message">
                    <div class="mesone">
                        <div class="mesone2">
                            <p>借款金额 <em><?php echo sprintf('%.2f', $loan->amount); ?></em>元</p>
                            <span><?php echo date('Y年m月d日 H:i', strtotime($loan->create_time)); ?></span>
                        </div>
                        <div class="mesone3">
                            <p class="green">
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
                            </p>
                        </div>
                    </div>
                </div>
            </a>
            <?php
        }
    }
    ?>
</div>
<div class="Hmask" hidden></div>
<div class="duihsucc" hidden>
    <p class="xuhua"> 短信验证码</p>
    <p>短信验证码已发送到您尾号<span>1234</span>的手机</p>
    <div class="nzsdsm margbor">
        <div class="dbk_inpL">
            <label>验证码</label>
            <input placeholder="请输入最新获取的短信验证码" type="text">
        </div>
    </div>
    <div class="tsmes">*手机号错误</div>
    <button class="sureyemian">下一步</button>
</div>

<div class="duihsucc bohomeg" hidden>
    <h3>借款驳回</h3>
    <img src="/newdev/images/iconjth.png" class="iconjth">
    <p class="bhtime">驳回时间：</p>
    <p class="sfmiao">2017-06-23 20:15</p>
    <p class="bhtime">驳回理由</p>
    <p class="sfmiao">借款电话审核未通过，请填写正确的联系人电话或工作电话后再次提交申请。</p>
    <button class="yesknow">我知道了</button>
</div>

