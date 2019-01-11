<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/newdev/css/loan/reset.css">
    <link rel="stylesheet" href="/newdev/css/loan/style.css">
    <title>激活结果</title>

</head>

<body>
    <div class="wraper">
        <div class="content-top" id="try_activation" style="display: none">
            <img src="/newdev/images/loan/timer-close.png" alt="">
            <p class="content-top-title">很遗憾，您的额度激活失败</p>
            <p class="content-top-small">请重试，或进行测评激活</p>
            <button class="content-top-btn" id="try" style=" margin-top: 3em;">再试一次</button>
        </div>
        <div class="content-botom" id="activation" style="display: none">
            <p id="evaluation">测评激活></p>
        </div> 


        <!-- 激活结果2 -->
        <div class="top-cont" id="activation_false" >
            <img src="/newdev/images/loan/timer-close.png" alt="">
            <p class="top-cont-title">很遗憾，您的额度激活失败</p>
            <p class="top-cont-small">请24小时后再次发起借款，或进行测评激活</p>
        </div>
        <!-- 激活结果2-底部（无内容） -->
        <div class="btom-cont" id="activation_none"></div>
    </div>
</body>
</html>

<script type="text/javascript" src="/newdev/js/jquery-1.11.0.min.js"></script>
<script type="text/javascript">
   
    $(function(){

        var btn_status = <?php echo $btn_status; ?>;
        var try_activation_url = <?php echo "'".$try_activation_url."'" ; ?> ;
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
            tongji('try_activation');
            if(btn_status){
                if( isApp === 1 ){
                      // getIndexapp();
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
            tongji('try_evaluation_activation');
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
            },1500);
            window.addEventListener("popstate", function(e) {
                tongji('activation_reback_btn');
                if(bool){
                   //根据自己的需求返回到不同页面
                    setTimeout(function(){
                            if(isApp == 1){
                                    getIndexapp();
                               
                               }else if( isApp == 2 ){      
                                    window.location.href= '/new/loan';
                             }
                           
                     },100);

                }
                    pushHistory();
            }, false);
   
       

    });
     function  getIndexapp(){
          if (isiOS) {
               window.myObj.toPage(ios);
          } else if(isAndroid) {
               window.myObj.toPage(android, position);
          }
     }
    function toPage() {}
    function pushHistory() {
    var state = {
        //title: "title",
        url: "#"
    };
    //window.history.pushState(state, "title", "#");
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
      function tongji(event) {
        <?php \app\common\PLogger::getInstance('weixin','',$encodeUserId); ?>
        <?php $json_data = \app\common\PLogger::getJson();?>
        var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
        baseInfoss.url = baseInfoss.url+'&event='+event;
        // console.log(baseInfoss);
        var ortherInfo = {
            screen_height: window.screen.height,//分辨率高
            screen_width: window.screen.width,  //分辨率宽
            user_agent: navigator.userAgent,
            height: document.documentElement.clientHeight || document.body.clientHeight,  //网页可见区域宽
            width: document.documentElement.clientWidth || document.body.clientWidth,//网页可见区域高
        };
        var baseInfos = Object.assign(baseInfoss, ortherInfo);

        var turnForm = document.createElement("form");
        turnForm.id = "uploadImgForm";
        turnForm.name = "uploadImgForm";
        document.body.appendChild(turnForm);
        turnForm.method = 'post';
        turnForm.action = baseInfoss.log_url+'weixin';
        //创建隐藏表单
        for (var i in baseInfos) {
            var newElement = document.createElement("input");
            newElement.setAttribute("name",i);
            newElement.setAttribute("type","hidden");
            newElement.setAttribute("value",baseInfos[i]);
            turnForm.appendChild(newElement);
        }
        var iframeid = 'if' + Math.floor(Math.random( 999 )*100 + 100) + (new Date().getTime() + '').substr(5,8);
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.id = iframeid;
        iframe.name = iframeid;
        iframe.src = "about:blank";
        document.body.appendChild( iframe );
        turnForm.setAttribute("target",iframeid);
        turnForm.submit();
    }
    
</script>
