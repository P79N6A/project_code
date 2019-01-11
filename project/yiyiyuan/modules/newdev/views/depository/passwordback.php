<div class="jdyyy loaning" style="width: 100%; height: 100%;background: rgba(0,0,0,.7);position: fixed; top: 0;left: 0; z-index: 100;"></div>
<div class="loading" style="position: fixed; width: 40%; top:30%; background: rgba(0,0,0,0)">
    <img style="width: 50%; margin-left: 17%;" src="/images/loadings.gif">
</div>
<div class="auth"></div>
<script type="text/javascript">
    var csrf = '<?php echo $csrf;?>';
    function hello(){
        $.post("/new/depository/setpwdres", {_csrf: csrf}, function (res) {
            var data = eval("(" + res + ")");
            if (data.ret == '0') {
                //设置密码成功跳转到发起借款页
                location.href = "/new/loan/second";
            }
        });
    }
    var t1 = window.setInterval(hello,10000);
</script>
