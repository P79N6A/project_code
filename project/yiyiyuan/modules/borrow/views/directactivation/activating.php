
    <div class="jihuo_wrap">
    <div class="can-title">
        <div class="timer" id="timer"></div>
        <p class="one">直接激活中</p>
        <p>培养良好的习惯，可提高额度申请成功率</p>
    </div>
    <div class="jh-btom"></div>
</div>

<script>
    //$encodeUserId
     <?php \app\common\PLogger::getInstance('weixin','',$encodeUserId); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
    
    window.onload = function () {
        var num = 20;
        var timer = setInterval(function(){
            num--;
            if(num < 0){
                    return;
            }
            $("#timer").html(num+'s');
        },1000);
    }
</script>

<script>

    $(function(){
       var refresh_status = <?php echo $refresh_status ;?> ;
       var activation_num = <?php echo $activation_num ;?> ;
        var loan_id = <?php echo $loan_id ;?> ;
        var u = navigator.userAgent, app = navigator.appVersion;
        var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
        var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
        var android = "com.business.main.MainActivity";
        var ios = "loanViewController";
        var position = "-1";
        
        var url = '18;'+ '/borrow/directactivation/activationresult?loan_id=' + loan_id + '&activation_num=' + activation_num ;
        $('#result_url').attr('content',url); 
              //重写返回按钮
       var isApp = <?php
        if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')){
            echo 1;  //app端
        }else {
            echo 2;  //h5端
        }
        ?>;
         if( refresh_status == 1){
            if( isApp == 1 ){
                if (isiOS) {
                   window.myObj.toPage(ios);
                } else if(isAndroid) {
                   window.myObj.toPage(android, position);
                }
            }else if( isApp == 2 ){
                setTimeout(function(){
                    window.location.href= '/borrow/loan';
                },1000);
            }
        }

        //重写返回按钮
       pushHistory();
       var bool=false;
        setTimeout(function(){
            bool=true;
        },500);
        window.addEventListener("popstate", function(e) {
           tongji('direct_activation_reback_btn',baseInfoss);
           if(bool){
              //根据自己的需求返回到不同页面
               setTimeout(function(){
                       if(isApp == 1){
                           if (isiOS) {
                              window.myObj.toPage(ios);
                           } else if(isAndroid) {
                              window.myObj.toPage(android, position);
                           }
                       }else if( isApp == 2 ){ 
                          window.location.href= '/borrow/loan';
                   } 
                },1000);
           }
               pushHistory();
        }, false);
    });
    
    function toPage() {}
    function pushHistory() {
        var state = {
            url: "#"
        };
        window.history.pushState(state,  "#");
    }

</script>
