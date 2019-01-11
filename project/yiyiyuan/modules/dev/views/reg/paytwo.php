<div class="container">
    <div class="content">
        <form class="form-horizontal">
            <p class="mb20"><input type="text" name="realname" id="reg_realname" maxlength="10" placeholder="姓名"  class="form-control"/></p>
            <p class="mb40"><input type="text" name="identity" id="reg_identitys" maxlength="18" is_real='0' placeholder="身份证号"  class="form-control"/></p>
            <input type="hidden" name="l" value="<?php echo $url; ?>">
            <button type="button" class="btn mb20" id="bank_form" style="width:100%;" >确定</button>                     
        </form>
    </div>
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    $(function () {
        $("#bank_form").click(function () {
            var realname = $('#reg_realname').val();
            var identity = $('#reg_identitys').val();
            var url = $('input[name="l"]').val();
            if (realname == '') {
                alert("请选择你的真实姓名");
                return false;
            }
            if (!checkregisteridentity(identity)) {
                alert('请填写正确的身份证号码');
                return false;
            } else {
                $("#reg_identitys").attr('is_real', '1');
            }
            var is_real = $('#reg_identitys').attr('is_real');
            if (identity == '0' || is_real == '0') {
                alert("请填写姓名/身份证号码");
                return false;
            }
            $.post("/dev/reg/namesave", {realname: realname, identity: identity}, function (result) {
                var data = eval("(" + result + ")");
                if (data.ret == '0') {
                    window.location = url;
                } else if (data.ret == '3') {
                    alert('身份证号码已经存在！');
                    return false;
                } else if (data.ret == '11') {
                    alert('身份证号码与姓名不匹配');
                    return false;
                } else if (data.ret == '1')
                {
                    window.location = '/dev/reg/login';
                    //alert('系统错误');
                } else if (data.ret == '2') {
                    alert('系统错误');
                    return false;
                }
            });
        });
    });


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
</script>