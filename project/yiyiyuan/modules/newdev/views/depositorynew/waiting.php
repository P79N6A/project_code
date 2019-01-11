<div class="huikuannone">
    <img src="/299/images/huikuannone.png">
    <p>等待中...</p>
</div>
<script type="text/javascript">
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
    function getResult(){
        $.ajax({
            type: "POST",
            url: "/new/depositorynew/getresult",
            data: {user_id: userId,_csrf:csrf,type:6},
            success: function (data) {
                data = eval('('+data+')');
                if (data.res_code == '0000') {
                    clearInterval(timer);
                    if(isApp == 1){
                        setTimeout(function () {
                            window.myObj.closeHtml();
                            function closeHtml() {
                            }
                        });
                    }else{
                        location.href = "/borrow/loan";
                    }
                } else if (data.res_code == '1001'){
                    clearInterval(timer);
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
        getResult();
    }, 10000);
</script>