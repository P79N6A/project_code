<style>
    .useful::before{
        background-image:url("/borrow/310/images/zan-able-n.png") !important;
    }
    .is_useful::before{
        background-image:url("/borrow/310/images/zan-able.png") !important;
    }
    .useless::before{
        background-image:url("/borrow/310/images/zan-unable@2x.png") !important;
    }
    .is_useless::before{
        background-image:url("/borrow/310/images/zan-unable.png") !important;
    }
</style>

<div class="wraper" style="min-height: auto;">
    <div class="hp-content">
        <p class="hp-con-title">Q:<?php echo $help_info->title;?></p>
        <div class="hp-bg"></div>
        <p class="hp-con-txt"><?php echo $help_info->contact;?></p>
    </div>
    <div class="hp-con-footer">
        <span class="hpct-lf" >是否对您有用？</span>
        <input type="hidden" id="status_hidden" value="<?php echo $useful_or_useless;?>" />
        <span class="hpct-zan useful <?php if ($useful_or_useless == 1): ?>is_useful<?php endif; ?>" id="useful" onclick="useful_or_useless_click(1)" style="color:#999;">(<?php echo $help_info->useful_number?>)</span>
        <span class="hpct-zan-unable <?php if ($useful_or_useless == 2): ?>is_useless<?php endif; ?>" id="useless" onclick="useful_or_useless_click(2)">(<?php echo $help_info->useless_number?>)</span>
        
    </div>

</div>
<style>
    .hp-footer-txt{
        position: initial;
        margin: 1rem 0 0.3rem;
        text-align: center;
    }
</style>
<div class="hp-footer-txt">
    <img src="/borrow/310/images/deng@2x.png" alt="">
    <span onclick="customer_service()">未能解决您的问题，联系客服</span>
</div>
<script>
    var user_id = '<?php echo $user_id;?>';
    var customer_url = '<?php echo $customer_service;?>';
    var help_id = '<?php echo $help_info->id;?>';
    var csrf = '<?php echo $csrf;?>';
    <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
    function customer_service(){
        tongji('do_service',baseInfoss);
        setTimeout(function(){
            window.location.href = customer_url;
        },100);
    }
    function useful_or_useless_click(type){ //1:有用 2：无用
        if(type == 1){
            tongji('useful',baseInfoss);
            setTimeout(function(){},100);
        }else{
            tongji('useless',baseInfoss);
            setTimeout(function(){},100);
        }

        var useful_or_useless = $('#status_hidden').val();
        console.log('useful_or_useless:'+useful_or_useless);
        console.log('type:'+type);
        if(type == useful_or_useless){
            return false;
        }
        $.ajax({
            url: "/borrow/helpcenter/helpusefulclick",
            type: 'post',
            async: false,
            data: {_csrf: csrf,type:type,user_id:user_id,help_id:help_id},
            success: function (json) {
                json = eval('(' + json + ')');
                console.log(json);
                if (json.rsp_code == '0000') {
                   if(type == 1){ 
                       $('#status_hidden').val(1);
                       $('#useful').addClass('is_useful');
                       $('#useless').removeClass('is_useless');
                       $('#useless').addClass('useless');
                       $('#useful').html('('+json.useful_number+')');
                       $('#useless').html('('+json.useless_number+')');
                   }
                   if(type == 2){
                       $('#status_hidden').val(2);
                       $('#useful').removeClass('is_useful');
                       $('#useful').addClass('useful');
                       $('#useless').addClass('is_useless');
                       $('#useful').html('('+json.useful_number+')');
                       $('#useless').html('('+json.useless_number+')');
                   }
                } else if(json.rsp_code == '1001') {
                   console.log(json.rsp_msg);
                }else{
                     alert(json.rsp_msg);
                }
            },
            error: function (json) {
               console.log('请求出错');
            }
        });
    }
</script>    