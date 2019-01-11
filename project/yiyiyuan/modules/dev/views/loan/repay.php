<?php

use app\commonapi\ImageHandler;

$uploadurl = ImageHandler::$img_upload;
?>
<div class="container">
    <form id="form_repay" name="form_repay" action="/new/repay/repaysave" method="POST" enctype="multipart/form-data" >
        <div >
            <ul class="way">
                <li style="margin:0;line-height: 24px;">1. 通过银行汇款给先花一亿元账户<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp;户&nbsp;&nbsp;&nbsp;名: 天津有信而立信息技术服务有限公司<br/>
                        支付宝账户: tjyxel@xianhuahua.com
                        <a href="javascript:void(0)" id="apply_user_orzer" style="display: inline-block;color: #c2c2c2;border: 1px solid #c2c2c2;border-radius: 8px;text-decoration: none; font-size: 14px; ">复制</a><br/>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="bd">待还款金额: <span class="red"><?php echo sprintf('%.2f', $loaninfo['huankuan_amount']); ?></span>元</span>
                </li>
            </ul>
            <div class="main">
                <p class="n22 red" style="padding-bottom:10px;">还款凭证截图内容应有收款方姓名，收款方账号，开户行，转账金额，转账时间等详细完整的信息 </p>
                <div class="row">
                    <div class="col-xs-4 col-xs-offset-4" id="firstd">
                        <div class="file-div">
                            <input  type="hidden" id="supply1Url" name="supplyUrl[1]" value=""/>
                            <img id="supply1" src="/images/dev/10002.png" width="100%" id="groupPhoto">
                        </div>
                        <p class="text-center mt10" id="groupPhotoDiv">添加还款凭证</p>
                    </div>

                    <div class="col-xs-4" style="display:none;" id="photo_2">
                        <div class="file-div">
                            <input  type="hidden" id="supply2Url" name="supplyUrl[2]" value=""/>
                            <img id="supply2" src="/images/dev/10002.png" width="100%" id="groupPhoto">
                        </div>
                        <p class="text-center mt10" id="groupPhoto1Div">继续添加</p>
                    </div>
                    <div class="col-xs-4" style="display:none;"  id="photo_3">
                        <div class="file-div">
                            <input  type="hidden" id="supply3Url" name="supplyUrl[3]" value=""/>
                            <img id="supply3" src="/images/dev/10002.png" width="100%" id="groupPhoto">
                        </div>
                        <p class="text-center mt10" id="groupPhoto2Div">继续添加</p>
                    </div>
                </div>
                <input type="hidden" name="loan_id" value="<?php echo $loan_id; ?>">
                <input type="hidden" name="_csrf" value="<?php echo Yii::$app->request->getCsrfToken(); ?>">
                <?php if ($now_time >= $start_time && $now_time <= $end_time): ?>
                    <div style="padding: 10px 5%;color:red;">由于春节期间（2月5日－2月15日）工作人员放假，还款订单将在2月15日被确认，敬请谅解</div>
                <?php endif; ?>
                <button class="btn mt40" id="repay1t" type='button' style="width:100%;">确定还款</button>
<!--                <div class="col-xs-12 text-right mt30"><a href="/dev/repay/cards?loan_id=<?php echo $loan_id; ?>">在线还款</a></div>-->
            </div>
    </form>
    <p id="submit-err" style="color:red;text-align:center;clear: both;"><?= $saveMsg ?></p>
</div>
</div>
<script src="/js/upload.js"></script>
<script src="https://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
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

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>


<script src="/js/upload/imgupload.js?m=v7" type="text/javascript"></script>
<script src="/js/upload/resizeimg/dist/lrz.bundle.js" type="text/javascript"></script>
<script src="/js/clipboard.min.js?v=10001" type="text/javascript"></script>
<!---复制功能 start--->
<script>
var clipboard = new Clipboard('#apply_user_orzer', {
    text: function() {
        return "tjyxel@xianhuahua.com";
    }
});
clipboard.on('success', function(e) {});
</script>
<!---复制功能 end--->
<script type="text/javascript">

    var showErr = function(id, msg) {
        $("#submit-err").html(msg);
    }
    var fnAfter = function(data) {
        // 验证回调结果
        var ok = data && parseInt(data.res_code, 10) === 0;
        if (!ok) {
            //$("#btok").html("确定");
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
        'onupload': function() {
            $("#repay1t").html("正在上传中");
        }
    });
    $(function() {
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
        $("#repay1t").click(function() {
            //$("#repay1t").unbind('click').click(function () {
            oUpload.save();
        });

    });
    var shows = function(img, i) {
        url = document.getElementById(img + 'Url').value;
        oUpload.add(img, url, function(id, rst) {
            document.getElementById(id).src = rst.base64;
            if (i == 1) {
                $('#firstd').removeClass('col-xs-offset-4');
            }
            j = i + 1;
            $("#photo_" + j).show();
        });
    }

</script>
