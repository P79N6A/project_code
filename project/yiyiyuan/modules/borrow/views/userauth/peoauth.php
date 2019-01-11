<?php

use app\commonapi\ImageHandler;
use yii\helpers\Url;

$uploadurl = (new ImageHandler)->img_upload_url;
?>

<div class="work">

    <span class="tip_1">请上传</span>
    <p class="tip_2"><?php echo $pictype->title?></p>
    <form target="_self" id="supplyForm" action="/new/userauth/picsave" method="post">
        <input id="_csrf" name="_csrf" type="hidden" value="<?php echo $csrf; ?>">
        <input name="orderinfo" type="hidden" value="<?php echo $orderinfo ?>">
        <input type="hidden" name="user_id" value="<?php echo $userinfo->user_id; ?>"/>
        <input type="hidden" name="serverid" value="" id="reg_serverid">
        <input type="hidden" name="user_id" value="<?php echo $userinfo['user_id']; ?>"/>
        <input type="hidden" name="pic_type" id="reg_pic_type" value="<?php echo $pictype['id']; ?>">
        <div class="upload_select">
            <img id="upload_bg" src="/borrow/310/images/add_icon.png">

            <input type="file" id="file" accept="image/*" capture="camera">
        </div>
        <span  class="cardUpload">
                        <?php
                        $path = isset($imgList[0]) ? $imgList[0]['img'] : '';
                        $url = $path ? ImageHandler::getUrl($path) : $imgDefault;
                        ?>
            <input  type="hidden" id="supply1Url" name="supplyUrl[1]" value="<?= $path ?>"/>
                    </span>
        <div class="module">
            <p>示例图片</p>
            <img src="<?php echo $pictype->pic; ?>">
        </div>
        <input type="button" id="btok" value="开始认证" class="progress_btn ok_btn" disabled>
    </form>
</div>
<!--  <div class="progress_btn ok_btn"  id="btok">开始认证</div>-->

<div class="help upload_help" >
    <img src="/borrow/310/images/help_deng.png" >
    <a style="color: #3D81FF; text-decoration:none;" href="javascript:void(0);" onclick="doHelp('/borrow/helpcenter/list?position=3&user_id=<?php echo $userinfo->user_id;?>')"><span class="contact_service_text">获取帮助</span></a>
</div>

<script src="http://upload.yaoyuefu.com/js/resizeimg/dist/lrz.bundle.js" type="text/javascript"></script>
<script src="/290/js/jquery-1.10.1.min.js"></script>
<script src="/js/upload/imgupload.js?m=v10" type="text/javascript"></script>
<script type="text/javascript">

    $(function () {
        /**
         * 上传图片前操作
         */
        ImageUpload.prototype.beforeSave=function(){
            return true;
        }
        var fnAfter = function (data) {
            // 验证回调结果
            var ok = data && parseInt(data.res_code, 10) === 0;
            if (!ok) {
                showErr(data.res_code, data.res_data);
                return null;
            }
            // 写入到本地表单中
            var urls = data.res_data;
            for (var id in urls) {
                $("#" + id + 'Url').val(urls[id]);
            }

            $("#btok").attr('disabled',false);
        }

        var showErr = function (id, msg) {
            $("#submit-err").html(msg);
        }

        var oUpload = new ImageUpload({
            "formid": "uploadImgForm",
            'action': "<?= $uploadurl ?>/upload",
            "encrypt": "<?= $encrypt ?>",
            "error": showErr,
            'afterSave': fnAfter,
            'onupload': function () {
                $("#btok").val("正在上传中");
            }
    });
        $('#file').on('change', function () {
            var file = $('#file')[0].files[0];
            var reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function (e) {
                $('#upload_bg').attr('src', e.target.result).css({
                    width: '100%',
                    height: '100%',
                    borderRadius: '0.53rem'
                });
                createForm(file, e);
            };
        });
        function createForm(file, e) {
            var html = '<div id="supply1_group"><input id="supply1_url" name="supply1[url]" type="hidden" value=""><input id="supply1_base64" name="supply1[base64]" type="hidden" value="' + e.target.result + '"><input id="supply1_file" name="supply1[file]" type="file" style="display:none;"></div>';
            // $("#supply1_group").remove();
            $("#uploadImgForm").append(html);
            oUpload.save();
        }
        $('#btok').click(function () {
            var file = $('#file')[0].files[0];
            if(!file){
                alert('请上传自拍照');return false;
            }
            $("#supplyForm").submit();
        })
    });
     function doHelp(url) {
        setTimeout(function(){
            window.location.href = url;
        },100);
    }
</script>
