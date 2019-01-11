<img src="/sevenday/images/bannerbg.png">
<div class="qtxsmx">请填写实名信息</div>
<div class="yhmaold">
    <div class="dbk_inpL">
        <label>姓名</label><input placeholder="请填写身份证姓名" type="text" name="realname">
    </div>
    <div class="dbk_inpL">
        <label>身份证号</label><input placeholder="请填写身份证号" type="text" name="identity">
    </div>
</div>
<div class="buttonyi">
    <button onclick="doUserauth()" id="do_userauth">提交认证</button>
</div>
<div class="tishi_success" id="divbox" style="display: none;"><a class="tishi_text">获取额度失败</a></div>
<input type="hidden" id="csrf" value="<?php echo $csrf; ?>">
<script type="text/javascript">
    var csrf = $('#csrf').val();
    function doUserauth() {
        zhuge.track('实名认证-填写信息后提交');
        $("#do_userauth").attr('disabled', true);
        var realname = $("input[name='realname']").val();
        var identity = $("input[name='identity']").val();
        $.ajax({
            type: "POST",
            url: "/day/userauth/saveidentity",
            data: {_csrf: csrf, realname: realname, identity: identity},
            success: function (result) {
                result = eval('(' + result + ')');
                if (result.rsp_code == '0000') {
                    location.href = result.url;
                } else if (result.rsp_code == '0005') {
                    zhuge.track('实名认证-已被注册-下载H5点击');
                    $("#do_userauth").attr('disabled', false);
                    $('.tishi_text').html(result.rsp_msg);
                    $('.tishi_success').show();
                } else {
                    $("#do_userauth").attr('disabled', false);
                    $('.tishi_text').html(result.rsp_msg);
                    $('.tishi_success').show();
                }
            }
        });
    }
</script>
