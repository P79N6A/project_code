<style>
    .process-bar {
    background: red;
}

.process-bar .prcess {
    position: absolute;
    display: inline-block;
    background-image: linear-gradient(-90deg, #FF4B17 0%, #F00D0D 100%);
    height: 2px;
    width: 100%;
}
.process-bar span {
    position: absolute;
    width: 2px;
    height: 8px;
    margin-top: -5px;
}
.process-bar>span:nth-child(2) {
    left: 20%;
    background: #fff;
}
.process-bar>span:nth-child(3) {
    left: 40%;
    background: #fff;
}
.process-bar>span:nth-child(4) {
    left: 60%;
    background: #fff;
}
.process-bar>span:nth-child(5) {
    left: 80%;
    background: #fff;
}
</style>

<section class="process-bar">
            <div class="prcess"></div>
            <!-- 进度条控制css样式.process-bar .prcess 的width属性20%,40%,60%....100% -->
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </section>


<div>
    <span style="height: 0.8rem;line-height: 0.8rem;color: #cccccc;padding-left: .5rem;">绑定信用卡信息，可以提高通过率和借款额度哦！</span>
</div>
<div class="photo">
    <div class="photo_input" style="margin-top:0;">
      <div>
        <label>姓名</label>
        <span><?php echo $user_name;?></span>
      </div>
      <div class="shenfenzheng">
        <label>身份证号</label>
        <span><?php echo $identity;?></span>
      </div>
    </div>
</div>

  <div class="hr"></div>
  <div class="per_info">
    
       <div class="com_reg">
      <label>信用卡号</label>
      <input type="text" name="card_no" id="card_no" placeholder="请输入您本人的信用卡号">
      <p hidden id="tip_card">*请填写正确的信用卡号</p>
    </div>
       <div class="com_reg">
      <label>手机号</label>
      <input type="text"  name="mobile" id="mobile" placeholder="请输入银行预留手机号" type="tel" onkeyup="value=value.replace(/[^\d]/g,'')"  maxlength="11">
      <p hidden id="jiebangcg"  >*请填写正确的手机号</p>
    </div>
    <div class="com_reg">
      <label>验证码</label>
      <input style="    width: 3.67rem;" type="text" name="code" id="mobile_code" placeholder="请输入手机验证码">
      <p id="reg_one_error" hidden id="tip_code">*请填写正确的验证码</p> 
      <span id="get_bankcode" style="display: inline-block;width: 3rem;border-left: 2px solid #cccccc;box-sizing: border-box;
            padding-left: 0.2rem;">获取验证码</span>
    </div>
  </div>
     <div class="progress_btn ok_btn" id="sub">提交</div> 
  <div class="help">
    <img src="/borrow/310/images/help_deng.png">
      <a href="/borrow/helpcenter/list?position=7&user_id=<?php echo $user_id;?>" style="text-decoration:none;color:#3D81FF;"><span>获取帮助</span></a>
  </div>
<!--  <div>
      
      <p style="text-align: center;
    margin-top: 2rem;    font-size: 0.4rem;
    color: #cccccc;" onclick="tiaoguo()" > 跳过> 
      </p>
  </div>-->
<script src="/290/js/jquery-1.10.1.min.js"></script>
<script>
    var userid = '<?php echo $user_id;?>';
    var _csrf = '<?php echo $csrf;?>';
    <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );

    var _mobileRex = /^(1(([0-9][0-9])|(47)))\d{8}$/;
    var _cardRex = /^\d{15,19}$/;
    var identity = '<?php echo $identity?>';
    var user_name = '<?php echo $user_name?>';
    var data_type = '<?php echo $data_type?>';//type：1从必填资料项去填写信用卡认证 2：从选填资料
    var bank_type = 2;
    var flag = true;
    var location_href = "<?php echo $redirect_info; ?>"; 
    
    $('#sub').click(function () {
            tongji('cardvalid_submit',baseInfoss);
            zhuge.track('信用卡认证页面-提交按钮');
            var card_no = $('input[name="card_no"]').val();
            var mobile = $('input[name="mobile"]').val();
            var code = $('input[name="code"]').val();
            card_no = card_no.replace(/\s+/g, "");
            var user_id = $('input[name="user_id"]').val();
            //var bank_type = 2;

            if(identity.length == 0) {
                $("#jiebangcg").show();
                $("#jiebangcg").text('请填写身份证号');
                setTimeout(function(){
                    $("#jiebangcg").hide();
                    $("#jiebangcg").text('');
                },1000);
                return false;
            }
            if(card_no.length == 0) {
                $("#jiebangcg").show();
                $("#jiebangcg").text('请填写信用卡号');
                setTimeout(function(){
                    $("#jiebangcg").hide();
                    $("#jiebangcg").text('');
                },1000);
                return false;
            }
            if(mobile.length == 0) {
//                $("#remain").html('*请填写银行预留手机号');
                $("#jiebangcg").show();
                $("#jiebangcg").text('请填写正确的手机号');
                return false;
            }
            if(code.length == 0) {
                $("#tip_code").show();
                $("#tip_code").text('请填写入短信验证码');
                return false;
            }
            if (mobile == '' || !(_mobileRex.test(mobile))) {
                $("#jiebangcg").show();
                $("#jiebangcg").text('请填写正确的手机号');
                return false;
            }
            if (!(_cardRex.test(card_no))) {
                $("#tip_card").show();
                $("#tip_card").text('请填写正确的信用卡号');
                return false;
            }
            if(!code){
                $("#tip_code").show();
                $("#tip_code").text('请填写短信验证码');
                $("#sub").attr('disabled', false);
                return false;
            }
            if(code.length != 4){
                $("#tip_code").show();
                $("#tip_code").text('请填写正确的短信验证码');
                $("#sub").attr('disabled', false);
                return false;
            }

            $("#sub").attr('disabled', true);
            $.post("/borrow/userauth/bindcard_xyk", {_csrf:_csrf, user_id: user_id, real_name: user_name, identity:identity, card: card_no, mobile: mobile, code: code, banktype: bank_type}, function (result) {
                var data = eval("(" + result + ")");
                console.log(data);
                if (data.res_code == 0) {
                    zhuge.identify(userid, {
                        '信用卡已认证': 1,  // 0表示false，1表示ture，下同
                    });
                    setTimeout(function(){
                        if(data_type == 1){
                           url ='/borrow/userinfo/requireinfo'; 
                        }else if(data_type == 2){
                            url ='/borrow/userinfo/selectioninfo'; 
                        }else{
                             url = location_href;
                        }
                        
                        window.location.href =url;
                    },1000);
                    $("#sub").attr('disabled', false);
                } else if (data.res_code == 1) {
                    $("#tip_code").show();
                    $("#tip_code").text('短信验证码输入错误，请重新输入');
                    $("#sub").attr('disabled', false);
                    return false;
                } else if (data.res_code == 2) {
                    alert(data.res_data.msg);
                    $("#sub").attr('disabled', false);
                    return false;
                } else if (data.res_code == 3) {
                    alert(data.res_data.msg);
                    $("#sub").attr('disabled', false);
                    return false;
                } else {
                    alert('系统繁忙，请稍后重试');
                    $("#sub").attr('disabled', false);
                    return false;
                }
            });
        });
 
 //绑卡短信验证码发送
$('#get_bankcode').on("click",handleClick);
function handleClick(){
    if($('#get_bankcode').hasClass('dis')){
        return false;
    }
    $('#get_bankcode').addClass('dis');
     tongji('cardvalid_getsmscode',baseInfoss);
     var card_no = $('input[name="card_no"]').val();
     var mobile = $('input[name="mobile"]').val();
        console.log(card_no);
        console.log(mobile);
     if (mobile == '' || !(_mobileRex.test(mobile))) {
         $("#jiebangcg").show();
         $("#jiebangcg").text('请填写正确的手机号');
         $('#get_bankcode').removeClass('dis');
         return false;
     }    
     if(card_no == '' ){
         $("#tip_card").show();
         $("#tip_card").text('请填写正确的信用卡号');
         $('#get_bankcode').removeClass('dis');
           console.log('银行卡不正确1');
          return false;
    }
     $("#get_bankcode").off("click");    
     $.post("/borrow/userauth/banksend", {_csrf:_csrf, mobile: mobile, cardno: card_no, banktype:bank_type}, function(result) { 
         var data = eval("(" + result + ")");
                  console.log(data);
         if (data.res_code == 0) {
             //发送成功
             console.log('发送成功');
             count = 60;
             countdown = setInterval(CountDown_bank, 1000);
         } else if(data.res_code == 1){
             console.log('银行卡不正确2');
             $("#tip_card").show();
             $("#tip_card").text('请填写正确的信用卡号');
             $("#card_no").focus();
             //$("#get_bankcode").attr('disabled', "false");
             $('#get_bankcode').on("click",handleClick);
             $('#get_bankcode').removeClass('dis');
             return false;
         } else if(data.res_code == 5){
             $("#jiebangcg").show();
             $("#jiebangcg").text('请填写正确的手机号');

             $("#mobile").focus();
             //$("#get_bankcode").attr('disabled', "false");
             $('#get_bankcode').on("click",handleClick);
             $('#get_bankcode').removeClass('dis');
             return false;
         }else {
             $("#jiebangcg").show();
             $("#jiebangcg").text('短信验证码获取次数已达上限,请24小时后重试');
             //$("#mobile").focus();
//             $("#get_bankcode").attr('disabled', "false");
            $('#get_bankcode').on("click",handleClick);
             $('#get_bankcode').removeClass('dis');
             return false;
         }
     });
     
     var CountDown_bank = function() {
            $("#get_bankcode").html("重新获取(" + count + ")");
            $("#get_bankcode").off("click");
            $("#get_bankcode").addClass('dis');
            if (count <= 0) {
		$('#get_bankcode').on("click",handleClick);
                $("#get_bankcode").html("获取验证码").removeAttr("disabled").removeClass('dis');
                clearInterval(countdown);
            }
            count--;
        };
 };
  
 $(function(){
//     input_mobile();
//     input_card();
//     console.log(document.referrer);
    
        //重写返回按钮
       pushHistory();
            var bool=false;
            setTimeout(function(){
                bool=true;
            },1500);
            window.addEventListener("popstate", function(e) {
            tongji('cardvaild_reback_btn',baseInfoss);
            if(bool){
               //根据自己的需求返回到不同页面
           setTimeout(function(){
                window.location.href= document.referrer;
            },100);
            }
                pushHistory();
            }, false);
       function pushHistory() {
           var state = {
               url: "#"
           };
           window.history.pushState(state,  "#");
       }
 });
       $('#mobile').bind({ 
           blur:function(){
               tongji('cardvalid_input_mobile',baseInfoss);
           }
       });
       $('#card_no').bind({ 
          blur:function(){
               tongji('cardvalid_input_cardnum',baseInfoss);
            }
       }); 
 
//    function input_mobile(){
//       $('#mobile').bind({ 
//           focus:function(){ 
//                setTimeout(function(){
//                    tongji('cardvalid_input_mobile',baseInfoss);
//                },100);
//           }
//       }); 
//    }
    
//    function input_card(){
//       $('#card_no').bind({ 
//           focus:function(){ 
//               tongji('cardvalid_input_cardnum',baseInfoss);
//           }
//       }); 
//    }
</script>


