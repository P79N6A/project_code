<?php
//$uploadurl = (new \app\commonapi\ImageHandler())->img_upload_url;
$uploadurl = 'http://upload.7dle.com';
?>
<style>
    *{
        margin: 0;
        padding: 0;
    }
    body{
        background: #e4e4e4;
        width: 100%;
        height: 100%;
    }
    .w_uploadBox{
        width: 100%;
        height: 100%;
        background: #fff;
    }
    .w_title{
        height: 40px;
        width: 96%;
        background: #e4e4e4;
        font: 16px/40px "微软雅黑";
        color: #444;
        padding-left: 4%;
    }
    .w_disText{
        height: 56px;
        width: 92%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font: 16px/56px "微软雅黑";
        color: #444;
        padding: 0 4%;
        border-bottom: 1px solid #e4e4e4;
    }
    .w_disText:nth-of-type(3){
        border: none;
    }
    .w_uploadCont{
        width: 96%;
        height: 160px;
        padding-top: 20px;
        padding-left: 4%;
    }
    .w_uploadContBox{
        height: 106px;
        width: 100%;
        display: flex;
        justify-content: flex-start;
        position: relative;
    }
    .uploadBox{
        height: 104px;
        width: 104px;
        background:url('./uploadBg.png') center no-repeat;
        border: 1px solid #D3D3D3;
        border-radius: 8px;
        background-size: 100%;
        margin-right: 14px;
    }
    .uploadText{
        height: 34px;
        width: 335px;
        margin-top: 10px;
        font-family: "微软雅黑";
        font-size: 12px;
        line-height: 16px;
        color: #999999;
    }
    .w_submitBtn{
        margin: 50px auto;
        width: 345px;
        height: 48px;
        background-image: linear-gradient(-90deg, #FC8900 0%, #FE5300 100%);
        border-radius: 100px;
        font-family: PingFangSC-Regular;
        font-size: 18px;
        line-height: 48px;
        text-align:center;
        color: #FFFFFF;
    }
    .file{
        width: 100%;
        height: 104px;
        opacity: 0;
    }
    .uploadImgBox{
        width: 100%;
        height: 100%;
        position: relative;
    }
    .uploadImg{
        width: 100%;
        height: 100%;
    }
    .uploadDelete{
        height: 20px;
        width: 20px;
        position: absolute;
        right: 4px;
        top: 4px;
    }
    .jirtu1{
        height:106px;
        width:92%;
        padding:15px 4% 0;
        justify-content:center;
        display:flex;
    }
    .jirtu1>div{
        height:104px;
        width:104px;
        margin-right:14px;
    </style>
</style>
<div class="w_uploadBox">
    <div class="w_title">通过支付宝汇款给七天乐</div>
    <div class="w_disText">
        <span>待还金额</span>
        <span>500.00元</span>
    </div>
    <div class="w_disText">
        <span>户名</span>
        <span>萍乡海桐技术服务外包有限公司</span>
    </div>
    <div class="w_disText">
        <span>支付宝账号</span>
        <span>pxhthk@xianhuahua.com</span>
        <span style="color: #FD5500;" id="apply_user_orzer">复制</span>
    </div>


    <form id="upload" enctype="multipart/form-data" action="/day/repay/repaysave" method="post"> 
        <input type="hidden" name="loan_id" value="<?php echo $user_loan->loan_id; ?>">
        <input type="hidden" name="_csrf" value="<?php echo Yii::$app->request->getCsrfToken(); ?>">
        <div class="jirtu1">
            <div id="firstd">
                <img id="supply1" src="/images/dev/10002.png" width="100%;height:100%">
                <input  type="hidden" id="supply1Url" name="supplyUrl[1]" value=""/>
            </div>
            <div id="photo_2" style="display: none;">
                <img id="supply2" src="/images/dev/10002.png" width="100%;height:100%">
                <input  type="hidden" id="supply2Url" name="supplyUrl[2]" value=""/>
            </div>
            <div id="photo_3" style="display: none;">
                <img id="supply3" src="/images/dev/10002.png" width="100%;height:100%">
                <input  type="hidden" id="supply3Url" name="supplyUrl[3]" value=""/>
            </div>
        </div>
        <div class="w_submitBtn" id="repay1t">提交还款凭证</div>
    </form>
</div>

<div  class="tishi_success" ><a id="reg_one_error" style="display: none">登录成功</a></div>

    <script src="/js/upload.js"></script>
    <script src="https://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script src="/js/upload/imgupload7d.js?m=v7" type="text/javascript"></script>
    <script src="/js/upload/resizeimg/dist/lrz.bundle.js" type="text/javascript"></script>
    <script src="/js/clipboard.min.js?v=10001" type="text/javascript"></script>
    <script>
        var showErr = function (id, msg) {
            $("#submit-err").html(msg);
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
            var pic1 = $('input[name="supplyUrl[1]"]').val();
            if (pic1.length == 0) {

                $('#reg_one_error').show();
                $("#reg_one_error").html('请上传图片！');
                setTimeout(function () {
                    $("#reg_one_error").hide();
                    $('#reg_one_error').text('');
                }, 1000);
                return false;
            }
            $("#upload").submit();
        }

        var oUpload = new ImageUpload({
            "formid": "uploadImgForm",
            'action': "<?= $uploadurl ?>/upload",
            "encrypt": "<?= $encrypt ?>",
            "error": showErr,
            'afterSave': fnAfter,
            'onupload': function () {
                $("#repay1t").html("正在上传中");
            }
        });
        $(function () {
            var i, img, id, url;
            for (var i = 1; i <= 3; i++) {
                img = 'supply' + i;
                id = document.getElementById(img + 'Url').value;
                // 只可添加,不可修改
                if (!id) {
                    shows(img, i);
                }
            }
            // 图片上传绑定
            $("#repay1t").click(function () {
                var photo = $("#photo_2").css('display');
                if (photo == 'none') {
                    $('#reg_one_error').show();
                    $("#reg_one_error").html('请上传图片！');
                    setTimeout(function () {
                        $("#reg_one_error").hide();
                        $('#reg_one_error').text('');
                    }, 1000);
                    return false;
                }
                setTimeout(function () {
                }, 100);
                oUpload.save();
            });
        });
        var shows = function (img, i) {
            url = document.getElementById(img + 'Url').value;
            oUpload.add(img, url, function (id, rst) {
                document.getElementById(id).src = rst.base64;
                if (i == 1) {
                    $('#firstd').removeClass('col-xs-offset-4');
                }
                j = i + 1;
                $("#photo_" + j).show();
            });
        }

        //复制
        var clipboard = new Clipboard('#apply_user_orzer', {
            text: function () {
                $('#reg_one_error').show();
                $("#reg_one_error").text('复制成功！');
                setTimeout(function () {
                    $("#reg_one_error").hide();
                    $('#reg_one_error').text('');
                }, 1000);
                return "pxhthk@xianhuahua.com";
            }
        });
        clipboard.on('success', function (e) {
        });


        function doHelp(url) {
            setTimeout(function () {
                window.location.href = url;
            }, 100);
        }
    </script>