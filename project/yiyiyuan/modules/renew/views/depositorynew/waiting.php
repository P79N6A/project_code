<div class="huikuannone">
    <img src="/299/images/huikuannone.png">
    <p>等待中...</p>
</div>
<script type="text/javascript">
    var step = '<?php echo $type;?>';//4：还款授权 5：消费授权
    var csrf =  '<?php echo $csrf;?>';
    var userId =  '<?php echo $user_id;?>';
    var loanId =  '<?php echo $loan_id;?>';
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
                if (data.res_code == '0000') {
                    clearInterval(timer)
                    if(step == 4){
                        location.href = "/new/depositorynew/choice?user_id="+userId;
                    }else if(step == 5){
                        if(isApp == 1){
                            setTimeout(function () {
                                window.myObj.closeHtml();
                                function closeHtml() {
                                }
                            });
                        }else{
                            location.href = "/new/loan/second";
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
                        location.href = "/new/repay/repaychoose?loan_id="+loanId;
                    }
                }
            }
        });
    }
    timer = setInterval(function(){
        getResult(step);
    }, 10000);
</script>