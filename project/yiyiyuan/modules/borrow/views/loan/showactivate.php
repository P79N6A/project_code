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
</div>
<div class="help_service">
    <img src="/borrow/310/images/tip.png" alt="" class="contact_service_tip">
    <a href="javascript:void(0);" onclick="doHelp('/borrow/helpcenter?user_id=<?php echo $user_info->user_id;?>')"><span class="contact_service_text">获取帮助</span></a>
</div>

<?= $this->render('/layouts/footer', ['page' => 'loan','log_user_id'=>$user_info->user_id]) ?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript" src="/298/js/leftTime.min.js?v=20180206"></script>
<script src="/js/clipboard.min.js?v=10001" type="text/javascript"></script>
<script>
    var user_id = '<?php echo $user_info->user_id;?>';
    var csrf = '<?php echo $csrf; ?>';
    var direct_activation_url = '<?php echo $direct_activation_url; ?>';
    var activation_btn_status = '<?php echo $activation_btn_status; ?>';
    var mobile = '<?php echo $user_info->mobile; ?>';
    var req_id = '<?php echo $req_id; ?>';
    var evaluation_activation_channel = '<?php echo $evaluation_activation_channel; ?>';
    var youxin_down_url = '<?php echo $youxin_down_url; ?>';
    var yxl_authentication_url = '<?php echo $yxl_authentication_url; ?>';
    <?php \app\common\PLogger::getInstance('weixin','',$user_info->user_id); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );

    $(function(){
        var dateShow = "<?php echo $dateShow = date('Y/m/d H:i:s',$yxl_count_down+time());?>";
        $.leftTime(dateShow,function(d){
            if(d.status){
                var $dateShow1=$("#dateShow");
                $dateShow1.find(".h").html(d.h);
                $dateShow1.find(".m").html(d.m);
                $dateShow1.find(".s").html(d.s);
            }
        });
    });

    function buySiganl() { //测评激活
          tongji('evaluation_activation',baseInfoss);
          $.ajax({
          url: '/borrow/creditactivation/clickstatus',
          type: 'get',
          data:{req_id:req_id},
          dataType: 'json',
          success: function(msg){
              if( msg.back_code === '0000' ){
                  var click_status = msg.click_status;
                  is_click_evaluation(click_status);
              }else{
                  console.log(msg.back_msg);
              }
              },
              error:function(msg){
               console.log('请求是否可点击测评激活按钮接口失败'+msg);
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
                 },1000);
          }else{
              alert(redict_activation_num);
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
                 },2000);
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




