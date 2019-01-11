<div class="hkxwc">
    <h3>还款需完成以下授权操作</h3>
    <?php if ($step == 4): ?>
    <div class="hkone">
        <img src="/299/images/hkone.jpg">
     <button class="hkymone" onclick="hkymone()"></button>
    </div>
    <?php endif; ?>
    <?php if ($step == 5): ?>
    <div class="hkone">
        <img src="/299/images/hktwo.jpg">
        <button class="hkymtwo" onclick="hkymtwo()"></button>
    </div>
    <?php endif; ?>
</div>
<div class="form"></div>
<script type="text/javascript">
    var csrf = '<?php echo $csrf;?>';
    var userId = '<?php echo $userId;?>';
    function hkymone(){
        $(".hkymone").attr('disabled',true);
        $.ajax({
            type: "POST",
            url: "/new/depositorynew/authorize",
            data: {user_id: userId,_csrf:csrf,type:2,is_repay:2},
            success: function (data) {
                data = eval('('+data+')');
                if (data.res_code == '0000') {
                    //$(".form").html(data.res_data);
                    window.location.href = data.res_data;
                } else {
                    alert(data.res_msg);
                    $(".hkymone").attr('disabled',false);
                }
            }
        });
    }

    function hkymtwo(){
        $(".hkymtwo").attr('disabled',true);
        $.ajax({
            type: "POST",
            url: "/new/depositorynew/authorize",
            data: {user_id: userId,_csrf:csrf,type:1,is_repay:2},
            success: function (data) {
                data = eval('('+data+')');
                if (data.res_code == '0000') {
                    //$(".form").html(data.res_data);
                    window.location.href = data.res_data;
                } else {
                    alert(data.res_msg);
                    $(".hkymtwo").attr('disabled',false);
                }
            }
        });
    }
</script>
