<div class="wraper">
    <section class="process-bar">
        <div class="prcess" style="width: 60%"></div>
        <!-- 进度条控制css样式.process-bar .prcess 的width属性20%,40%,60%....100% -->
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </section>
    <!-- 视频认证6、8 -->
    <section class="rz-one">
      <img src="/borrow/310/images/rz-faile.png" alt="">
      <p class="rz-title">认证失败</p>
      <p class="rz-failed-txt">请拍摄<font style="color: #F33737;">清晰的正脸</font>视频</p>
  </section>

   <form enctype="multipart/form-data" id="supplyForm" action="<?php echo $request_url ?>/soupfile/filevideo" method="post">
       <input type="hidden" name="aid" value="10">
       <input type="hidden" name="callbackurl" value="<?php echo $callBackUrl ?>">
       <input id="_csrf" class="csrf" name="_csrf" type="hidden" value="<?php echo $csrf; ?>">
       <input class="request_id" name="requestid" type="hidden" value="">
       <input type="hidden" name="user_id" class="user_id" value="<?php echo $userinfo->user_id; ?>"/>
        <div style="position: relative">
            <input style="
            width: 9.20rem;
            height: 1.28rem;
            position: absolute;
            margin-left: 0.4rem;
            z-index: 9;
            opacity: 0;"
                   id="file" class="file" type="file" accept="video/*" capture="camera" name="files" >
        </div>
   </form>
            <button class="big345-button rz-filed">重新认证</button>
       <a href="/borrow/userauth/peo-auth">
           <div class="rz-filed-conte"  style="display: none" id="people"><span class="rz-failed-confim">人工认证</span>
       </a>
    <p class="rz-failed-smtxt">为保证顺利借款，我们还提供人工认证方式</p></div>

    
</div>

<div class="help" style="position: absolute;bottom: 0.5rem;text-align: center;width: 100%;">
    <img src="/borrow/310/images/help_deng.png" style="width: .4rem;position: relative;top: .07rem;margin-right: .1rem;">
    <a style="color: #3D81FF; text-decoration:none;" href="javascript:void(0);" onclick="doHelp('/borrow/helpcenter/list?position=3&user_id=<?php echo $userinfo->user_id;?>')"><span class="contact_service_text">获取帮助</span></a>
</div>
<script src="/news/js/jquery-1.10.1.min.js"></script>
<script type="text/javascript">
    canClick = true;
    var user_id = '<?php echo $userinfo->user_id; ?>';
    $(function () {
        if(!canClick){
            return false;
        }
        canClick = false;
        var times = "<?php echo $times; ?>";
        var peoauth = '无';
        var picauth = '有';
        if(times >= 3){
            $("#people").show();
            peoauth = '有';
        }
        if(times >= 5){
           picauth = '无';
        }
        $(document).on('click','#file',function () {
            zhuge.track('视频认证失败页面-重新拍摄');
            if(times >= 5){
                alert('认证次数达到上限：您的认证次数已达上限，请使用人工审核方式或30天后重试');
                return false;
            }
        })

        $("#file").off().change(function () {
            var file_size = Math.ceil(this.files[0].size / 1024 / 1024);
            if(file_size < 16 && file_size > 0){
                videosave();
            }else {
                    canClick = true;
                    alert("当前视频大小" + file_size + "MB>16MB，请重新录制3-5s视频");
                    window.location='/borrow/userauth/uploadfailure';
                    return false;
            }

        });
        
        //诸葛埋点-视频认证失败页面PV/UV
        zhuge.track('视频认证失败页面', {
            '用户ID': user_id,
            '重新拍摄': picauth,
            '去人工审核': peoauth,
        });
    });
    
    $("#people").click(function(){
        zhuge.track('视频认证失败页面-去人工审核');
    });
    function doHelp(url) {
        setTimeout(function(){
            window.location.href = url;
        },100);
    }
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
