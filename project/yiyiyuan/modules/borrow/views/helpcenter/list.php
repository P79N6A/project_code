<style>
    html,body{
        overflow: auto;
    }
</style>
<div class="wraper">
    <div class="hp-lic-con">
        <?php if($position_or_category == 1):?>
            <?php foreach ($list as $key=>$val):?>
                <a href="javascript:void(0);" onclick="doUrl('/borrow/helpcenter/detail?help_id=<?php echo $val->id;?>&user_id=<?php echo $user_id;?>',<?php echo $val->id;?>);"><p class="ellipsis"><?php echo $val['title']?></p></a>
                <div class="hp-lic-firstline"></div>
            <?php endforeach;?>
        <?php elseif($position_or_category == 2):?>
            <?php foreach ($list as $key=>$val):?>
                <?php if( !empty($val->helpCenterList)):?>
                <a href="javascript:void(0);" onclick="doUrl('/borrow/helpcenter/detail?help_id=<?php echo $val->help_id;?>&user_id=<?php echo $user_id;?>',<?php echo $val->help_id;?>)"><p class="ellipsis"><?php echo !empty($val->helpCenterList) ? $val->helpCenterList->title : '';?></p></a>
                <div class="hp-lic-firstline"></div>
                <?php endif;?>
            <?php endforeach;?>
        <?php endif;?>
    </div>
    <div class="hp-lic-footer">
        <img src="/borrow/310/images/gzh-ss.png" alt="">
        <span>关注官方公众号，获取更多福利资讯</span>
    </div>
</div>
<script>
    <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
    function doUrl(url,help_id) {
        tongji('do_help_page_'+help_id,baseInfoss);
        setTimeout(function(){
            window.location.href = url;
        },100);
    }

</script>
