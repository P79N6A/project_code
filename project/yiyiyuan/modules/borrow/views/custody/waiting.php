<div class="depository_waiting">
    <img class="depository_waiting_img" src="/borrow/310/images/waitingIcon.png">
    <p class="depository_waiting_text">正在处理中...</p>
</div>
<script type="text/javascript">
    var step = '<?php echo $type; ?>';//1：开户 2：设置密码 4：还款授权 5：消费授权 6：绑卡 7:绑卡且跳转至银行卡列表页 8:绑卡且跳回存管列表页 9:设置密码且跳转至银行卡列表页 10：未知跳转 11：解卡跳回银行卡列表页 12:四合一授权跳回借款首页 13：解卡跳回借款首页
    var csrf = '<?php echo $csrf; ?>';
    var userId = '<?php echo $user_id; ?>';
    var renew_type = '<?php echo $renew_type; ?>';
    var cg_whole = '<?php echo $cg_whole; ?>';
    var timer;
    var isApp = <?php
                    if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
                        echo 1;
                    } else {
                        echo 2;
                    }
                ?>;
    function getResult(t) {
        $.ajax({
            type: "POST",
            url: "/borrow/custody/getresult",
            data: {user_id: userId, _csrf: csrf, type: t},
            success: function (data) {
                data = eval('(' + data + ')');
                console.log(data);
                if (data.res_code == '0000') {
                    location.href = "/borrow/custody/showinfo?user_id="+userId+"&type="+t;
                }else{
                    location.href = "/borrow/custody/list?user_id=" + userId;
                }
            }
        });
    }
    timer = setInterval(function () {
        getResult(step);
    }, 10000);
</script>

