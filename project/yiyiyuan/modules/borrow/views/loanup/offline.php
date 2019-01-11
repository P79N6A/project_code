<?php
if ($source == '2') {
    $uploadurl = 'http://upload.xianhuahua.com';
} else {
    $uploadurl = (new \app\commonapi\ImageHandler())->img_upload_url;
}
\app\commonapi\Logger::dayLog('upload', $source, $user_id);
?>
<style>
    .jiebangcg { position: fixed;top: 28%;color: #fff; z-index: 15; border-radius: 5px; text-align: center;background: rgba(0,0,0,0.8); padding: 15px 0px;width: 50%; margin: 0 25%;}
    .jiebangcg.xtfmang{width: 60%; margin: 0 20%;}
</style>
<p class="hint_text">截图您的消费记录给先花一亿元</p>
<form id="form_repay" name="form_repay" action="/borrow/loanup/repaysave" method="POST" enctype="multipart/form-data" >
    <input type="hidden" name="loan_id" value="<?php echo $loan_id; ?>">
    <input type="hidden" name="_csrf" value="<?php echo Yii::$app->request->getCsrfToken(); ?>">
    <div class="buy_hkuan">
        <div class="hkuan_cont">
            <p class="left">借款金额</p>
            <p class="right">¥<?php echo sprintf('%.2f', $loaninfo->amount); ?></p>
        </div>
        <div class="jirtu1">
            <div id="firstd" onclick="doUploadRepayImg()">
                <img id="supply1" src="/images/dev/10002.png" width="100%">
                <input  type="hidden" id="supply1Url" name="supplyUrl[1]" value="" accept="image/*" />
            </div>
            <div id="photo_2" style="display: none;" onclick="doUploadRepayImg()">
                <img id="supply2" src="/images/dev/10002.png" width="100%">
                <input  type="hidden" id="supply2Url" name="supplyUrl[2]" value="" accept="image/*" />
            </div>
            <div id="photo_3" style="display: none;" onclick="doUploadRepayImg()">
                <img id="supply3" src="/images/dev/10002.png" width="100%">
                <input  type="hidden" id="supply3Url" name="supplyUrl[3]" value="" accept="image/*" />
            </div>
        </div>
        <p class="zhifjtu">消费凭证截图应有收货方姓名、消费金额、消费时间等详细完整的订单信息</p>
    </div>


    <button type='button' id="repay1t">提交凭证</button>
</form>
<style>
    .help_service{
        /*position: absolute;*/
        width: 100%;
        height: 0.37rem;
        text-align: center;
        margin-top: 1rem;
        margin-bottom: .3rem;
    }
    .contact_service_tip{
        width: 0.40rem;
        height: 0.43rem;
        position: absolute;
        left: 3.97rem;
        top: auto;
    }
    .contact_service_text{
        height: 0.37rem;
        position: absolute;
        left:4.59rem;
        font-family: "微软雅黑";
        font-size: 0.37rem;
        color: #3D81FF;
        letter-spacing: 0;
        line-height: 0.43rem;
    }
    button{
        border: none;
    }
</style>
<div id="xtfmang" class="jiebangcg xtfmang" style="display: none;"></div>
<script src="/js/upload.js"></script>
<script src="https://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<?php if ($source == '2'): ?>
    <script src="/js/upload/imguploadapp.js?m=vddd7" type="text/javascript"></script>
<?php else : ?>
    <script src="/js/upload/imgupload.js?m=vddd7" type="text/javascript"></script>
<?php endif; ?>
<script src="/js/upload/resizeimg/dist/lrz.bundle.js" type="text/javascript"></script>
<script src="/js/clipboard.min.js?v=10001" type="text/javascript"></script>
<script>
<?php \app\common\PLogger::getInstance('weixin', '', $user_id); ?>
<?php $json_data = \app\common\PLogger::getJson(); ?>
                var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');
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
                    $("#form_repay").submit();
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
                        tongji('do_repay_submit', baseInfoss);
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
                        tongji('copy_accounts', baseInfoss);
                        $('#xtfmang').show();
                        $("#xtfmang").text('复制成功！');
                        setTimeout(function () {
                            $("#xtfmang").hide();
                            $('#xtfmang').text('');
                        }, 1000);
                        return "<?php echo \app\commonapi\Keywords::getAlipayInfo()['account']; ?>";
                    }
                });
                clipboard.on('success', function (e) {
                });

                //微信配置
                wx.config({
                    debug: false,
                    appId: '<?php echo $jsinfo['appid']; ?>',
                    timestamp: <?php echo $jsinfo['timestamp']; ?>,
                    nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
                    signature: '<?php echo $jsinfo['signature']; ?>',
                    jsApiList: [
                        'hideOptionMenu'
                    ]
                });

                wx.ready(function () {
                    wx.hideOptionMenu();
                });

                function doUploadRepayImg() {
                    tongji('do_upload_repay_img', baseInfoss);
                }

                function doHelp(url) {
                    tongji('do_help', baseInfoss);
                    setTimeout(function () {
                        window.location.href = url;
                    }, 100);
                }
</script>