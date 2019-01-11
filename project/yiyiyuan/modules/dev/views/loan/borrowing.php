<?php 
    $startTime = Yii::$app->params['newyear_start_time'];
    $endTime   = Yii::$app->params['newyear_end_time'];
    $time = time();
    if($time >= $startTime && $time <= $endTime){
        include '../modules/dev/views/loan/newyear.php';
    }
?>


<script>
    window.onload = function () {
        var loan_type = $('#loan_type').val();
        if (loan_type == 1) {
            $('#loan_days').attr('disabled', true);
            $('#qx .dis_mask').css('display', 'block');
            $('#qx').find('input').attr("disabled", true);
        } else {
            $('#loan_days').attr('disabled', false);
            $('#qx .dis_mask').css('display', 'none');
            $('#qx').find('input').attr("disabled", false);
        }
    }
</script>
<div class="Hcontainer nP">
    <img src="/images/sxed_head.jpg" width="100%">
    <div class=" overflow" style="position: absolute;top: 15px;color:#444; margin:10px 6.25% 0; padding-left:5px;">
        <p class="n28 mt10" style="color:#444;">您有<?php echo $dtotal; ?>点担保额度,可担保借款<span id='money'><?php echo $guarantee_amount; ?></span>元</p>
        <p class="n22 pink mt10" style="color:#c2c2c2;">*银行会扣去您1%的通道费哦！</p>
    </div>
    <form action='/dev/loan/qd' method='GET'>
        <div class="main">
            <div class="col-xs-12 nPad">
                <div class="dbk_inpL mt20">
                    <label class="n26"><span id="desc_col">借款用途</span></label><input type="text" name='desc' id='loan_desc'>
                </div>
            </div>
            <div class="col-xs-12 nPad mt20">
                <div class="col-xs-6" style="padding-right: 2%;">
                    <div id="qx" class="dbk_inpS">
                        <label id="day_col">期限(天)</label>
                        <input type="text" maxlength="2" placeholder="7－21" name='days' id='loan_days' disabled="disabled">
                        <div class="dis_mask" style="display: block;"></div>
                    </div>
                </div>
                <div class="col-xs-6" style="padding-left: 2%;">
                    <div id="geh" class="dbk_inpS">
                        <label class="geh_ques">隔夜还<img src="/images/icon_ques2.png" alt="" width="20%" onclick = "help()"></label>
                        <div class="onoffswitch">
                            <input type="checkbox" name="days" class="onoffswitch-checkbox" checked="checked">
                            <label class="onoffswitch-label" for="myonoffswitch">
                                <div class="onoffswitch-inner">
                                    <div class="onoffswitch-active"></div>
                                    <div class="onoffswitch-inactive"></div>
                                </div>
                                <div class="onoffswitch-switch"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 nPad">
                <div class="dbk_inpL mt20">
                    <label class="n26" id="mon_col">金额（元）</label><input type="text" maxlength="10" placeholder="可担保借款<?php echo $guarantee_amount; ?>元" name='amount' id="loan_amount">
                </div>
            </div>
            <div class="col-xs-12 nPad mt40">
                <p id="loan_error_tip" class="red"></p>
                <p class="text-right n30" >到期应还款<span class="red" id='yinhuan'>0.00</span>元</p>
            </div>
            <div class="clearfix"></div>
            <input type="hidden" id="loan_type" value="1"/>
            <?php if ($is_bank == '0'): ?>
                <?php if ($user_id == 284574 || $user_id == 318310 || $user_id == 562548 || $user_id == 555837 || $user_id == 562779 || $user_id == 622498 ||  $user_id == 1788313 || $user_id ==2514033): ?>
                    <button class="btn mt20 " style="width:100%" disabled="disabled" value='确定'>确定</button>
                <?php else: ?>
                    <button class="btn mt20 " style="width:100%" id="geh_sure" value='确定'>确定</button>
                <?php endif; ?>
            <?php else: ?>
                <?php if ($user_id == 284574 || $user_id == 318310 || $user_id == 562548 || $user_id == 555837 || $user_id == 562779 || $user_id == 622498 ||  $user_id == 1788313 || $user_id ==2514033 || $user_id == 1487846 || $user_id == 698192): ?>
                    <a href="javascript:;" class="btn mt20 " style="width:100%" disabled="disabled" value='确定'>确定</a>
                <?php else: ?>
                    <a href="javascript:;" class="btn mt20 " style="width:100%" id="geh_sure1" value='确定'>确定</a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($isexist == '1'): ?>
                <a href='/dev/loan/loanlist' id='laon_buttons' type="submit" class="btn1 mt20" style="width:100%;">查看借款记录</a>
            <?php endif; ?>
        </div>

        <div class="Hmask" style="display:none;"></div>
        <div class="layer_border overflow noBorder layer1" id="gyh_mask" style="display:none;">
            <p class="n28 mb30 padlr625">您当前借款期限为<span class="red" id='span1'>7</span>天，若您逾期未还款，将在<span class="red" id="span2">7</span>天后抵消您的担保额度，您才可以进行下笔借款。<span class="red">要试试隔夜还吗？</span></p>

            <div class="border_top_2 nPad overflow">
                <button class="n30 boder_right_1 text-center" id='next_loan'><span class="grey2">继续借款</span></button>
                <a class="n30 red text-center bRed" id="second_loan"><span class="white">隔夜还</span></a>
            </div>
        </div>  
    </form>
    <!--<div class="Hmask" style="display:none;"></div>-->
    <div class="xhb_layer pad" style="display:none;">
        <img src="/images/icon_wt.png" style="width:30%;position: absolute;top:-84px;left:-5px;width:100px;">
        <p class="n26 mt20"><span class="red">隔夜还</span>为担保卡借款的专属功能，<span class="red">光速出款</span>。借款时间为1天，若您到期未全额还款，一亿元将抵消您的担保额度。</p>
        <button href='' class="btn_red" id="zzdl">朕知道了</button>
    </div>
    <div class="layer_border layer3" style="display:none;">
        <div class="padlr625">
            <p class="n30 text-indent">您尚未绑定借记卡，不能完成借款，快去添加吧。</p>
        </div>
        <div class="clearfix"></div>
        <div class="border_top_red mt20 text-center">
            <a href="/dev/bank/addcard" class="n26 bRed borRad5" style="display:block;width:80%;margin: 10px auto;margin-left: 10%;"><span class="white">去绑定</span></a>
        </div>
    </div>
</div>
<script>
	$(function(){
        //春节放假期间担保卡借款界面进来弹层
        var startTime = parseInt(<?php echo  Yii::$app->params['newyear_start_time'] ?>);
        var endTime = parseInt(<?php echo  Yii::$app->params['newyear_end_time'] ?>)
        var time = parseInt(<?php echo  time() ?>)
        if(time >= startTime && time <= endTime){
            $(".show_new_year").show();
        }else{
            $(".show_new_year").hide();
        }
        $(".sureyemian").click(function(){
            window.location.href='/dev/loan';
        })
    })
</script>
<script>
    //非隔夜换的天数写入
    $(function () {
        $("#loan_days").bind('blur', function () {
            var loan_days = $("#loan_days").val();
            $("#span1").html(loan_days);
            $("#span2").html(loan_days);
        })
    })

    //隔夜还的问号
    function help() {
        $('.Hmask').show();
        $('.xhb_layer').show();
    }
    $('#zzdl').click(function () {
        $('.Hmask').hide();
        $('.xhb_layer').hide();
    });
    $('#geh_sure1').click(function () {
        $('.Hmask').show();
        $('.layer3').css('display', 'block');
    });

</script>

<script>
    $(function () {
        
        
        //var _numberRex = /^[0-9]*[1-9][0-9]*$/;
        var _numberRex = /^[1-9]{1}[0-9]*$/;
        var flagdesc = flagdays = flagamount = false;

        $('.onoffswitch-checkbox').click(function () {
            if ($('.onoffswitch-checkbox').prop('checked') == true) {
                //隔夜还
                $('.onoffswitch-checkbox').attr('checked', 'checked');
                setTimeout(function () {
                    $('#qx .dis_mask').css('display', 'block');
                }, 300);
                $('#qx').find('input').attr("disabled", true);
                $('#loan_type').val('1');
                if (!flagdesc)
                {
                    $("#loan_error_tip").html("");
                }
            } else {
                //期限
                $('.onoffswitch-checkbox').removeAttr('checked');
                setTimeout(function () {
                    $('#qx .dis_mask').css('display', 'none');
                }, 300);
                $('#qx').find('input').attr("disabled", false);
                $('#loan_type').val('2');

            }
        });


        $('#loan_desc').keyup(function () {
            var desc = $("#loan_desc").val();

            if (desc.length < 5 || desc.length > 25) {
                $("#loan_error_tip").html("请输入5~25个字符");
                $('#desc_col').css('color', '#e74747');
                flagdesc = false;
            } else {
                $("#loan_error_tip").html("");
                $('#desc_col').css('color', '#444444');
                flagdesc = true;
            }
            return flagdesc;
        });


        $("#loan_days").bind('keyup', function () {
            var days = $("#loan_days").val();
            var amount = parseInt($("#loan_amount").val());
            if (days == '' || !(_numberRex.test(days))) {

                $("#loan_error_tip").html("请输入7~21天");
                $('#day_col').css('color', '#e74747');
                flagdays = false;
            } else {
                days = parseInt(days);
                if (days < 7 || days > 21) {
                    $("#loan_error_tip").html("请输入7~21天");
                    $('#day_col').css('color', '#e74747');
                    flagdays = false;
                } else {
                    $("#loan_error_tip").html("");
                    $('#day_col').css('color', '#444444');
                    var repayVal = parseFloat(amount);
                    $('#loan_repay_amount').html(repayVal);
                    flagdays = true;
                }

            }
            return flagdays;
        });

        $("#loan_amount").bind('keyup', function () {
            var days = $("#loan_days").val();
            var amount = parseInt($("#loan_amount").val());
            var money = parseInt($('#money').html());
            var leastnum = 100;
            if (amount == '' || !(_numberRex.test(amount))) {
                $("#loan_error_tip").html("请输入100~" + money + "的整数");
                $('#mon_col').css('color', '#e74747');
                flagamount = false;
                //return false;
            } else {
//			$("#loan_error_tip").html("");
//			flagamount = true;
                if (amount < 100 || amount > money) {
                    $("#loan_error_tip").html("请输入100~" + money + "的整数");
                    $('#mon_col').css('color', '#e74747');
                    flagamount = false;
                } else {
                    $("#loan_error_tip").html("");
                    $('#mon_col').css('color', '#444444');
                    amount = Math.ceil(amount / 0.99 * 100) / 100;
                    $("#yinhuan").html(amount);
                    var repayVal = parseFloat(amount)
                    $('#loan_repay_amount').html(repayVal);
                    flagamount = true;
                }
            }
            return flagamount;
        });

        $("#geh_sure").bind('click', function () {
            var desc = $("#loan_desc").val();
            //alert(desc);
            var days = $("#loan_days").val();

            var amount = parseInt($("#loan_amount").val());
            var money = parseInt($('#money').html());
            var loan_type = $('#loan_type').val();
            if (loan_type == '2') {
                var days = $("#loan_days").val();
            } else if (loan_type == '1') {
                var days = 1;
            }


            //alert(loan_type);
            //alert(amount);
            var leastnum = 100;

            if (desc == '' || days == '' || amount == '') {
                $("#loan_error_tip").html("借款金额不符合要求，请重新填写");
                if (desc == '') {
                    $('#desc_col').css('color', '#e74747');
                }
                if (days == '') {
                    $('#day_col').css('color', '#e74747');
                }
                if (amount == '') {
                    $('#mon_col').css('color', '#e74747');
                }
                return false;
            }
            $("#loan_desc").keyup();
            $("#loan_days").keyup();
            $("#loan_amount").keyup();
            var filter = /^[1-9]{1}[0-9]*$/;
            if (!(filter.test(amount))) {
                $("#loan_error_tip").html("请输入100以上的正整数");
                $('#mon_col').css('color', '#e74747');
                return false;
            }
            if (loan_type == '2')
            {
                if (!flagdesc || !flagdays || !flagamount)
                {
                    if (!flagdesc)
                    {
                        $("#loan_error_tip").html("请输入5~25个字符");
                        $('#desc_col').css('color', '#e74747');
                        return false;
                    }
                    if (!flagdays)
                    {
                        $("#loan_error_tip").html("请输入7~21天");
                        $('#day_col').css('color', '#e74747');
                        return false;
                    }
                    if (!flagamount)
                    {
                        if (amount > money || amount < 100) {
                            $("#loan_error_tip").html("请输入100~" + money + "的整数");
                            $('#mon_col').css('color', '#e74747');
                            return false;
                        }
                    }
                }
                else
                {
                    $('.Hmask').css('display', 'block');
                    $('#gyh_mask').css('display', 'block');

                    return false;
                    //$("#loan_form").submit();
                }
            }
            else
            {
                if (!flagdesc || !flagamount)
                {
                    if (!flagdesc)
                    {
                        $("#loan_error_tip").html("请输入5~25个字符");
                        $('#desc_col').css('color', '#e74747');
                        return false;
                    }
                    if (!flagamount)
                    {
                        if (amount > money || amount < 100) {
                            $("#loan_error_tip").html("请输入100~" + money + "的整数");
                            $('#mon_col').css('color', '#e74747');
                            return false;
                        }
                    }
                }
                else
                {

                    $("#loan_form").submit();
                }
            }

        });

        $("#next_loan").click(function () {
            $("#loan_form").submit();
        });

        $("#second_loan").click(function () {
            $('.Hmask').hide();
            $('#gyh_mask').hide();
            $('#qx .dis_mask').css('display', 'block');
            //$('.onoffswitch-checkbox').attr('checked', true);
            $('.onoffswitch-checkbox').attr('checked', 'checked');
            $('.onoffswitch-checkbox').trigger('click');
            setTimeout(function () {
                $('#qx .dis_mask').css('display', 'block');
            }, 300);
            $('#qx').find('input').attr("disabled", true);
            $('#loan_type').val('1');
        });


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

    wx.ready(function () {
        wx.hideOptionMenu();
    });
</script>