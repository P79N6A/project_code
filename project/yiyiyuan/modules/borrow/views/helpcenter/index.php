<div class="wraper">
    <div class="top-title" style="box-sizing: border-box">问题分类</div>
        <div class="card-cont">
            <div class="card-styl card-ziliao" onclick="category_click(1)">填写资料</div>
            <div class="card-styl card-jiekuan" onclick="category_click(2)" >借款相关</div>
            <div class="card-styl card-huankuan" onclick="category_click(3)" >还款相关</div>
            <div class="card-styl card-cunguan" onclick="category_click(4)" >存管账户</div>
            <div class="card-styl card-yuqi" onclick="category_click(5)" >逾期相关</div>
            <div class="card-styl card-xuqi" onclick="category_click(7)" >其他问题</div>
        </div>
        <div class="btom-title">猜您遇到以下问题</div>
        <div class="hp-lic-con">
            <?php foreach ($list as $key => $val ):?>
            <?php if( !empty($val->helpCenterList)):?>
            <p class="ellipsis" onclick="doUrl('/borrow/helpcenter/detail?help_id=<?php echo $val->help_id;?>&user_id=<?php echo $user_id;?>',<?php echo $val->help_id;?>)"><?php echo !empty($val->helpCenterList) ? $val->helpCenterList->title : '';?></p>
            <div class="hp-lic-firstline"></div>
            <?php endif;?>
            <?php endforeach;?>
        </div>
        <div class="hp-lic-footer">
            <img src="/borrow/310/images/gzh-ss.png" alt="">
            <span>关注官方公众号，获取更多福利资讯</span>
        </div>
        <div style="height: 1.6rem"></div>
        <div class="hp-foot-banner">
            <span onclick="customer_service()">联系客服</span>
            <span onclick="advise()" class="tj">投诉建议</span>
        </div>
</div>
<script>
    var user_id = '<?php echo $user_id;?>';
    var customer_url = '<?php echo $customer_service;?>';
    var advise_url = '<?php echo $advise_url;?>';
    console.log(advise_url);
    <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
    
    function category_click(type){
        tongji('do_help_list_'+type,baseInfoss);
        setTimeout(function(){
            window.location.href = '/borrow/helpcenter/list?category='+type;
        },100);
    }
    function customer_service(){
        tongji('do_service',baseInfoss);
        setTimeout(function(){
            window.location.href = customer_url;
        },100);
    }
    function advise(){
        tongji('do_advise',baseInfoss);
        setTimeout(function(){
            window.location.href = advise_url;
        },100);
    }
    function doUrl(url,help_id) {
        tongji('do_help_page_'+help_id,baseInfoss);
        setTimeout(function(){
            window.location.href = url;
        },100);
    }
</script>    