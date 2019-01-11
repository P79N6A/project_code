<div class="container">
    <div class="content text-center">
        <form enctype="multipart/form-data" id="supplyForm" action="<?php echo $request_url?>/soupfile/filevideo" method="post">
            <div class="shiprzg">
                <img class="sprg supply1" src="/h5/images/sprg.png"/>
                <input type="file" style="visibility: hidden" capture="camcorder" accept="video/*" value="" id="upload"  name="files" class="upinput" />
                <input name ="orderinfo" type = "hidden" value="<?php echo $orderinfo ?>">
                <input type="hidden" name="aid" value="10">
                <input type="hidden" name="callbackurl" value="<?php echo $callBackUrl?>">
                <p>请根据页面提示完成相关操作</p>
            </div>
            <div id="errormsg" style="color: red; font-size: 12px; margin-left: 5px;"></div>
            <div class="buttonyi supply1"> <button type="button">开始认证</button></div>
            <p class="sprzg">视频认证每天认证上限为5次哦！</p>
            <input id="_csrf" class="csrf" name="_csrf" type="hidden" value="<?php echo $csrf; ?>">
            <input class="request_id" name="requestid" type="hidden" value="">
        </form>
        <div id="errormsg" style="color: red; font-size: 12px; margin-left: 5px;"></div>
        <input type="hidden" name="user_id" class="user_id" value="<?php echo $userinfo->user_id; ?>"/>
    </div>
</div>
<div id='backSuccess' hidden class="jiebangcg">认证次数达到上限：您的认证次数已达上限，请使用人工审核方式或30天后重试</div>
<div id='backFail' hidden class="jiebangcg">您有认证中的视频，请稍后再试</div>
<div id='backError' hidden class="jiebangcg">网络错误</div>
<script src="/js/upload/jquery-1.12.0.min.js" type="text/javascript"></script>
<script src="/js/upload/jquery.ui.widget.js" type="text/javascript"></script>
<script src="/js/upload/jquery.iframe-transport.js" type="text/javascript"></script>
<script src="/js/upload/jquery.fileupload.js" type="text/javascript"></script>
<script src="/js/upload/jquery.xdr-transport.js" type="text/javascript"></script>
<script src="/js/upload/mobileBUGFix.mini.js" type="text/javascript"></script>
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
            $(".upinput").off().change(function(){
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
        var orderinfo = '<?php echo $orderinfo; ?>';
        $.ajax({
            type: 'POST',
            url: url,
            data: {'user_id':user_id, '_csrf':csrf, 'url': url, 'orderinfo':orderinfo},
            dataType:'json',
            success:function(json){
                canClick = true;
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


    var getstatus = function () {
        var url = '/new/userauth/getstatus';
        var csrf = '<?php echo $csrf; ?>';
        var user_id = '<?php echo $userinfo->user_id; ?>';
        $.ajax({
            type: 'POST',
            url: url,
            data: {'user_id':user_id, '_csrf':csrf, 'url': url},
            dataType:'json',
            success:function(json){
                canClick = true;
                if(json.res_code == '0000'){
                    $(".request_id").val(json.res_data.request_id);
                    $("#supplyForm").submit();
                }else{
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
