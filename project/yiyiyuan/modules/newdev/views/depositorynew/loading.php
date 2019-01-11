<div class="huikuannone">
    <img src="/299/images/huikuannone.png">
    <p>等待中...</p>
</div>

<script type="text/javascript">
    var step = '<?php echo $type;?>';//1：开户 2：设置密码 4：还款授权 5：消费授权 6：绑卡 7:绑卡且跳转至银行卡列表页
    var csrf =  '<?php echo $csrf;?>';
    var userId =  '<?php echo $userId;?>';
    var timer;
    var isApp = <?php
        if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')){
            echo 1;
        }else {
            echo 2;
        }
        ?>;
    function getResult(t){
        $.ajax({
            type: "POST",
            url: "/new/depositorynew/getresult",
            data: {user_id: userId,_csrf:csrf,type:t},
            success: function (data) {
                data = eval('('+data+')');
                console.log(data);
                if (data.res_code == '0000') {
                    clearInterval(timer)
                    if(step == 1 ||step == 2 || step == 4){
                        location.href = "/borrow/custody/list?user_id="+userId;
                    }else if(step == 5 || step == 6 || step == 7){
                        if(isApp == 1){
                            setTimeout(function () {
                                window.myObj.closeHtml();
                                function closeHtml() {
                                }
                            });
                        }else{
                            if(step == 7){
                                location.href = "/new/bank";
                            }else{
                                location.href = "/borrow/loan";
                            }
                        }
                    }
                } else if (data.res_code == '1001'){
                    clearInterval(timer)
                    if(isApp == 1){
                        setTimeout(function () {
                            window.myObj.closeHtml();
                            function closeHtml() {
                            }
                        });
                    }else{
                        location.href = "/borrow/loan";
                    }
                }
            }
        });
    }
    timer = setInterval(function(){
        getResult(step);
    }, 10000);
</script>
