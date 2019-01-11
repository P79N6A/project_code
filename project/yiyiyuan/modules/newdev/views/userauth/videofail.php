<?php

use app\commonapi\ImageHandler;

$uploadurl = ImageHandler::$img_upload;
$imgurl    = ImageHandler::$img_domain;
?>
<div class="shiprzg">
    <img class="success" src="/h5/images/false.png">
    <p class="ysccg">认证失败</p>
    <?php if(in_array($videoInfo->video_auth_status,[1,3])):?>
        <img class="imgtu" src="<?php echo $imgurl.$videoInfo->image_url?>">
    <?php else:; ?>
        <img class="imgtu" src="/h5/images/zhanwei.png">
    <?php endif; ?>
</div>
<form enctype="multipart/form-data" id="supplyForm" action="<?php echo $request_url?>/soupfile/filevideo" method="post">
    <div id="errormsg" style="color: red; font-size: 12px; margin-left: 5px;"></div>
    <div class="buttonyi supply1"> <button type="button">重新上传</button></div>
    <input type="file" style="visibility: hidden" capture="camcorder" accept="video/*" value="" id="upload" name="files" class="upinput"/>
    <input type="hidden" name="aid" value="10">
    <input type="hidden" name="callbackurl" value="<?php echo $callBackUrl?>">
    <p class="sprzg">视频认证每天认证上限为5次哦！</p>
    <input id="_csrf" class="csrf" name="_csrf" type="hidden" value="<?php echo $csrf; ?>">
    <input class="request_id" name="requestid" type="hidden" value="">
</form>
<div id='backSuccess' hidden class="jiebangcg">认证次数达到上限：您的认证次数已达上限，请使用人工审核方式或30天后重试</div>
<div id='backFail' hidden class="jiebangcg">您有认证中的视频，请稍后再试</div>
<div id='backError' hidden class="jiebangcg">网络错误</div>
<?php if($times>=3):?>
    <div style="position: fixed; bottom: 20px; width: 100%; text-align: center">
        <a href="/new/userauth/pic?type=2"><h3 style="color: #c90000; font-size: 14px;padding-bottom: 5px;">人工审核》</h3></a>
        <p style="color: #999;font-style: 12px;">为保证顺利借款，我们还提供人工审核方式</p>
    </div>
<?php endif; ?>
<script src="/js/upload/jquery-1.12.0.min.js" type="text/javascript"></script>
<script src="/js/upload/jquery.ui.widget.js" type="text/javascript"></script>
<script src="/js/upload/jquery.iframe-transport.js" type="text/javascript"></script>
<script src="/js/upload/jquery.fileupload.js" type="text/javascript"></script>
<script src="/js/upload/jquery.xdr-transport.js" type="text/javascript"></script>
<script src="http://upload.yaoyuefu.com/js/resizeimg/dist/lrz.bundle.js" type="text/javascript"></script>
<script type="text/javascript">
    canClick = true;
    $(function() {
        $(".supply1").click(function () {
            if(!canClick){
                return false;
            }
            canClick = false;
            var times = "<?php echo $times; ?>";
            if(times >= 5){
                $("#backSuccess").show();
                hideDiv('backSuccess');
                return false;
            }
            $("#upload").click();
            $("#upload").off().change(function(){
                var file_size = Math.ceil(this.files[0].size/1024/1024);
                if(file_size >= 16){
                    canClick = true;
                    $("#backError").html("当前视频大小"+file_size+"MB>16MB，请重新录制3-5s视频");
                    $("#backError").show();
                    hideDiv('backError');
                    return false;
                }
                videosave();
            });
        });
    });

    var videosave = function () {
        var url = '/new/userauth/videosave';
        var csrf = '<?php echo $csrf; ?>';
        var user_id = '<?php echo $userinfo->user_id; ?>';
        $.ajax({
            type: 'POST',
            url: url,
            data: {'user_id':user_id, '_csrf':csrf, 'url': url},
            dataType: 'json',
            success:function(json){
                if(json.res_code == '0000'){
                    $(".request_id").val(json.res_data.request_id);
                    $("#supplyForm").submit();
                }else{
                    canClick = true;
                    $("#backError").html(json.res_data);
                    $("#backError").show();
                    hideDiv('backError');
                    return false;
                }
            },
            error:function(){
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
