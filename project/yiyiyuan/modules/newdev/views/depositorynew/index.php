<div class="hkxwc">
    <h3>借款需完成以下授权操作</h3>
    <?php if(!$isPassword){ ?>
        <div class="hkone">
            <img src="/299/images/jkone.jpg">
            <button onclick="toPassWord()" class="hkymone"></button>
        </div>
    <?php }elseif(!$isrepayAuth || $isRepayAuthTimeOut){ ?>
        <div class="hkone">
            <img src="/299/images/jktwo.jpg">
            <button onclick="hkymthree()" class="hkymthree"></button>
        </div>
    <?php }elseif(!$isfundAuth || $isfundAuthTimeOut){ ?>
        <div class="hkone">
            <img src="/299/images/jkthree.jpg">
            <button onclick="hkymtwo()" class="hkymtwo"></button>
        </div>
    <?php } ?>
</div>
<div class="form"></div>

<script type="text/javascript">
    var csrf = '<?php echo $csrf;?>';
    var user_id = '<?php echo $user_id;?>';
    function toPassWord() {
        $(".hkymone").attr('disabled',true);
        $.ajax({
            type: "POST",
            url: "/new/depositorynew/setpwd",
            data: {user_id: user_id,_csrf:csrf},
            success: function (data) {
                data = eval('('+data+')');
                if (data.res_code == '0000') {
                    location.href = data.res_data;
                } else {
                    alert(data.res_msg)
                    $(".hkymone").attr('disabled',false);
                }
            }
        });
    }

    function hkymthree(){
        $(".hkymthree").attr('disabled',true);
        $.ajax({
            type: "POST",
            url: "/new/depositorynew/authorize",
            data: {user_id: user_id,_csrf:csrf,type:2},
            success: function (data) {
                data = eval('('+data+')');
                if (data.res_code == '0000') {
                    //$(".form").html(data.res_data);
                    window.location.href = data.res_data;
                } else {
                    alert(data.res_msg)
                    $(".hkymthree").attr('disabled',false);
                }
            }
        });
    }

    function hkymtwo(){
        $(".hkymtwo").attr('disabled',true);
        $.ajax({
            type: "POST",
            url: "/new/depositorynew/authorize",
            data: {user_id: user_id,_csrf:csrf,type:1},
            success: function (data) {
                data = eval('('+data+')');
                if (data.res_code == '0000') {
                    //$(".form").html(data.res_data);
                    window.location.href = data.res_data;
                } else {
                    alert(data.res_msg)
                    $(".hkymtwo").attr('disabled',false);
                }
            }
        });
    }

</script>
