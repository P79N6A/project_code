<style>
    .Hmask { width: 100%;height: 100%;background: rgba(0,0,0,.7); position: fixed;top: 0;left: 0; z-index: 100;}
    /*提额活动弹层*/
    .tancgg{ position: fixed; top:5%; z-index: 100; width:90%; margin:0 5%;}
    .tancgg img{width:100%;}
    .tancgg .tcerror{ position: absolute; top: 0;right: 5%; width: 10%;height: 3rem;}
    .tancgg .buttonbu{ position: absolute; bottom: 4%;left: 9%; width: 82%; height: 4rem; background: rgba(0,0,0,0);border: 0;}
    .tancgg .tanconess{ position: absolute; bottom: 9%;left: 10%; width: 80%;  height: 4rem; background: rgba(0,0,0,0);border: 0;}
    /*付款方式弹窗*/
    .Hmask {width: 100%; height: 100%;background: rgba(0,0,0,.7); position: fixed;top: 0;left: 0; z-index: 100;}
    .fukfsi {width: 90%;position: fixed;top: 15%;left: 5%;border-radius: 5px;z-index: 100;background: #fff;}
    .fukfsi .errore{ position: relative; top:0; left:0; border-bottom:1px #c2c2c2 solid; width: 100%; color: #444;  }
    .fukfsi .errore img{position: absolute; left:5%; top: 18px; width: 6%;}
    .fukfsi .errore span{display: block; width: 100%; text-align: center; font-size: 1.5rem; padding: 15px 0;}

    .fukfsi .gzmess{overflow: hidden; border-bottom: 1px solid #c2c2c2;}
    .fukfsi .gzmess .ymxinxi{ position: relative;}
    .fukfsi .gzmess .ymxinxi p{ width:11% ; float: left; margin:15px 10px 15px 20px;}
    .fukfsi .gzmess .ymxinxi span{ float: left; font-size: 1.4rem; margin-top: 18px; color: #02ba0e;}
    .fukfsi .gzmess .ymxinxi span.bankzf{ color: #5b8bf7;}
    .fukfsi .gzmess .ymxinxi span.xinxiazf{ color: #ff914e;}
    .fukfsi .gzmess .ymxinxi em{ display: block; width: 10%; position: absolute; top:15px; right: 4%;}
    .fukfsi .gzmess.yumendyu p img{ width: 85%;}
    .fukfsi button.queding{ background: #c90000;  color: #fff; border-radius: 20px; font-size: 1.4rem; width: 80%; margin: 10px 10% 20px; height: 3.5rem;}

    .zhihe{top:30%;}
    .fukfsi .payerror{ width: 100%; font-size: 1.25rem; color: #444; padding: 30px 0 15px; text-align: center;}
    .fukfsi .adderr{ position: relative; top:0; left:0; width: 100%; color: #444;  }
    .fukfsi .adderr img{position: absolute; right:5%; top: 10px; width: 6%;}
    .tancgg .error{ position: absolute; top: 0;right: 5%; width: 9%;height: 3rem;}
    .tancgg .buttonbu{ position: absolute; bottom: 4%;left: 9%; width: 82%; height: 4rem; background: rgba(0,0,0,0);border: 0;}
    /*更多还款方式*/
    .txtxtexs .chakan{font-size: 1rem;text-align: center; width: 100%; padding: 10px 31%; height: 2rem;}
    .txtxtexs .chakan span{float: left;display: inline-block;}
    .txtxtexs .chakan img{ float: left; width: 3%; display: inline-block;margin: 6px 5px;}
    /*逾期字样*/
    .yq {color: #c90000;float: right;padding: 5px 5%;}
    .indextc{position: fixed;top: 20%;left: 7%; border-radius: 5px; z-index: 1000; width: 86%;}
    .indextc button{ position: absolute; bottom: 5%; left:25%;width: 50%; background: rgba(0,0,0,0);}
    #overDivs{background: #000;width: 100%;height: 100%;left: 0;top: 0;filter: alpha(opacity=7);opacity: 0.7;z-index: 900;position: fixed!important; position: absolute;_top: expression(eval(document.compatMode &&document.compatMode=='CSS1Compat') ?documentElement.scrollTop + (document.documentElement.clientHeight-this.offsetHeight)/2 :/*IE6*/document.body.scrollTop + (document.body.clientHeight - this.clientHeight)/2);}
    /*已借款为认证弹框*/
    .duihsucc{width: 90%;position: fixed; top: 20%;left: 5%;border-radius: 5px; z-index: 100;background: #fff; color: #444;}
    .duihsucc button{ width: 80%; height: 40px; background: #c90000; color: #fff; font-size: 1.2rem;border-radius: 30px;}
    .duihsucc button.sureyemian{ margin: 15px 10% 20px;}
    .duihsucc p{ text-align: center; font-size: 1rem;}
    .duihsucc p.xuhua{ padding:30px 0 5px; font-size: 1.25rem;}
    .duihsucc p span{ color: #c90000;}
    .duihsucc.hkaapp button{ width: 40%; margin: 15px 0 15px 7%;  height: 35px; line-height: 33px; font-size: 1.1rem; border:1px solid #c90000;}
    .duihsucc.hkaapp button.xzai{ border:1px solid #c90000; color: #c90000; background: #fff;}
    .duihsucc .margbor{ margin-top: 10px; }
    .duihsucc .margbor .dbk_inpL{ border-bottom: 1px solid #e7edf0;}
</style>
<script type="text/javascript">
    $(function() {
        //点击切换
        $('.nav_jk .item').each(function(index) {
            $(this).click(function() {
                $('.nav_jk .item').removeClass('on');
                $(this).addClass('on');
                $('.jk_item').css('display', 'none');
                $('.jk_item').eq(index).css('display', 'block');
            });
        });

        $('.onoffswitch-checkbox').click(function() {
            if ($('.onoffswitch-checkbox').prop('checked') == true) {
                //隔夜还
                setTimeout(function() {
                    $('#qx .dis_mask').css('display', 'block');
                }, 300);
                $('#qx').find('input').attr("disabled", true);
            } else {
                //期限
                setTimeout(function() {
                    $('#qx .dis_mask').css('display', 'none');
                }, 300);
                $('#qx').find('input').attr("disabled", false);
            }
        });

        $('.tchucye .yhqv').click(function() {
            $('.Hmask_ok').css('display', 'none');
            $('.tchucye').css('display', 'none');
        });
    })
</script>
<!--
<div class="allnewjkuan">
    <img src="/images/banner2.png" width="100%">
    <div class="nav_jk">
        <div class="item on">好友借款</div>
        <div class="item ">担保借款</div>
    </div>
</div>
-->
<div class="zuoyminew jk_item">
    <div class="amountyem">
        <span style="">通过APP借款额度更高哦～</span>
        <a href="/dev/ds/down">
            <button style="">去下载</button>
        </a>
    </div>
    <div class="shezhiminay">
        <?php if ($loaninfo->status == 9): ?>
            <img  style="width:30%;" src="/images/daihk.png">
            <!-- 判断是否在选中的用户中-->
            <?php if($loaninfo->number != 0): ?>
                <p class = "yq">当前已续期<em><?php echo $loaninfo->number; ?></em>次</p>
            <?php endif; ?>
            <div class="imgimgnew"><img src="/images/daihaik2.png"></div>
            <div class="txtsty">
                <div class="bse">待还款</div>
                <div>还款确认中</div>
                <div>借款已还清</div>
            </div>
        <?php else: ?>
            <img  style="width:30%;" src="/images/daihk2.png">
            <div class="imgimgnew"><img src="/images/daihaik1.png"></div>
            <div class="txtsty">
                <div >筹款已完成</div>
                <div>审核已通过</div>
                <div class="bse">等待打款</div>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($sinashow == 1): ?>
        <button class="qinlingqv">领取借款</button>
    <?php endif; ?>
    <div class="daihukan_cont">
        <div class="daoqihk">到期应还（元） <span><?php echo sprintf('%.2f', $loaninfo['huankuan_amount']); ?></span></div>
        <div class="rowym">
            <div class="corname">借款金额（元）</div>
            <div class="corliyou" ><?php echo sprintf('%.2f', $loaninfo->amount); ?></div>
        </div>
        <div class="rowym">
            <div class="corname">到账金额（元）</div>
            <div class="corliyou"> <?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $loaninfo->amount - $loaninfo->withdraw_fee) : sprintf('%.2f', $loaninfo->amount); ?></div>
        </div>
        <div class="rowym">
            <div class="corname">保险费（元）  </div>
            <div class="corliyou" ><?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $loaninfo->withdraw_fee) : sprintf('%.2f', $service_amount); ?></div>
        </div>
        <div class="rowym">
            <div class="corname">利息（元）</div>
            <div class="corliyou" ><?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $service_amount) : '0.00'; ?></div>
        </div>
        <div class="rowym">
            <div class="corname">优惠券减免（元）</div>
            <div class="corliyou" >
                <?php if (empty($loan_coupon)): ?>
                    0.00
                <?php else: ?>
                    <?php if ($loan_coupon['val'] == 0): ?>
                        <?php if (($loan_coupon->couponList['limit'] == 0) || ($loan_coupon->couponList['limit'] <= $loaninfo->current_amount)): ?>
                            全免
                        <?php else: ?>
                            <?php echo sprintf('%.2f', $loaninfo->coupon_amount); ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <!--sss-->
                        <?php if($loaninfo->coupon_amount >=  $loaninfo->interest_fee): ?>
                            -<?php echo sprintf('%.2f', $loaninfo->interest_fee); ?>
                        <?php else: ?>

                            <!--eee-->
                            -<?php echo sprintf('%.2f', $loaninfo->coupon_amount); ?>
                            <!--sss-->
                        <?php endif; ?>
                        <!--eee-->
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="rowym">
            <div class="corname">点赞减息（元）</div>
            <div class="corliyou" ><?php if ($loaninfo->like_amount > 0): ?>-<?php echo sprintf('%.2f', $loaninfo->like_amount); ?><?php else: ?>0.00<?php endif; ?></div>
        </div>
        <div class="rowym">
            <div class="corname">借款期限（天）</div>
            <div class="corliyou" ><?php echo $loaninfo->days; ?></div>
        </div>
        <div class="rowym">
            <div class="corname">最后还款日</div>
            <div class="corliyou" ><?php if ($loaninfo->status == 9): ?><?php echo date('Y-m-d', (strtotime($loaninfo->end_date) - 24 * 3600)); ?><?php else: ?>以短信推送时间为准<?php endif; ?></div>
        </div>
    </div>
    <?php if ((!empty($loan_coupon) && ($loan_coupon['val'] == 0) && ($loan_coupon['status'] == 2)) || (!empty($loan_coupon) && $loaninfo['is_calculation'] == 0 && (($loaninfo['interest_fee'] + $loaninfo['withdraw_fee']) <= $loaninfo['coupon_amount'] )) || (!empty($loan_coupon) && $loaninfo['is_calculation'] == 1 && (($loaninfo['interest_fee']) <= $loaninfo['coupon_amount'] ))): ?>
        <a href="<?php echo Yii::$app->request->hostInfo . "/dev/share/freecoupon?uid=" . $loaninfo['user_id'] . "&loan_id=" . $loaninfo['loan_id']; ?>" class="btn1 mt20" style="width:100%">
            <button type="submit" class="bgrey hanhaoyou" >分享到朋友圈</button></a>
    <?php else: ?>
        <a href="<?php echo $shareurl; ?>" class="btn1 mt20" style="width:100%"><button type="submit" class="bgrey hanhaoyou" >喊好友减息</button></a>
    <?php endif; ?>
    <?php if ($loaninfo->status == 9): ?>
        <!--<a href="/dev/repay/cards?loan_id=<?php //echo $loaninfo['loan_id']; ?>"><button type="submit" class="bgrey" >我要还款</button></a>-->
        <!--<a href="javascript:;"><button type="button" class="bgrey" id="gopay">我要4还款</button></a>-->
        <a href="/dev/repay/repaychoose?loan_id=<?php echo $loaninfo['loan_id']; ?>"><button type="button" class="bgrey">我要还款</button></a>
    <?php endif; ?>
    <div class="marbot100"></div>
    <div class="Hmask Hmask_ok" style="display: none;"></div>
    <div class="shezhizfma" style="display: none;">
        <p>新用户需要<em>设置支付密码</em>才能领取借款！</p>
        <button class="lingqv" onclick="setPayPassword(<?php echo $loaninfo->user_id; ?>)">去设置</button>
        <a class="error"></a>
    </div>
</div>

<?php if ($active_show == 1): ?>
    <div id="overDivs"></div>
    <div class="indextc">
        <img src="/images/activity/indextc.jpg">
        <button class="indextcbutton" onclick="location.href='/dev/activity/pinganactivity'">
            <img src="/images/activity/indextcbutton.png">
        </button>
    </div>
<?php endif; ?>

<!--申请借款但还未活体认证-->
<?php if($user_status != 3 && $loaninfo->status == 6): ?>
    <div class="Hmask Hmask_none" ></div>
    <div class="duihsucc">
        <p class="xuhua">您的借款已通过审核！</p>
        <p>下载APP完成视频认证后立即领取借款</p>
        <button class="sureyemian" id = "loansuccok_down">下载领取</button>
    </div>
<?php endif; ?>

<div class="ydabaiye jk_item" hidden>
    <?php if ($exist == '1'): ?>
        <a href="/dev/loan/borrowing"><img src="/images/dbk.png"  style="width:70%;margin:25px 15% 0;"></a>
    <?php else: ?>
        <a href="/dev/loan/mdbk"><img src="/images/dbk.png"  style="width:70%;margin:25px 15% 0;"></a>
    <?php endif; ?>
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    var loan_id = '<?php echo $loaninfo->loan_id; ?>';
    var user_id = '<?php echo $loaninfo->user_id; ?>';
    $('.qinlingqv').click(function() {
        $.get("/dev/st/statisticssave", {type: 154, loan_id: loan_id, user_id: user_id}, function(data) {
            $(".Hmask_ok").show();
            $(".shezhizfma").show();
        })
    });
    $('.Hmask_ok').click(function() {
        $(".Hmask_ok").hide();
        $(".shezhizfma").hide();
        $(".tancgg").hide();
    });
    $('.tcerror').click(function(){
        $(".Hmask_ok").hide();
        $(".tancgg").hide();
    });
    function setPayPassword(user_id) {
        $.get("/dev/st/statisticssave", {type: 155, loan_id: loan_id, user_id: user_id}, function(data) {
            $.post("/dev/loan/sinaactivate", {user_id: user_id}, function(result) {
                    var data = eval("(" + result + ")");
                    if (data.code == 0) {
                        location.href = data.url;
                    } else if (data.code == 2) {
                        alert("暂时无法激活，请稍候再试");
                    } else if (data.code == 11) {
                        alert("已激活,请等待打款");
                        location.href = "/dev/loan";
                    } else {
                        alert("暂时无法激活，请联系先花客服");
                    }
                }
            )
        });
    }
</script>


<!--   选择付款方式弹层和js代码 start  -->
<div id="showChoice" style="display:none">
    <div class="Hmask Hmask_ok"></div>
    <div class="fukfsi">
        <div class="errore">
            <img src="/images/zfufsi4.png" class = 'error_close'>
            <span>还款方式选择</span>
        </div>
        <div class="gzmess yumendyu yhkzf" data-type="2" data-url="/dev/repay/cards?loan_id=<?php echo $loaninfo['loan_id'];?>">
            <div class="ymxinxi">
                <p><img src="/images/zfufsi2.png"></p>
                <span class="bankzf">银行卡支付</span>
                <em class="showImg"></em>
            </div>
        </div>
        <div class="gzmess wxzf" data-type="1" style="display: none;">
            <div class="ymxinxi">
                <p><img src="/images/zfufsi1.png"></p>
                <span>微信支付</span>
                <em class="showImg"></em>
            </div>
        </div>
        <div class="gzmess yumendyu xxzf" data-type="3" data-url="/dev/loan/repay?loan_id=<?php echo $loaninfo['loan_id'];?>" style="display: none;">
            <div class="ymxinxi">
                <p><img src="/images/zfufsi3.png"></p>
                <span class="xinxiazf">线下支付</span>
                <em class="showImg"></em>
            </div>
        </div>
        <div class="txtxtexs" style="display: display;">
            <div  class="chakan"><span>查看更多还款方式 </span><img src="/images/bottomjt.png"></div>
        </div>
        <p id="showError" style="display: none;color:#c90000;padding-top:10px; font-size:1.15rem; width:100%; text-align:center;"></p>
        <button class="queding">确定</button>
    </div>
</div>

<!--支付失败弹层-->
<div style="display:none" id="errorLayer" class="fukfsi zhihe">
    <div class="adderr close_error_layer">
        <img src="/images/zfufsi4.png">
    </div>
    <p class="payerror">支付失败！</p>
</div>

<script>
    $(function(){
        //点击展开支付方式
        $('.txtxtexs').click(function(){
            $('.txtxtexs').hide();
            $('.wxzf').show();
            $('.xxzf').show();
        })
        var initFun = function(){
            $(".Hmask_ok").show();
            $(".queding").attr("disabled" , false);
            $(".payerror").html('支付失败！');
            $("#errorLayer").hide();
            $("#showError").html('').hide();
            $(".showImg").html("");
            $(".gzmess").removeClass("already");
        }
        var imgHtml = '<img class="right_img" src="/images/zfufsi5.png">';
        //点击我要还款 弹层显示
        $("#gopay").click(function(){
            initFun();
            //默认选择银行卡支付,并合并选项
            $('.txtxtexs').show();
            $('.wxzf').hide();
            $('.xxzf').hide();
            $('.yhkzf').find(".showImg").html(imgHtml);
            $('.yhkzf').addClass("already");
            $("#showChoice").show();
        })
        //关闭弹层
        $('.fukfsi .error_close').click(function(){
            $("#showChoice").hide();
            $(".Hmask_ok").hide();
        });
        $('.Hmask_ok').bind('click',function() {
            $("#showChoice").hide();
            $(".Hmask_ok").hide();
        })
        $('#overDivs').click(function () {
            $('#overDivs').hide();
            $('.indextc').hide();
        });
        $('.indextc').click(function () {
            $('#overDivs').hide();
            $('.indextc').hide();
        });

        //选择还款，显示对号图标
        $(".gzmess").click(function(){
            initFun();
            var self = $(this);
            self.find(".showImg").html(imgHtml);
            self.addClass("already");
        })

        //点击确定还款
        $(".queding").click(function(){
            var self = $(this);
//                self.html("loading...");
            self.attr("disabled" , true);
            var already = $(".already"); //是否已经选择了支付方式
            if(already.length <= 0){
                $("#showError").html('请选择还款方式').show();
                return false;
            }

            //获取选中的还款方式的url
            var url = already.attr("data-url");
            var type = parseInt(already.attr("data-type"));
            if(type == 2 || type == 3){  //如果是线下支付和银行卡支付直接调转
                location.href = url;
            }else if(type == 1){  //微信支付
                var url = '/dev/yyygzhpay/submitorderinfo';
//                    微信支付以分为单位 在这里处理
//                    var total_fee = <?php //echo $loaninfo['huankuan_amount'] ?>;
                var loan_id = <?php echo intval($loaninfo['loan_id']) ?>;
//                    var total_fee = '0.01';
                $.ajax({
                    type: "POST",
                    url: url,
                    dataType: 'json',
                    data: {'loan_id':loan_id},
                    success: function(msg){
                        if(msg.status==0){
                            location.href=msg.url;
                        }else{
                            self.attr("disabled" , false);
                            $(".payerror").html(msg.msg);
                            $("#showChoice").hide();
                            $("#errorLayer").show();
                        }
                    },
//                        error:function((xhr, ajaxOptions, thrownError){
//                            self.attr("disabled" , false);
//                            $("#showChoice").hide();
//                            $("#errorLayer").show();
//                            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
//                        }
                });
            }
        })
    });

    //关闭支付失败弹层
    $(".close_error_layer").click(function(){
        $(".Hmask_ok").hide();
        $("#errorLayer").hide();
        $(".payerror").html("支付失败！");
    })

    //点击下载app统计
    $('#loansuccok_down').bind('click', function () {
        $.get("/wap/st/statisticssave", {type: 88}, function () {
            window.location = '/wap/st/down';
            return false;
        })
    })

</script>



<!--   选择付款方式弹层和js代码 end  -->
