<div class="huikuannone">
    <img src="/299/images/huikuannone.png">
    <p>等待中...</p>
</div>

<script type="text/javascript">
    var step = '<?php echo $type; ?>'; //1：开户 2：设置密码 4：还款授权 5：消费授权 6：绑卡 7:绑卡且跳转至银行卡列表页 8四合一授权
    var csrf = '<?php echo $csrf; ?>';
    var userId = '<?php echo $userId; ?>';
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
            url: "/renew/depositorynew/getresult",
            data: {user_id: userId, _csrf: csrf, type: t},
            success: function (data) {
                data = eval('(' + data + ')');
                if (data.res_code == '0000') {
                    clearInterval(timer)
                    if (step == 1) {
                        $.ajax({
                            type: "POST",
                            url: "/renew/depositorynew/setpwd",
                            data: {user_id: userId, _csrf: csrf},
                            success: function (data) {
                                data = eval('(' + data + ')');
                                if (data.res_code == '0000') {
                                    location.href = data.res_data;
                                } else {
                                    location.href = "/renew/depositorynew?user_id=" + userId;
                                }
                            }
                        });
                    } else if (step == 8) {
                        if (isApp == 1) {
                            setTimeout(function () {
                                window.myObj.closeHtml();
                                function closeHtml() {
                                }
                            });
                        } else {
                            location.href = "/renew/loan";
                        }
                    }
                } else if (data.res_code == '1001') {
                    clearInterval(timer);
                    if (isApp == 1) {
                        setTimeout(function () {
                            window.myObj.closeHtml();
                            function closeHtml() {
                            }
                        });
                    } else {
                        location.href = "/renew/loan";
                    }

                }
            }
        });
    }
    timer = setInterval(function () {
        getResult(step);
    }, 10000);

    pushHistory();
    var bool=false;
    setTimeout(function(){
        bool=true;
    },1500);
    window.addEventListener("popstate", function(e) {
        if(bool){
            if (isApp == 1) {
                setTimeout(function () {
                    window.myObj.closeHtml();
                    function closeHtml() {
                    }
                });
            }
            //根据自己的需求返回到不同页面
            setTimeout(function(){
                window.location.href= '/renew/loan';
            },100);
        }
        pushHistory();
    }, false);
    function pushHistory() {
        var state = {
            url: "#"
        };
        window.history.pushState(state,  "#");
    }
</script>
