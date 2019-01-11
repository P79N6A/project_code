<div class="end_home_wrap">
    <img src="/borrow/310/images/itemIcon.png" alt="" class="itemIcon">
    <p class="item_title">信用借款服务</p>
    <div class="cash_end">
        <span class="comp_text" style="left:0;">用钱，就找先花一亿元！</span>
    </div>
    <div class="cash_end_title">
        <div>
            <img src="/borrow/310/images/rocket.png" class="rocket">
            <span class="title_txt">3分钟审批</span>
        </div>
        <div>
            <img src="/borrow/310/images/book.png" class="rocket">
            <span class="title_txt">超快到账</span>
        </div>
        <div>
            <img src="/borrow/310/images/home.png" class="rocket">
            <span class="title_txt">无需抵押</span>
        </div>
    </div>
</div>
<div class="account">
    <img src="/borrow/310/images/account.png" class="cash_account">
    <span class="account_list">账单</span>
</div>
<div class="box_end_wrap">
    <span class="account_txt">您当前有<span class="red_color" id="bill_count"><?php echo $total; ?></span>个待还账单</span>
    <span class="account_supply">请按时还款，避免产生逾期费用</span>
    <button class="btn_check" style="padding: 0 .2rem; width:auto;height: auto" onclick="look_bill()">立即查看</button>
</div>
<p class="wrap_txt">点击<a href="javascript:;" class="btn_focus">“<span style="border-bottom: 1px solid #F00D0D;padding: 5px;">关注</span>”</a>官方微信获取福利</p>

<div class="help_service">
    <img src="/borrow/310/images/tip.png" alt="" class="contact_service_tip">
    <a href="javascript:void(0);" onclick="doHelp('/borrow/helpcenter?user_id=<?php echo $user_id;?>')"><span class="contact_service_text">获取帮助</span></a>
</div>
<div class="toast_tishi" id="xtfmang" style="top: 67%;" hidden >复制成功</div>

<div class="poppay_mask" id="toast"  style="position: fixed;top: 0;left: 0;z-index: 1;" hidden></div>
<div class="mask_box" id="toast_tixian_success" style="top: 38%;z-index: 2;height:5.75rem;" hidden  >
    <img src="/borrow/310/images/bill-close.png" onclick="close_toast()" class="close_mask" style="width: 0.4rem;height: 0.4rem;">
    <img src="/borrow/310/images/tx_icon.png" style="height: 0.6rem;position: absolute;left: 3.6rem;top: 0.8rem;" >
    <p class="mask_title" style="top:1.6rem;">提现成功</p>  
    <p class="mask_text" style="text-align: center; width:100%;left:0;top:2.6rem;">恭喜您,提现成功！</p>
    <?php if( $tixian_success == 2 ):?>
    <p class="mask_text"  style="text-align: center; top:3.2rem; width: 100%;left: 0;">立即支付测评账单，可获得专项优惠！</p>
    <span class="add_btn"  onclick="ious_ljhg()" style="top:3.9rem;">立即支付</span>
    <?php else:?>
    <span class="add_btn" onclick="look_bill()" style="top:3.9rem;">查看账单</span>
    <?php endif;?>
    
</div>
<?= $this->render('/layouts/footer', ['page' => 'loan','log_user_id'=>$user_id]) ?>
<script src="/js/clipboard.min.js?v=10001" type="text/javascript"></script>
<script>
    <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var csrf = '<?php echo $csrf;?>';  
    var error_id = <?php echo $error_id ;?>;
    var tixian_success = <?php echo $tixian_success; ?>   ; 
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
    var clipboard = new Clipboard('.btn_focus', {
        text: function () {
            $('#xtfmang').show();
            $("#xtfmang").text('复制成功！');
            setTimeout(function () {
                $("#xtfmang").hide();
                $('#xtfmang').text('');
            }, 1000);
            return "xianhuayyy";
        }
    });
    clipboard.on('success', function (e) {
    });
    function doHelp(url) {
        tongji('do_help',baseInfoss);
        setTimeout(function(){
            window.location.href = url;
        },100);
    }
    function look_bill(){
        zhuge.track('首页点击', {
              '按钮名称': '查看账单',
        });
        window.location.href = '/borrow/billlist/index';
    }
    
    function ious_ljhg(){
       var url = '<?php echo $ious_url;?>';
       window.location =url;
    }
    //取消蒙层        
    function close_toast() {
        $('#toast').hide();
        $('#toast_tixian_success').hide();
    }
    $(function(){
        if( tixian_success == 1 || tixian_success == 2 ){ //提现成功弹窗
              $.ajax({
                url: "/borrow/loan/tixianajax",
                type: 'post',
                async: false,
                data: {_csrf: csrf,error_id:error_id},
                success: function (json) {
                    json = eval('(' + json + ')');
                    if(json.res_code == '0000'){
                        $('#toast').show();
                        $('#toast_tixian_success').show();
                    }
                },
                error: function (json) {
                    console.log('请求失败');
                }
            });
        }
        
    });


</script>