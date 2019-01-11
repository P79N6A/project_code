<style>
  .big345-button {
        display: table;
        width: 4.8rem;
        height: 0.65rem;
        background-image: linear-gradient(-90deg, #FF4B17 0%, #F00D0D 100%);
        border-radius: 0.13rem;
        font-size: 0.28rem;
        color: #FFFFFF;
        letter-spacing: 0;
        line-height: 0.48rem;
    }
    .rz-filed {
        margin: 0.40rem auto;
    }
</style>
<div class="wraper">
        <div class="content-top" id="try_activation" style="display: none">
            <img src="/borrow/310/images/failIcon.png" alt="">
            <p class="content-top-title" style="font-size: 0.24rem;margin: 0.13rem;">激活失败</p>
            <p class="content-top-small">很遗憾，您的额度激活失败，请重试，或进行测评激活</p>
<!--            <button class="content-top-btn" id="try" style=" margin-top: 3em;">再试一次</button>-->
<button class="big345-button rz-filed"  id="try" style="font-size: 0.25rem;">再试一次</button>
        </div>
        <div class="content-botom" id="activation" style="display: none;position: relative;">
            <p id="evaluation" style="top: 0.80rem;font-weight: bold; color: #F12A2A">测评激活</p>
        </div> 
    
        <!-- 激活结果2 -->
        <div class="top-cont" id="activation_false" >
            <img src="/borrow/310/images/failIcon.png" alt="">
            <p class="top-cont-title">激活失败</p>
            <p class="top-cont-small">请24小时后再次发起借款，或进行测评激活</p>
        </div>
        <!-- 激活结果2-底部（无内容） -->
        <div class="btom-cont" id="activation_none"></div>
    </div>


<script type="text/javascript" src="/newdev/js/jquery-1.11.0.min.js"></script>
<script type="text/javascript">
   
    $(function(){
        <?php \app\common\PLogger::getInstance('weixin','',$encodeUserId); ?>
        <?php $json_data = \app\common\PLogger::getJson();?>
        var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
        
        var btn_status = <?php echo $btn_status; ?>;
        var try_activation_url = <?php echo "'".$try_activation_url."'" ; ?> ;
        console.log(try_activation_url);
        var evaluation_activation_url = <?php echo "'".$evaluation_activation_url."'" ; ?> ;
        var isApp = <?php
        if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')){
            echo 1;  //app端
        }else {
            echo 2;  //h5端
        }
        ?>;
        var u = navigator.userAgent, app = navigator.appVersion;
        var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
        var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
        var android = "com.business.main.MainActivity";
        var ios = "loanViewController";
        var position = "-1";
      
        getBtn(btn_status); //再试一次和测评激活按钮是否显示
        $('#try').click(function(){
            tongji('try_activation',baseInfoss);
            if(btn_status){
                if( isApp === 1 ){
                      if (isiOS) {
                            window.myObj.toPage(ios);
                       } else if(isAndroid) {
                            window.myObj.toPage(android, position);
                       }
                }else if( isApp === 2 ){
                     setTimeout(function(){
                        window.location.href = try_activation_url;
                    },100);
                    
                }
              
            }
        });
        $('#evaluation').click(function(){
            tongji('try_evaluation_activation',baseInfoss);
             if(btn_status){
                 setTimeout(function(){
                         window.location.href = evaluation_activation_url;
                    },100);               
            }
        });

        //重写返回按钮
       pushHistory();
        var bool=false;
        setTimeout(function(){
            bool=true;
        },500);
        window.addEventListener("popstate", function(e) {
           tongji('activation_reback_btn',baseInfoss);
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
    
    function getBtn(btn_status){
        if( btn_status ){
            $('#activation_false').hide();
            $('#activation_none').hide();
            $('#try_activation').show();
            $('#activation').show();
            
            
        }else{
            $('#activation_false').show();
            $('#activation_none').show();
            $('#try_activation').hide();
            $('#activation').hide();
        }
    }
    
</script>
