<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>视频认证</title>
    <link rel="stylesheet" href="/borrow/310/css/reset.css">
    <link rel="stylesheet" href="/borrow/310/css/yStyle.css">
    <script src="/290/js/jquery-1.10.1.min.js"></script>
</head>

<body>
    <div class="y-vertify">
        <section class="process-bar">
            <div class="prcess"></div>
            <!-- 进度条控制css样式.process-bar .prcess 的width属性20%,40%,60%....100% -->
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </section>
        <img class="y-vertify-icon" src="/borrow/310/images/spsc.png" alt="">
        <p class="y-vertify-tips">视频上传中</p>
    </div>
    <div>
        <form enctype="multipart/form-data" id="supplyForm" action="<?php echo $request_url ?>/soupfile/filevideo" method="post">
<!--            <div class="video_sel" hidden>
                <p class="tip">请拍摄3至5秒本人正脸短视频</p>
                <img src="/borrow/310/images/video_auto.png">
                <input id="file" class="file" type="file" accept="video/*" capture="camera" name="files">
                <div class="progress_btn ok_btn">拍摄</div>
                <span>视频认证每天认证上限为5次！</span>
            </div>-->
            <input type="hidden" name="aid" value="<?php echo $aid;?>">
            <input type="hidden" name="callbackurl" value="<?php echo $callbackurl ?>">
            <input id="_csrf" class="csrf" name="_csrf" type="hidden" value="<?php echo $csrf; ?>">
            <input class="request_id" name="requestid" type="hidden" value="<?php echo $requestid;?>">
            <input type="hidden" name="user_id" class="user_id" value="<?php echo $user_id; ?>"/>
        </form>
    </div>
</body>
</html>
<script>
    var csrf = '<?php echo $csrf; ?>';
    var aid = '<?php echo $aid?>';
    var callbackurl = '<?php echo $callbackurl?>';
    var requestid = '<?php echo $requestid?>';
    var user_id = '<?php echo $user_id?>';
    
    $(function(){
          setTimeout(function () {
              $("#supplyForm").submit();
          }, 5000);
    });
  
</script>