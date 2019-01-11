<?php

use app\commonapi\ImageHandler;
use yii\helpers\Url;

$uploadurl = ImageHandler::$img_upload;
?>
<style>
    .process-bar {
    background: red;
}

.process-bar .prcess {
    position: absolute;
    display: inline-block;
    background-image: linear-gradient(-90deg, #FF4B17 0%, #F00D0D 100%);
    height: 4px;
    width: 60%;
    z-index: 9;
}
.process-bar span {
    position: absolute;
    width: 2px;
    height: 15px;
    margin-top: -5px;
    z-index: 9
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
<!-- 进度条 -->
<section class="process-bar">
    <div class="prcess"></div>
    <!-- 进度条控制css样式.process-bar .prcess 的width属性20%,40%,60%....100% -->
    <span></span>
    <span></span>
    <span></span>
    <span></span>
</section>
<form enctype="multipart/form-data" id="supplyForm" action="<?php echo $request_url ?>/soupfile/filevideo" method="post">
    <div class="video_sel">
        <p class="tip">请拍摄3至5秒本人正脸短视频</p>
        <img src="/borrow/310/images/video_auto.png">
        <input id="file" class="file" type="file" accept="video/*" capture="camera" name="files">
        <div class="progress_btn ok_btn"  >拍摄</div>
        <span>视频认证每天认证上限为5次！</span>
    </div>
    <input type="hidden" name="aid" value="10">
    <input type="hidden" name="callbackurl" value="<?php echo $callBackUrl ?>">
    <input id="_csrf" class="csrf" name="_csrf" type="hidden" value="<?php echo $csrf; ?>">
    <input class="request_id" name="requestid" type="hidden" value="">
    <input type="hidden" name="user_id" class="user_id" value="<?php echo $userinfo->user_id; ?>"/>
</form>
<p class="video_warn">注意事项</p>
<div class="video_warn_list">
    <div class="list">
        <img src="/borrow/310/images/pro_1.png">
        <span>保持光线良好</span>
    </div>
    <div class="list">
        <img src="/borrow/310/images/pro_2.png">
        <span>保持正脸拍摄</span>
    </div>
    <div class="list">
        <img src="/borrow/310/images/pro_3.png">
        <span>不能带眼镜</span>
    </div>
    <div class="list">
        <img src="/borrow/310/images/pro_4.png">
        <span>时长3至5秒</span>
    </div>
</div>
<p class="error_fn">
    <a href="/borrow/helpcenter/list?position=5&user_id=<?php echo $userinfo->user_id;?>">认证失败5次怎么办？</a>
</p>
<div id='backSuccess' hidden class="jiebangcg">认证次数达到上限：您的认证次数已达上限，请使用人工审核方式或30天后重试</div>
<script src="/js/imgupload.js?m=v7" type="text/javascript"></script>
<script src="http://upload.yaoyuefu.com/js/resizeimg/dist/lrz.bundle.js" type="text/javascript"></script>
<script src="http://upload.yaoyuefu.com/js/resizeimg/dist/lrz.bundle.js" type="text/javascript"></script>
<script type="text/javascript">

    canClick = true;
    $(function () {
        $(document).on('click','#file',function () {
            zhuge.track('视频认证首页-拍摄按钮');
        });
        $("#file").off().change(function () {
            
            var file_size = Math.ceil(this.files[0].size / 1024 / 1024);
            
            if (file_size >= 16) {
                canClick = true;
                alert("当前视频大小" + file_size + "MB>16MB，请重新录制3-5s视频");
                window.location='/borrow/userauth/uploadfailure';
                return false;
            }
            if (file_size > 0) {
                videosave();
            }
        });
    });
    
    var videosave = function () {
        var url = '/new/userauth/videosave';
        var csrf = '<?php echo $csrf; ?>';
        var user_id = '<?php echo $userinfo->user_id; ?>';
        $.ajax({
            type: 'POST',
            url: url,
            data: {'user_id': user_id, '_csrf': csrf, 'url': url},
            dataType: 'json',
            success: function (json) {
                canClick = true;
                if (json.res_code == '0000') {
                    zhuge.identify(user_id, {
                        '视频信息已认证': 1,  // 0表示false，1表示ture，下同
                    });
                    $(".request_id").val(json.res_data.request_id);
                    $("#supplyForm").submit();
                } else {
                    canClick = true;
                    alert('网络错误');
                    return false;
                }
            },
            error: function () {
                canClick = true;
                alert('网络错误');
                return false;
            }
        });
    };


    var getstatus = function () {
        var url = '/new/userauth/getstatus';
        var csrf = '<?php echo $csrf; ?>';
        var user_id = '<?php echo $userinfo->user_id; ?>';
        $.ajax({
            type: 'POST',
            url: url,
            data: {'user_id': user_id, '_csrf': csrf, 'url': url},
            dataType: 'json',
            success: function (json) {
                canClick = true;
                if (json.res_code == '0000') {
                    $("input[name='request_id']").val(json.res_data.request_id);
                    $("#supplyForm").submit();
                } else {
                    $("#backError").show();
                    hideDiv('backError');
                    return false;
                }
            },
            error: function () {
                canClick = true;
                $("#backError").show();
                hideDiv('backError');
                return false;
            }
        });
    };

    //2秒隐藏上传成功提示框
    function hideDiv(id)
    {
        var obj = $("#" + id);
        setTimeout(function () {
            obj.hide();
        }, 2000);
    }
</script>