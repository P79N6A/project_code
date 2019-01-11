<style>
    .mask{
         position: fixed;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,.3);
        z-index: 999;
        top: 0;
        left: 0;
    }

    .payout_wrap{
        height: 11rem;
        position: relative;
    }
    .cuiyicui{
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        text-align: center;
        color: red;
        font-size: .4rem;
    }
</style>
<div class="payout_wrap">
<!--    1:审核中 2：待激活 3：放款中 4：待提现 5:提现中 -->
    <?php if( $page_status == 2 ):?>
      <div class="payout_main_top">
        <div class="main_text">
          <img src="/borrow/310/images/getPay.png" alt="" class="getPayIcon">
          <span class="getPayTitle">借款待激活</span>
          <span class="getPayText">借款激活成功可立即下款</span>
        </div>
      </div>
        <div class="payout_identification">
          <img src="/borrow/310/images/identificationBar1.png" class="identificationBar">
          <p class="stepNumOne">借款待激活</p>
          <p class="stepNumOneDate">激活倒计时
              <span id="dateShow" class="red" style="color:#e74747;">
                  <span class="h">00</span>:<span class="m">00</span>:<span class="s">00</span>                  
              </span>
          </p>
          <p class="stepNumTwo">待放款</p>
          <p class="stepNumThree">待提现</p>
        </div>
        <div class="test_active" id="evaluation_activation" onclick="buySiganl()" >
          测评激活
        </div>
        <div class="immediate_active" id="redirct_activation" onclick="direct_activation()" >
          直接激活
        </div>
        <div class="alert-box" id="cue_activating" style="width: 90%;display: none; position: fixed; top: 64%;left: 5%;border-radius: 5px; z-index: 100; padding:10px 0;background:rgba(0,0,0,0.5); color: #fff;text-align: center;font-size: 0.4rem; ">
                    您的激活申请正在处理中，请耐心等待
        </div>

        <div class="mask" style="display: none;" id="toast_mask"></div>
        <div id="toast" style=" width: 90%;position: fixed; top: 20%;left: 5%;border-radius: 5px; z-index: 9999;background: #fff; padding-bottom: 20px; display: none;" id="tanceng" >
            <img src="/newdev/images/loan/cha.png" id="reject_activation1" style="position: absolute;  top: -81px;right: 0; display: inline-block;width: 10%;">
            <h3 style="text-align: center;font-size: 0.4rem;padding-top: .6rem;color: #444;font-weight: bold;">是否确认激活？</h3>
            <p style="text-align: center;padding-bottom: 25px; text-align: left; font-size: 0.4rem; color: #444; margin: .6rem 5% .2rem;">你已激活失败两次，三次激活失败将导致借款驳回！</p >
            <button style="width: 40%; color: #c2c2c2; background: #fff; border:1px solid #c2c2c2; font-size: 0.5rem;  margin-left:6%;padding: 8px 0;border-radius: 20px;" id="confirm_activation">确认激活</button>
            <button style="width: 40%; background: #c90000; border:1px solid #c90000; font-size: 0.5rem; color: #fff; margin: 0 6% 0 5%;padding: 8px 0;border-radius: 20px;" id="reject_activation" >取消</button>
       </div>
    <?php elseif( $page_status == 3 ):?>
        <div class="payout_main_top">
             <span class="pass_count">放款中</span>
             <span class="pass_count_text">恭喜您，钱款已上路，最快24小时到账！</span>
         </div>
        <div class="payout_identification">
          <img src="/borrow/310/images/identificationBar2.png" class="identificationBar">
          <p class="stepNumOne">借款审核通过</p>
          <p class="stepNumOneDate"><?php echo date('Y年m月d日 H:i:s',strtotime($time_fk))?></p>
          <p class="stepNumTwo">放款中</p>
          <p class="stepNumThree">待提现</p>

          <div class="toast_tishi"  style="top: 67%;background: rgba(0,0,0,0.6); border-radius: .2rem;font-size: .45rem;" hidden id='cuing'>已经在催了哟</div>
          <div class="toast_tishi"  style="top: 67%;background: rgba(0,0,0,0.6); border-radius: .2rem;font-size: .45rem;" hidden id="cui_success">消息发送成功，正在为您加速处理</div>
        </div>
<p style="text-align: center;width:100%; margin-top:8.5rem;font-size: 0.32rem;color:#444;height: 0.45rem;line-height: 0.45rem;position: absolute;">点击<font style="color:red;" class="btn_focus">“<span style="border-bottom: 1px solid #F00D0D;padding: 5px;">关注</span>”</font>公众号，获取提额小技巧！</p>
<p class="cuiyicui" style="font-weight:700;" onclick="urge()">催一催></p>
<div class="toast_tishi" id="xtfmang" style="top: 67%;" hidden>复制成功</div>
    <?php elseif( $page_status == 4 || $page_status == 5 ):?>
        <div class="payout_main_top">
          <span class="pass_count">您的<?php echo $loan_amount?>元已到账</span>
          <span class="pass_count_text">请务必5天内提现，否则借款将失效</span>
      </div>
        <div class="payout_list_item">
            <span class="item_left_one">实际到账金额</span>
            <span class="item_right_one"><?php echo $loan_amount?>元</span>
            <span class="heng_one" style="height:1px;"></span>
            <span class="item_left_two">借款期限</span>
            <span class="item_right_two"><?php echo $days?>天x<?php echo $period?>期</span>
            <span class="heng_two"></span>
            <span class="item_left_three">综合费用</span>
            <span class="item_right_three"><?php echo $interest_amount;?>元</span>
            <?php if (\app\commonapi\Keywords::inspectOpen() == 2 && !$is_installment): ?>
                <span class="item_left_four">综合利息</span>
                <span class="item_right_four"><?php echo $surplus_amount;?>元</span>
            <?php endif; ?>
        </div>
        <?php if($page_status == 4):?>
            <div class="payout_Btn" onclick="tixian()">
                立即提现
            </div>
        <?php elseif($page_status == 5):?>
        <div class="payout_Btn" style="opacity:0.4;" >
                提现中，请稍后
            </div>
        <?php endif;?>

    <?php endif;?>
</div>
<div class="help_service">
    <img src="/borrow/310/images/tip.png" alt="" class="contact_service_tip">
    <a href="javascript:void(0);" onclick="doHelp('/borrow/helpcenter?user_id=<?php echo $user_id;?>')"><span class="contact_service_text">获取帮助</span></a>
</div>

<!--提现失败弹窗-->
<div class="poppay_mask" id="toast"  style="position: fixed;top: 0;left: 0;z-index: 1;" hidden></div>
<div class="mask_box" id="toast_tixian_fail" style="top: 38%;z-index: 2;height:5.75rem;" hidden  >
    <img src="/borrow/310/images/bill-close.png" onclick="close_toast()" class="close_mask" style="width: 0.35rem;height: 0.35rem;">
    <img src="/borrow/310/images/fail_icon.png" style="height: 0.6rem;position: absolute;left: 3.6rem;top: 0.8rem;width:0.6rem" >
    <p class="mask_title" style="top:1.6rem;">提现失败</p>  
    <p class="mask_text" style="text-align: center; width:100%;left:0;top:2.6rem;">失败原因：<span style="color:#F22727">网络加载延迟</span>，请重新发起提现</p>
    <span class="add_btn" onclick="tixian()" style="top:3.9rem;">再次提现</span>
</div>

<?= $this->render('/layouts/footer', ['page' => 'loan','log_user_id'=>$user_id]) ?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript" src="/298/js/leftTime.min.js?v=20180206"></script>
<script src="/js/clipboard.min.js?v=10001" type="text/javascript"></script>
<script>
    var user_id = '<?php echo $user_id;?>';
    var csrf = '<?php echo $csrf; ?>';
    var direct_activation_url = '<?php echo $direct_activation_url; ?>';
    var activation_btn_status = '<?php echo $activation_btn_status; ?>';
    var mobile = '<?php echo $user_info->mobile; ?>';
    var loan_id = '<?php echo $loan_id; ?>';
    var evaluation_activation_channel = '<?php echo $evaluation_activation_channel; ?>';
    var youxin_down_url = '<?php echo $youxin_down_url; ?>';
    var yxl_authentication_url = '<?php echo $yxl_authentication_url; ?>';
    <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
    var fail_tixian = <?php echo $tixian_fail;?>;
    var error_id = <?php echo $error_id?>;

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


    $(function(){
        //tongji('footer_click_index',baseInfoss);
        var dateShow = "<?php echo $dateShow = date('Y/m/d H:i:s',$yxl_count_down+time());?>";
        $.leftTime(dateShow,function(d){
            if(d.status){
                var $dateShow1=$("#dateShow");
                $dateShow1.find(".h").html(d.h);
                $dateShow1.find(".m").html(d.m);
                $dateShow1.find(".s").html(d.s);
            }
        });
        
        if(fail_tixian == 1 || fail_tixian == 2){
            $.ajax({
                url: "/borrow/loan/tixianajax",
                type: 'post',
                async: false,
                data: {_csrf: csrf,error_id:error_id},
                success: function (json) {
                    json = eval('(' + json + ')');
                    if(json.res_code == '0000'){
                        $('#toast').show();
                        $('#toast_tixian_fail').show();
                    }
                },
                error: function (json) {
                    console.log('请求失败');
                }
            });
        }
    });
    
    //取消蒙层        
    function close_toast() {
        $('#toast').hide();
        $('#toast_tixian_fail').hide();
    }
    //催一催
    function urge(){
        tongji('tixian_urge',baseInfoss);
        setTimeout(function(){
              geturge();
        },100);
    }
    function geturge(){
         $.ajax({
            url: '/borrow/loan/urgeajax',
            type: 'post',
            data:{_csrf:csrf},
            dataType: 'json',
            success: function(msg){
              if( msg.rsp_code === '0000' ){
                  $('#cui_success').show();
                  $('#cuing').hide();
                 setTimeout(function () {
                   $('#cui_success').hide();
                   $('#cuing').hide();
                }, 1000);

                }else if(msg.rsp_code === '1000'){

                    alert(msg.rsp_msg);
                }else if(msg.rsp_code === '2000'){
                    $('#cui_success').hide();
                    $('#cuing').show();
                   setTimeout(function () {
                         $('#cui_success').hide();
                         $('#cuing').hide();
                    }, 1000);

                }
              },
                error:function(msg){
                    console.log('催一催ajax请求失败');
                }
         });
    }

    //提现
    function tixian(){
        tongji('tixian',baseInfoss);
        zhuge.track('立即提现按钮');
        setTimeout(function(){
//              window.location.href = '/borrow/custody/getmoneyopen?loan_id=' + loan_id;
          $.ajax({
                url: '/borrow/custody/getmoneyopen',
                type: 'get',
                data:{loan_id:loan_id},
                dataType: 'json',
                success: function(msg){
                    if(msg.ret == '0000'){
                        window.location.href = msg.msg;
                    }else{
                        alert(msg.msg);
                    }
                },
                error:function(msg){
                 console.log('请求提现接口失败')
                }
          });
        },100);
    }



      function buySiganl() { //测评激活
            tongji('evaluation_activation',baseInfoss);
            $.ajax({
            url: '/borrow/evaluationactivation/clickstatus',
            type: 'get',
            data:{loan_id:loan_id},
            dataType: 'json',
            success: function(msg){
                if( msg.back_code === '0000' ){
                    var click_status = msg.click_status;
                    console.log('ddd:'+click_status);
                   is_click_evaluation(click_status);
                }else{
                    console.log(msg.back_msg);
                }
                },
                error:function(msg){
                 console.log('请求是否可点击测评激活按钮接口失败'+msg)
                }
            });
        }

        function direct_activation(){ //直接激活
             var redict_activation_num = '<?php echo $redict_activation_num; ?>';
             console.log(activation_btn_status);
             tongji('direct_activation',baseInfoss);
             if(activation_btn_status == 0){
                 $('#cue_activating').show();
                   setTimeout(function(){
                            $('#cue_activating').hide();
                   },3000);
            }else{
                if(redict_activation_num < 2){
                    setTimeout(function(){
                        window.location = direct_activation_url;
                      },100);

                }else if(redict_activation_num == 2){
                    //弹框提示已激活2次
                    $('#toast_mask').show();
                    $('#toast').show();
                }else{
                    console.log('已够三次，'+ redict_activation_num);
                }
            }
        }

        function is_click_evaluation(click_status){

            if(  click_status ==0){
                $('#cue_activating').show();
                   setTimeout(function(){
                      $('#cue_activating').hide();
                   },3000);
                return false;
            }

            if( click_status != 0 ){
                if(evaluation_activation_channel == 1){  //下载智融app
                     tongji('activation_down_app',baseInfoss);
                    setTimeout(function(){
                        window.location = youxin_down_url;
                    },100);

                }else if(evaluation_activation_channel == 2){  //智融H5认证
                       tongji('activation_zrys_h5',baseInfoss);
                        setTimeout(function(){
                          window.location = yxl_authentication_url;
                        },100);

                }
            }
        }
       $('#confirm_activation').click(function(){
            tongji('confirm_direct_activation',baseInfoss);

              setTimeout(function(){
                 window.location = direct_activation_url;
              },100);
        });
        $('#reject_activation').click(function(){
            tongji('reject_direct_activation',baseInfoss);
             $('#toast_mask').hide();
             $('#toast').hide();
        });
        $('#reject_activation1').click(function(){
             $('#toast_mask').hide();
             $('#toast').hide();
        });

    function doHelp(url) {
        tongji('do_help',baseInfoss);
        setTimeout(function(){
            window.location.href = url;
        },100);
    }
</script>




