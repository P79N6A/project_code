<?php

use app\commonapi\ImageHandler;

$uploadurl = ImageHandler::$img_upload;
$imgurl    = ImageHandler::$img_domain;
?>
<div class="shiprzg">
    <img class="success" src="/h5/images/success.png">
    <p class="ysccg">已上传成功</p>
    <?php if(in_array($videoInfo->video_auth_status,[1,3])):?>
        <img class="imgtu" src="<?php echo $imgurl.$videoInfo->image_url?>">
    <?php else:; ?>
        <img class="imgtu" src="/h5/images/zhanwei.png">
    <?php endif; ?>
</div>
<div class="buttonyi supply1"> <button>下一步</button></div>
<p class="sprzg">视频认证每天认证上限为5次哦！</p>

<script type="text/javascript">
    $(function(){
        $('.supply1').click(function(){
            window.location.href = '/new/loan';
        });
    });
</script>
