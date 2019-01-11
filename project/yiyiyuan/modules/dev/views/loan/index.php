<script type="text/javascript">
    $(document).ready(function() {
        //底部导航
        $('input').focus(function() {
            $('footer').css('display', 'none');
        });
        $('input').blur(function() {
            $('footer').css('display', 'block');
        });
        //点击切换
        $('.nav_jk .item').each(function(index) {
            $(this).click(function() {
                $('.nav_jk .item').removeClass('on');
                $(this).addClass('on');
                $('.jk_item').removeClass('on');
                $('.jk_item').eq(index).addClass('on');
            });
        });
    })
</script>

<style>
    .Hcontainer .p_ipt{background:#e7edf0;border:0; padding: 0;line-height:40px;}
    .Hcontainer .p_ipt .col-xs-9{background: #fff;border-radius: 5px; padding-left: 10px; border: 1px solid #cdcdcd;}
    .Hcontainer .p_ipt .col-xs-9 input{  height:40px; background: #fff; width: 96%;}
    .Hcontainer .p_ipt .col-xs-4{width:36%;height:40px; background: #fff; border: 1px solid #cdcdcd; text-align: center; border-radius: 0;}
    .Hcontainer .p_ipt .col-xs-4.padrgh{ margin-right:6px;}
    .Hcontainer .p_ipt .col-xs-2{ width: 18%; padding: 0; margin:0; text-align: center;background: #fff; border: 1px solid #cdcdcd; margin-right: 2px;border-radius: 0;}
    .Hcontainer .p_ipt .imgimg{position: relative; border:1px solid #e74747;}
    .Hcontainer .p_ipt .imgimg img{ width: 15%; display:inline-block;position: absolute; right:0; top:-2px; }
    .Hcontainer .p_ipt .col-xs-2.imgimg img{width: 30%;}
    .Hcontainer  .imgtuzi{ position: relative;}
    .Hcontainer  .imgtuzi img{ width: 40px;height:55px; position: absolute; right: 0; top:-64px; display: inline-block;}

    .Hcontainer .p_ipt .col-xs-9 .seltxt{ width: 100%;height: 40px; border: 0;
                                          padding-left: 10px;
                                          margin-left: -5%;
                                          /*很关键：将默认的select选择框样式清除*/
                                          appearance:none;
                                          -moz-appearance:none;
                                          -webkit-appearance:none;
                                          /*在选择框的最右侧中间显示小箭头图片*/
                                          background: url("/images/yumen.png") no-repeat scroll right center transparent;
                                          background-size: 8%;
    }
    .Hcontainer .p_ipt .col-xs-9 .seltxt div{ height: 100px; overflow: hidden;}
    .Hcontainer .p_ipt .col-xs-9 .seltxt option{ display: block; text-align: center; padding: 10px; text-indent: 20px;}

    /*优惠卷*/
    .Hcontainer_coupon .boldera{height:50px;line-height:50px; font-size:18px; padding-left:40%; font-weight: bold;border-bottom: 1px solid #c4bfbe;}
    .Hcontainer_coupon .boldera span{ display: inline-block; float: right;padding-right: 10%; font-weight: normal; font-size: 14px; color: #e74747;}
    .overflow{overflow: hidden;}
    .Hcontainer_coupon{padding-bottom: 78px;}
    .Hcontainer_coupon .Hmask{width: 100%;height: 100%;background: rgba(0,0,0,.7);position: fixed;top: 0;left:0;z-index: 100;}
    .Hcontainer_coupon .layer{width:95%;position: fixed;top:15%;left:45%;margin-left: -43%;background: #fff;border-radius: 10px;z-index: 110;}
    .Hcontainer_coupon .layer .item,.layer .item{overflow: hidden;position: relative; margin: 10px auto;}
    .Hcontainer_coupon .layer .item input{display:none;}
    .Hcontainer_coupon .layer .choose{float: left;position: absolute;top:65%;margin-top: -20px;}
    .Hcontainer_coupon .layer .available2{margin:0 auto; width:94%;max-width:502px;display:block;}
    .Hcontainer_coupon .layer .price_left{text-align: center;width: 40%; position: absolute;top: 20%;left: 0%;color: #fff;padding: 0;}
    .Hcontainer_coupon .layer .price_left.left3{ left:3%;}
    .Hcontainer_coupon .layer .price_left p.black{ font-size: 20px; font-weight: bold; color: #444;}
    .Hcontainer_coupon .layer .price_left p.black span{ font-size: 14px;}
    .Hcontainer_coupon .layer .price_left p.green{background: #ffc24d;font-size:12px;height: 20px;line-height: 20px;border-radius: 10px;margin: 10px auto 0;text-align: center;width: 60px;}
    .Hcontainer_coupon .layer .price_left p.white{ color: #fff; }
    .Hcontainer_coupon .layer .price_left p.ftsz24{ font-size: 24px;}
    .Hcontainer_coupon .layer .price_left p.baishes{ color: #fff; font-size: 24px;}
    .Hcontainer_coupon .layer .price_left p.basise{ background: #fff; color: #ffc24d;}
    .Hcontainer_coupon .layer .price_left p.bgrf{ background: #fff; color: #f56a45;}
    .Hcontainer_coupon .layer .price_left p.rgbf{ background: #f56a45; color: #fff;}
    .Hcontainer_coupon .layer .price_right{width: 60%;position: absolute;top:18%;right: 2px;font-size: 12px;color: #fff;line-height: 22px; text-align: center;}
    .Hcontainer_coupon .layer .price_right .one_one{ color: #444; font-size: 16px;}
    .Hcontainer_coupon .layer .price_right .one_two{color: #ffc24d}
    .Hcontainer_coupon .layer .price_right .redred{ color: #f56a45;}
    .Hcontainer_coupon .layer .price_right .one_three{color: #c2c2c2;}
    .Hmask { width: 100%;height: 100%;background: rgba(0,0,0,.7); position: fixed;top: 0;left: 0; z-index: 100;}


    /*提额活动弹层*/
    .tancgg{ position: fixed; top:5%; z-index: 100; width:90%; margin:0 5%;}
    .tancgg img{width:100%;}
    .tancgg .tcerror{ position: absolute; top: 0;right: 5%; width: 10%;height: 3rem;}
    .tancgg .buttonbu{ position: absolute; bottom: 4%;left: 9%; width: 82%; height: 4rem; background: rgba(0,0,0,0);border: 0;}
    .tancgg .tanconess{ position: absolute; bottom: 9%;left: 10%; width: 80%;  height: 4rem; background: rgba(0,0,0,0);border: 0;}

    .indextc{position: fixed;top: 20%;left: 7%; border-radius: 5px; z-index: 100; width: 86%;}
    .indextc button{ position: absolute; bottom: 5%; left:25%;width: 50%; background: rgba(0,0,0,0); border: 0;}
    #overDivs{background: #000;width: 100%;height: 100%;left: 0;top: 0;filter: alpha(opacity=7);opacity: 0.7;z-index: 11;position: fixed!important; position: absolute;_top: expression(eval(document.compatMode &&document.compatMode=='CSS1Compat') ?documentElement.scrollTop + (document.documentElement.clientHeight-this.offsetHeight)/2 :/*IE6*/document.body.scrollTop + (document.body.clientHeight - this.clientHeight)/2);}
/*三周年活动*/
.tanchuceng{ position: fixed;top: 0%;left: 0%;border-radius: 5px;z-index: 100;}
.tanchuceng  img{ width: 80%;margin:20px 10% 0 ;}
.tancymia{ position: fixed;top: 0%;left: 0%;border-radius: 5px;z-index: 100;}
.tancymia img{ width: 90%; margin: 20% 5% 0;}
.tancymia .tcerror{width: 27%; height: 3rem;position: absolute; bottom: 5%; right: 14%; top: 75%}
.tancymia .tcerror_gz{width: 27%; height: 3rem;position: absolute; bottom: 5%; right: 14%; top: 87%}
.tancymia.indexelayer{ top:3rem; z-index: 10000;}
.indexelayer .toperror { width: 10%;  height: 3rem; position: absolute;top: 19%; right: 12%;}
.indexelayer .bottomlink { width: 74%; height: 18%; position: absolute; bottom: 2rem;left: 13%;}
</style>
<div class="Hcontainer">
    <div>
        <img src="/images/banner2.png" width="100%" />
        <ul class="nav_jk overflow">
            <li class="col-xs-6">
                <div class="item on">好友借款</div>
            </li>
            <li class="col-xs-6">
                <div class="item">担保借款</div>
            </li>
        </ul>
    </div>
    <div class="main">

        <ul>
            <li class="jk_item on">
                <?php if ($userinfo->status == 2): ?>
                    <div style="margin:25px 5% 15px">
                        <h3 style="text-align:center; padding-bottom:30px; border-bottom:2px solid #e74747;">资料已提交成功</h3>
                        <p style="text-indent:24px; padding-top:10px;"> 由于您是初次使用，需要进行身份审核，工作时间（早9点半--晚6点半）24小时内审核完成，非工作时间次日进行审核。</p>
                    </div>
                    <div class="main">
                        <ul>
                            <li class="">
                                <form class="form-horizontal" role="form">
                                    <button type="button" class="btn mt20" onclick="javascript:window.location = '/dev/loan'">刷新</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div id="current_amount" style="margin-bottom:5px; height:30px;"><span style="float:left;line-height:30px;">通过APP借款额度更高哦～</span>
                        <a href="/dev/ds/down">
                            <button style="float:right; width:20%; background:#e74747;color:#e74747;  border-radius:5px;padding:5px 0;border:0; color:#fff;">去下载</button>
                        </a>
                    </div>
                    <form class="form-horizontal" role="form" method="post" action="/dev/loan/second" id="loan_form">
                        <div class="form-group p_ipt">
                            <div class="col-xs-3">借款金额：</div>
                            <div class="col-xs-4 padrgh <?php echo $value == 500 ? 'imgimg' : ''; ?>" id="amount_500">500元 
                                <img src="/images/imgimg.png" style="<?php echo $value == 1000 ? 'display:none' : ''; ?>">
                            </div>
                            <?php if ($value == 1000): ?>
                                <div class="col-xs-4 imgimg" id="amount_1000">1000元
                                    <img src="/images/imgimg.png">
                                </div>
                            <?php endif; ?>
                            <input type="hidden" name="amount" value="<?php echo $value; ?>">
                        </div>
                        <div class="form-group p_ipt">
                            <div class="col-xs-3">借款周期：</div>                            
                            <div class="col-xs-2" id="days_21" style="width:36%;margin-right:6px;">21天
                                <img src="/images/imgimg.png" style="display:none; width: 15%;">
                            </div>
                            <div class="col-xs-2 imgimg" id="days_28" style="width:36%;margin-right:0;">28天
                                <img src="/images/imgimg.png" style="width:15%;">
                            </div>
                            <input type="hidden" name="day" value="28">
                            <input type="hidden" name="day_rate" id="day_rate" value='<?php echo $dayratestr; ?>'>
                            <?php if (!empty($couponlist)): ?>
                                <input type="hidden" name="coupon_id" id="coupon_id" value="<?php echo $couponlist[0]['id']; ?>">
                                <input type="hidden" name="coupon_amount" id="coupon_amount" value="<?php echo $couponlist[0]['val']; ?>">
                                <input type="hidden" name="coupon_limit" id="coupon_limit" value="<?php echo $couponlist[0]['limit']; ?>">
                            <?php else: ?>
                                <input type="hidden" name="coupon_id" id="coupon_id" value="">
                                <input type="hidden" name="coupon_amount" id="coupon_amount" value="">
                                <input type="hidden" name="coupon_limit" id="coupon_limit" value="">
                            <?php endif; ?>
                        </div>
                        <div class="form-group p_ipt">
                            <div class="col-xs-3">借款理由：</div>
                            <div class="col-xs-9">
                                <!--<input type="text" name="desc" class="ipt" placeholder="请输入5～25个字"/>-->
                                <select class="seltxt" name="desc" id="desc">
                                    <div>
                                        <?php foreach ($loandesc as $val): ?>
                                            <option><?php echo $val[0] ?></option>
                                        <?php endforeach; ?>                                        
                                    </div>
                                </select>
                            </div>
                        </div>
                        <?php if (!empty($couponlist)): ?>
                            <div class="form-group p_ipt highlight">
                                <div class="col-xs-3">优惠券：</div>
                                <div class="<?php if (!empty($couponlist)): ?>col-xs-9 red cor imgtuzi<?php else: ?>col-xs-9 cor imgtuzi<?php endif; ?>" id="use_coupon">
                                    <?php if (!empty($couponlist)): ?>
                                        <?php if ($couponlist[0]['val'] != 0): ?>
                                            优惠券<?php echo $couponlist[0]['val']; ?>元
                                        <?php else: ?>
                                            全免
                                        <?php endif; ?>
                                    <?php else: ?>
                                        使用优惠券可减免服务费
                                    <?php endif; ?>
                                </div>
                                <!-- <i></i> -->
                            </div>
                        <?php else: ?>
                            <div class="form-group p_ipt highlight">
                                <div class="col-xs-3">优惠券：</div>
                                <div class="col-xs-9 cor imgtuzi">
                                    您还没有优惠券
                                </div>
                                <!-- <i></i> -->
                            </div>
                        <?php endif; ?>
                        <div class="imgtuzi">
                            <img src="/images/tuzi.png">
                        </div>

                        <p class="red mb20 n22" id="loan_error_tip"></p>
                        <p class="n26 text-right">到期应还款：<label class="red n30" id="loan_repay_amount"><?php echo round($repay, 2); ?></label>元</p>
                        <button type="button" id="loan_button" <?php if ($isexist == '1'): ?>class="bgrey btn mt20" disabled="disabled"<?php else: ?>class="btn mt20"<?php endif; ?>><?php if ($isexist == '1') { ?>您有未完成的借款<?php } else { ?>确定<?php } ?></button>
                        <!-- <button type="submit" class="btn1 mt20" style="width:100%;" >查看当前借款</button>  -->
                        <?php if ($isexist == '1'): ?><a href="/dev/loan/succ?l=<?php echo $loan_id; ?>"><button type="button" class="btn1 mt20" style="width:100%;" >查看当前借款</button></a><?php endif; ?>
                    </form>
                <?php endif; ?>
            </li>
            <li class="jk_item">
                <div class="text-center">
                    <!--这里为点击跳转链接-->
                    <?php if ($exist == '1'): ?>
                        <a href="/dev/loan/borrowing"><img src="/images/dbk.png" width="70%"></a>
                    <?php else: ?>
                        <a href="/dev/loan/mdbk"><img src="/images/dbk.png" width="70%"></a>
                    <?php endif; ?>
                </div>
            </li>
        </ul>
    </div>
</div>

<div class="Hcontainer_coupon" style="display:none">
    <div class="Hmask"></div>
    <div class="layer overflow" style="position:absolute;">
        <div class="boldera">优惠券 <span id="use_loan_coupon" class="queding">确定</span></div>
        <div class="content padlr">

            <?php if (!empty($couponlist)): ?>
                <?php foreach ($couponlist as $key => $value): ?>
                    <div class="item">
                        <img src="/images/<?php echo $key == 0 ? 'choosered.png' : 'unchoosered.png'; ?>" class="available2">
                        <div class="price_left">
                            <?php if ($value['val'] != 0): ?><p class="black ftsz24 <?php echo $key == 0 ? 'white' : ''; ?>"><?php echo intval($value['val']); ?><span>元</span><?php else: ?><p class="black ftsz24 <?php echo $key == 0 ? 'white' : ''; ?>">全免<span>券</span><?php endif; ?></p>
                            <p class="green rgbf">好友借款</p>
                        </div>
                        <div class="price_right">
                            <p class="one_one"><?php echo $value['title']; ?></p>
                            <p class="redred"><?php if ($value['limit'] == 0): ?>不限金额<?php else: ?>满<?php echo $value['limit']; ?>元可用<?php endif; ?></p>
                            <p class="one_three">有效期：<?php echo date('Y' . '年' . 'm' . '月' . 'd' . '日', (strtotime($value['end_date']) - 24 * 3600)); ?></p>
                        </div>
                        <input type="radio" name="discount" cid="<?php echo $value['id']; ?>" min="<?php echo intval($value['limit']); ?>" value="<?php echo $value['val']; ?>" id="radio-<?php echo $key + 1; ?>">
                    </div>
                <?php endforeach; ?>
            <?php endif; ?> 

        </div>                    
    </div>
</div>

<!--申请借款但还未活体认证-->
<?php if($user_status != 3 && $loan_status == 6): ?>
<div class="Hmask Hmask_none" ></div>
<div class="duihsucc">
    <p class="xuhua">您的借款已通过审核！</p>
    <p>下载APP完成视频认证后立即领取借款</p>
    <button class="sureyemian" id = "loansuccok_down">下载领取</button>
</div>
<?php endif; ?>
<?= $this->render('/layouts/_page', ['page' => 'loan']) ?>
<script>
    $('.col-xs-2').click(function() {
        var id = this.id;
        $('.col-xs-2').each(function() {
            $('#' + this.id).removeClass('imgimg');
            $('#' + this.id + '>img').css('display', 'none');
        });
        $('#' + id).addClass('imgimg');
        $('#' + id + '>img').css('display', 'block');
        var day = id.split("_")[1];
        $('input[name="day"]').val(day);
        getRepay();
    });

    $('.col-xs-4').click(function() {
        var id = this.id;
        $('.col-xs-4').each(function() {
            $('#' + this.id).removeClass('imgimg');
//            $('#' + this.id).removeClass('imgimg');
            $('#' + this.id + '>img').css('display', 'none');
        });
        var amount = id.split("_")[1];
        if (amount == 1000) {
            $('#' + id).addClass('imgimg');
        } else {
            $('#' + id).addClass('padrgh imgimg');
        }
        $('#' + id + '>img').css('display', 'block');
        $('input[name="amount"]').val(amount);
        getRepay();
    });

    $("#use_coupon").click(function() {
        $(".Hmask").css('display', 'block');
        $(".layer").css('display', 'block');
        $(".Hcontainer_coupon").css('display', 'block');
        $('.Hcontainer_coupon .queding').click(function() {
            setTimeout(function() {
                $('.Hcontainer_coupon').css('display', 'none');
                $('.Hmask').css('display', 'none');
            }, 100)

        });
    });

    var getRepay = function() {
        var days = $('input[name="day"]').val();
        var amount = $('input[name="amount"]').val();
        var rateStr = $('input[name="day_rate"]').val();
        var coupon_amount = $("#coupon_amount").val();
        if (coupon_amount != '' && coupon_amount == 0) {
            $("#loan_error_tip").html("");
            $('#mon_col').css('color', '#444444');
            $('#loan_repay_amount').html(parseFloat(amount));
            flagamount = true;
        } else {
            $("#loan_error_tip").html("");
            $('#mon_col').css('color', '#444444');
            if (coupon_amount == '') {
                var repayVal = parseFloat(amount) + parseFloat(amount * rateStr * days);
            } else {
                //判断优惠券的金额是否大于借款的服务费，如果优惠券的金额大于借款的服务费，则优惠券只能优惠服务费的金额，多余的金额作废
                if (parseFloat(amount * rateStr * days) >= coupon_amount) {
                    var repayVal = parseFloat(amount) + parseFloat(amount * rateStr * days) - coupon_amount;
                } else {
                    var repayVal = parseFloat(amount);
                }
            }
            $('#loan_repay_amount').html(repayVal);
            flagamount = true;
        }
    };


    $(".Hmask").click(function() {
        $(".Hmask").hide();
        $(".Hcontainer_coupon").hide();
        $(".tancgg").hide();
    });

    $('.tcerror').click(function() {
        $(".Hmask").hide();
        $(".tancgg").hide();
    });

    $('#overDivs').click(function () {
        $('#overDivs').hide();
        $('.indexelayer').hide();
    });
    //关闭按钮
    $('.toperror').click(function () {
        $('#overDivs').hide();
        $('.indexelayer').hide();
    });


    //使用优惠券
    $("#use_loan_coupon").click(function() {
        var coupon_amount = $('input:radio:checked').val();
        var coupon_id = $('input:radio:checked').attr('cid');
        if (coupon_amount == undefined || coupon_id == undefined) {
            $(".Hmask").hide();
            $(".layer").hide();
            return false;
        }
        var days = $('input[name="day"]').val();
        var amount = $('input[name="amount"]').val();
        var limit = $('input:radio:checked').attr('min');
        if ((_numberRex.test(amount))) {
            amount = parseInt(amount);
            if ((limit != 0) && (amount < limit) && (coupon_amount != 0)) {
                $("#coupon_amount").val(coupon_amount);
                $("#coupon_limit").val(limit);
                $("#loan_error_tip").html("金额满" + limit + "才可使用");
                return false;
            } else if ((limit != 0) && (amount < limit) && (coupon_amount == 0)) {
                $("#coupon_amount").val(coupon_amount);
                $("#coupon_limit").val(limit);
                $("#loan_error_tip").html("金额满" + limit + "才可使用");
                return false;
            } else {
                $(".Hmask").hide();
                $(".layer").hide();
                if (coupon_amount != 0) {
                    var html = "优惠券" + coupon_amount + "元";
                } else {
                    var html = "全免";
                }
                $("#use_coupon").html(html).removeClass('col-xs-9 cor').addClass('col-xs-9 red');
                var rateStr = $("#day_rate").val();
                var date_rate = rateStr;
                $("#coupon_id").val(coupon_id);
                $("#coupon_amount").val(coupon_amount);
                $("#coupon_limit").val(limit);
                if ((days != '')) {
                    if (coupon_amount == 0) {
                        var repayVal = parseFloat(amount);
                        $('#loan_repay_amount').html(repayVal);
                    } else {
                        //判断优惠券的金额是否大于借款的服务费，如果优惠券的金额大于借款的服务费，则优惠券只能优惠服务费的金额，多余的金额作废
                        if (parseFloat(amount * date_rate * days) >= coupon_amount) {
                            var repayVal = parseFloat(amount) + parseFloat(amount * date_rate * days) - coupon_amount;
                            $('#loan_repay_amount').html(repayVal);
                        } else {
                            var repayVal = parseFloat(amount);
                            $('#loan_repay_amount').html(repayVal);
                        }
                    }
                }
            }
        } else {
            $(".Hmask").hide();
            $(".layer").hide();
        }
    });
</script>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
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