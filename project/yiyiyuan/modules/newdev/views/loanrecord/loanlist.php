<!-- <script type="text/javascript">
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
</script> -->
<!-- <div class="jiekmeg">
    <h3 class="changehover">信用借款</h3>
    <h3>担保借款</h3>
</div> -->
<!--信用借口-->
<div class="jdyu" id="credit">
    <?php
        if (empty($loanlist)){
    ?>
    <div class="message">
        <div class="mesone" style="text-align: center">
            您还没有借款记录！
        </div>
    </div>
    <?php
    }else{
            foreach($loanlist as $loan) {
                ?>
                <a href="/new/loanrecord/creditdetails?loan_id=<?=$loan['loan_id']?>">
                <div class="message">
                    <div class="mesone">
                        <div class="mesone2">
                            <p>借款金额 <em><?php echo sprintf('%.2f', $loan->amount); ?></em>元</p>
                            <span><?php echo date('Y年m月d日 H:i:s', strtotime($loan->create_time)); ?></span>
                        </div>
                        <div class="mesone3" style="with: 35%;">
                            <?php if ($loan->status == '3' || $loan->status == '4'|| $loan->status == '7' || $loan->status == '23') { ?>
                                <p class="grey">
                                    已驳回
                                </p>
                            <?php } else if ($loan->status == '5' || in_array($loan->status, [20])) { ?>
                                <p class="green">
                                    审核中
                                </p>
                            <?php } else if ($loan->status == '6') { ?>
                                <p class="green">
                                    待打款
                                </p>
                            <?php } else if ($loan->status == '8') { ?>
                                <p class="green">
                                    已还款
                                </p>
                            <?php } else if ($loan->status == '9') { ?>
                                <p class="green">
                                    待还款
                                </p>
                            <?php } else if ($loan->status == '11') { ?>
                                <p class="green">
                                    待确认
                                </p>
                            <?php } else if ($loan->status == '12') { ?>
                                <p class="redred">
                                    已逾期
                                </p>
                            <?php } else if ($loan->status == '13') { ?>
                                <p class="redred">
                                    已逾期
                                </p>
                            <?php } else if ($loan->status == '18') { ?>
                                <p class="green">
                                    待提现
                                </p>
                            <?php } else if ($loan->status == '19') { ?>
                                <p class="green">
                                    提现中
                                </p>
                            <?php } else if ($loan->status == '21') { ?>
                                <p class="green">
                                    待激活
                                </p>
                            <?php } else if ($loan->status == '22') { ?>
                                <p class="green">
                                    匹配中
                                </p>
                            <?php } ?>
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

