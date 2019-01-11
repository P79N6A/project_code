<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>额度激活</title>
    <link rel="stylesheet" href="/newdev/css/loan/reset.css">
    <link rel="stylesheet" href="/newdev/css/loan/style.css">
</head>
<body>
    <div class="yaoshi_wraper">
        <div>
            <img class="zh-img" src="/newdev/images/loan/zhihui.png" alt="">
        </div>
        <p class="zh-title">
            测评激活需下载智融钥匙APP
        </p>
        <p class="zh-sm-title">为确保信息同步，请使用<font style="color: #C90000;"><?php echo $mobile; ?></font>登录智融钥匙</p>
        <div class="zh-btn-con">
            <button class="zhihui-btn" id="open">立即打开</button>
        </div>
       
    </div>
    
</body>
</html>
<script type="text/javascript" src="/newdev/js/jquery-1.11.0.min.js"></script>
<script type="text/javascript">
    
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
    
    $(function(){
        $('#open').click(function(){
            tongji('real_down_zrys');

            if(isApp === 1){ //唤起或下载智融钥匙app1
               
            if(isAndroid){ 
                  
                 window.myObj.doShare('doDown');
            }
            if(isiOS){ 
                window.myObj.toDown();
            }
            }
        if(isApp === 2){ //h5  到智融钥匙下载智融钥匙app  
             setTimeout(function(){
                    window.location.href = 'http://sj.qq.com/myapp/detail.htm?apkName=com.pxhzrk.loankey';
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
                tongji('evaluation_down_reback_btn');
                if(bool){
                   //根据自己的需求返回到不同页面
                    setTimeout(function(){
                        if( isApp == 1 ){
                            getIndexapp();
                        }else if( isApp == 2 ){
                               window.location.href= '/new/loan';
                        }
                        
                     },100);

                }
                    pushHistory();
            }, false);

        
    });
    function pushHistory() {
    var state = {
        //title: "title",
        url: "#"
    };
    window.history.pushState(state,  "#");
    }
    function  getIndexapp(){
          if (isiOS) {
               window.myObj.toPage(ios);
          } else if(isAndroid) {
               window.myObj.toPage(android, position);
          }
     }
    function toPage() {}
    function doShare() {}
    function getDown(){
//        var u = navigator.userAgent, app = navigator.appVersion;
//        var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
//        var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
        if(isApp === 1){ //唤起或下载智融钥匙app
            if(isAndroid){ 
               
                 window.myObj.toDown();
            }
            if(isiOS){ 
                window.myObj.toDown();
            }
        }
        if(isApp === 2){ //h5  到智融钥匙下载智融钥匙app  
             setTimeout(function(){
                    window.location.href = 'http://sj.qq.com/myapp/detail.htm?apkName=com.pxhzrk.loankey';
             },100);
             
        }    
    }
    function toDown(){}

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