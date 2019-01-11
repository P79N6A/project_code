<style>
    .poppay_mask{
        position: fixed;top: 0;left: 0;z-index: 1;height: 100%;width: 100%;background: #000;opacity: 0.5;
    }
    .mask_box{
        top: 38%;z-index: 2;height:2.65rem; width: 6.89rem;
        position: absolute;
        text-align: center;
        left: 50%;
        top: 50%;
        background: #fff;
        transform: translate(-50%,-50%);
        border-radius: 0.13rem;
    }
    .mask_title{
        top:1.6rem;
        font-size: 0.35rem;
        color: #444444;
        line-height: 0.48rem;
        width: 100%;
        position: absolute;
        left: 0;
        text-align: center;
        top: 0.5rem;
        font-weight: bold;
    }
    .add_btn{
        background-image: linear-gradient(-90deg, #F00D0D 0%, #FF4B17 100%);
        border-radius: 0.13rem;
        height: 0.9rem;
        width: 3rem;
        position: absolute;
        left: 50%;
        top:1.35rem;
        text-align: center;
        margin-left: -1.5rem;
        font-size: 0.43rem;
        color: #FFFFFF;
        line-height: 0.9rem;
    }
</style>
<div class="wraper">
        <header  class="xyjk-head">
            <p class="xy-head-title">完善补充资料</p>
            <p class="xy-head-txt">提高审核通过率，有机会获取更高额度</p>
            <a class="xyjk-help" href="/borrow/helpcenter?user_id=<?php echo $user_id;?>"><span class="rz-foot-txt" >获取帮助</span></a>
        </header>
        <section class="zl-content">
            <div class="zl-con-list">
                <img src="/borrow/310/images/xinyongka.png" alt="">
                <span class="zl-txt">信用卡认证</span>
                <span class="zl-num">推荐</span>
                <?php if( $bank_valid == 1 ):?>
                <span class="zl-rz" onclick="do_card_valid()">去认证</span>
                <?php elseif( $bank_valid == 2 ):?>
                <span class="zl-wc">已完成</span>
                <?php endif;?>
            </div>
            <!--1:未认证 2：已认证  3认证中 4：已过期-->
            <div class="zl-con-list">
                <img src="/borrow/310/images/gongjijin.png" alt="">
                <span class="zl-txt">公积金认证</span>
                <?php if( $fund_valid == 1 ):?>
                <span class="zl-rz" onclick="toAuth('<?php echo $user_id ?>', 3, this)">去认证</span>
                <?php elseif($fund_valid == 4 ):?>
                <span class="zl-rz" onclick="toAuth('<?php echo $user_id ?>', 3, this)">已过期</span>
                <?php elseif( $fund_valid == 2):?>
                <span class="zl-wc">已完成</span>
                <?php elseif( $fund_valid == 3):?>
                <span class="zl-wc">认证中</span>
              <?php endif;?>
            </div>
            <div class="zl-con-list">
                <img src="/borrow/310/images/shebao.png" alt="">
                <span class="zl-txt">社保认证</span>
               <?php if( $social_valid == 1 ):?>
                <span class="zl-rz" onclick="toAuth('<?php echo $user_id ?>', 2, this)">去认证</span>
               <?php elseif( $social_valid == 4 ):?>
                <span class="zl-rz" onclick="toAuth('<?php echo $user_id ?>', 2, this)">已过期</span> 
               <?php elseif($social_valid ==2):?>
                 <span class="zl-wc">已完成</span>
               <?php elseif($social_valid == 3 ):?>
                  <span class="zl-wc">认证中</span>
               <?php endif;?>
            </div>
            <div class="zl-con-list">
                <img src="/borrow/310/images/edu.png" alt="">
                <span class="zl-txt">学历认证</span>
                <?php if( $edu_valid == 1 ):?>
                <span class="zl-rz" onclick="toAuth('<?php echo $user_id ?>', 1, this)" >去认证</span>
                <?php elseif( $edu_valid == 2 ):?>
                <span class="zl-wc">已完成</span>
                <?php elseif($edu_valid == 3 ):?>
                  <span class="zl-wc">认证中</span>
                <?php endif;?>
            </div>
            <div class="zl-con-list">
                <img src="/borrow/310/images/bankflow.png" alt="">
                <span class="zl-txt">银行卡认证</span>
                <?php if($bankflow_valid == 1 ):?>
                    <span class="zl-rz" onclick="toAuth('<?php echo $user_id ?>', 7, this)" >去认证</span>
                <?php elseif($bankflow_valid == 2 ):?>
                    <span class="zl-wc">已完成</span>
                <?php elseif($bankflow_valid == 3 ):?>
                    <span class="zl-wc">认证中</span>
                <?php elseif ($bankflow_valid == 4): ?>
                    <span class="zl-rz" onclick="toAuth('<?php echo $user_id ?>', 7, this)">已过期</span>
                <?php endif;?>
            </div>
            <div class="zl-con-list">
                <img src="/borrow/310/images/jd.png" alt="">
                <span class="zl-txt">京东认证</span>
                <?php if($jd_valid == 1 ):?>
                    <span class="zl-rz" onclick="toAuth('<?php echo $user_id ?>', 4, this)" >去认证</span>
                <?php elseif($jd_valid == 2 ):?>
                    <span class="zl-wc">已完成</span>
                <?php elseif($jd_valid == 3 ):?>
                    <span class="zl-wc">认证中</span>
                <?php elseif ($jd_valid == 4): ?>
                    <span class="zl-rz" onclick="toAuth('<?php echo $user_id ?>', 4, this)">已过期</span>
                <?php endif;?>
            </div>
              <div class="zl-con-list">
                <img src="/borrow/310/images/taobao.png" alt="">
                <span class="zl-txt">淘宝认证</span>
                <?php if($taobao_valid == 1 ):?>
                    <span class="zl-rz" onclick="toAuth('<?php echo $user_id ?>', 6, this)" >去认证</span>
                <?php elseif($taobao_valid == 2 ):?>
                    <span class="zl-wc">已完成</span>
                <?php elseif($taobao_valid == 3 ):?>
                    <span class="zl-wc">认证中</span>
                <?php elseif ($taobao_valid == 4): ?>
                    <span class="zl-rz" onclick="toAuth('<?php echo $user_id ?>', 6, this)">已过期</span>
                <?php endif;?>
            </div>
        </section>
    <?php if($isShow == 1):?>
         <?php if($isCreditshow):?>
    <button class="big345-button rz-filed" id="dorecredit" onclick="do_re_credit()">重新获取额度</button>
        <?php else:?>
        <button class="big345-button rz-filed" style="opacity: 0.3;" >重新获取额度</button>
        <?php endif;?>
    <?php elseif($isShow == 2):?>
         <?php if($isImprove):?>
        <button class="big345-button rz-filed" onclick="do_improve()">立即加速</button>
        <?php else:?>
        <button class="big345-button rz-filed" style="opacity: 0.3;" >立即加速</button>
        <?php endif;?>
    <?php endif;?>
    </div>
    <!--    黑名单弹窗-->
<div class="poppay_mask" id="toast"  hidden></div>
<div class="mask_box" id="toast_tixian_fail" hidden   >
    <p class="mask_title" style="">你暂时不能操作此功能</p>  
    <span class="add_btn" onclick="black_to()" style="">确定</span>
</div>

    
    <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
<script>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
    var shop_url = '<?php echo $shop_url; ?>';
    var shop_reback_url = '<?php echo $shop_reback_url; ?>';
    var black_user = '<?php echo $black_user;?>';
    $(function(){            
        //重写返回按钮
        pushHistory();
        var bool=false;
        setTimeout(function(){
            bool=true;
        },1500);
        window.addEventListener("popstate", function(e) {
        tongji('selectioninfo_list_reback_btn',baseInfoss);
        if(bool){
           //根据自己的需求返回到不同页面
            setTimeout(function(){
                if(shop_reback_url){
                    window.location.href= shop_reback_url;
                    return false;
                }
                 window.location.href= '/borrow/loan';
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
        if(black_user){
            $('#toast').show();
            $('#toast_tixian_fail').show();
        }
    });
    //黑名单跳转
    function black_to(){
        if(shop_reback_url){
            window.location.href = shop_reback_url;
            return false;
        }
        window.location.href = '/borrow/loan';
        return false;
    }
    
    //去信用卡认证
    function do_card_valid(){
            tongji('selectioninfo_do_cardvalid',baseInfoss);
              setTimeout(function(){
                   window.location.href = '/borrow/userauth/card?type=2';//type：1从必填资料项去填写信用卡认证 2：从选填资料
              },100);
        
    }
    var csrf = '<?php echo $csrf; ?>';
    //1学历、2社保、3公积金 4京东 6淘宝 7银行卡认证
    function toAuth(userId, type, obj) {
        console.log(type);
        tongji('selectioninfo_do_'+type,baseInfoss);
        if ($(obj).hasClass('lock')) {
            return false;
        }
        $(obj).addClass("lock");
           setTimeout(function(){
            $.ajax({
            type: "POST",
            url: "/borrow/selection",
            data: {_csrf: csrf, user_id: userId, type: type},
            datatype: "json",
            async: true,
            success: function (data) {
                data = eval('(' + data + ')');
                if (data.code == 1) {
                    setTimeout(function () {
                        window.location.href = data.data;
                    }, 100);
                    } else {
                        $(obj).removeClass('lock');
                        alert(data.msg);
                    }
                }
            });
           },100);

    }
    
    //加速
    function do_improve(){
         tongji('selectioninfo_do_imporive',baseInfoss);
              setTimeout(function(){
                  if(shop_url){
                      window.location.href = shop_url;
                      return false;
                  }else{
                      window.location.href = '/borrow/loan';
                  }
              },100);         
    }
    
    //重新获取额度
    function do_re_credit(){
        if($('#dorecredit').hasClass('dis')){
              return false;
        }
        $('#dorecredit').addClass('dis');
        tongji('selectioninfo_do_recredit',baseInfoss);
        setTimeout(function(){
                getcanloan();
         },100);
    }
    function getcanloan(){
        $.ajax({
            url: "/borrow/loan/getcanloan",
            type: 'post',
            async: false,
            data: {_csrf: csrf,type:1},
            success: function (json) {
                json = eval('(' + json + ')');
                console.log(json);
                if (json.rsp_code == '0000') {
                    if (json.is_change == 1) { //有待完善必填资料 跳转到认证页
                        window.location.href = '/borrow/userinfo/requireinfo';
                    } else if (json.is_change == 2) { //跳转到额度审核中页面
                        if(json.shop_mark == 1){ //跳到先花商城页面
                            window.location.href = json.shop_url;
                            return false;
                        }
                        window.location.href = '/borrow/loan';
                    }
                } else {
                    alert(json.rsp_msg);
                }
            },
            error: function (json) {
                alert('请十分钟后发起评测');
            }
        });
    }
</script>

