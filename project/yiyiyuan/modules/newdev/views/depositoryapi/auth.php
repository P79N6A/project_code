<div class="jdyyy loaning" style="width: 100%; height: 100%;background: rgba(0,0,0,.7);position: fixed; top: 0;left: 0; z-index: 100;"></div>
<div class="loading" style="position: fixed; width: 40%; top:30%; background: rgba(0,0,0,0)">
    <img style="width: 50%; margin-left: 17%;" src="/images/loadings.gif">
</div>
<input type="hidden" id="userId" value="<?php echo $userId;?>">
<input type="hidden" id="csrf" value="<?php echo $csrf;?>">
<script type="text/javascript">
    var userId = $('#userId').val();
    var csrf = $('#csrf').val();
    var timer;
    function getPassword(){
        $.ajax({
            type: "POST",
            url: "/new/depositoryapi/getsetpwd",
            data: {user_id: userId,_csrf:csrf},
            success: function (data) {
                data = eval('('+data+')');
                if (data.res_code == 1) {
                    location.href = "/new/depositoryapi/app";
                }
            }
        });
    }

    timer = setInterval(function(){
        getPassword();
    }, 10000);
</script>
