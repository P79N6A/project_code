<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" id="result_url" content="" /> 
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>激活中</title>
<!--    <link rel="stylesheet" href="/newdev/css/loan/reset.css">-->
    <style>
        html,body{
            height: 100%;
            margin:0;
            padding: 0;
            background: #fff;
        }
      #canvas{
    display: table;
    margin:0 auto;
  }
  .jihuo_wrap{
    height: 100%;
    overflow: hidden;
  }
  .can-title {
  display: block;
  width: 100%;
  font-size:16px;
  color: #444444;
  margin-top: 1rem;
  text-align: center;
  margin-bottom: 1.5rem;
   }
  .jh-btom{
      width:100%;
      height: 100%;
      background:#F3F3F3;
  }
    </style>
</head>

<body>
    <div class="jihuo_wrap">
    <canvas id="canvas" width="250" height="250" style=""></canvas>
    <div class="can-title">
        <span>请稍等，正在激活中...</span>
    </div>
    <div class="jh-btom"></div>
</div>

<script>
    window.onload = function () {
        var canvas = document.getElementById('canvas'), 
            context = canvas.getContext('2d'), 
            centerX = canvas.width / 2, 
            centerY = canvas.height / 2, 
            rad = Math.PI * 2 / 60,
            speed = 20; 
            var speed2=0;
        
        function blueCircle(n) {
            context.save();
            context.strokeStyle = "#C90000"; 
            context.lineWidth = 5; 
            context.beginPath(); 
            context.arc(centerX, centerY, 100,(1/6)*Math.PI- n * rad, Math.PI *(1.5) ,  true); //用于绘制圆弧context.arc(x坐标，y坐标，半径，起始角度，终止角度，顺时针/逆时针)
            context.stroke(); 
            context.closePath(); 
            context.restore();
        }
        function whiteCircle() {
        context.save();
        context.strokeStyle = 'rgb(234, 234, 234)';
        context.lineWidth = 2;
        context.beginPath();
        context.arc(centerX, centerY, 100 , 0, Math.PI*2, true);
        context.stroke();
        context.closePath();
        }
        //秒（s）文字绘制
        function text(n) {
            context.save(); 
            context.fillStyle = "#C90000"; 
            context.font = "30px Arial"; 
            context.fillText(n.toFixed(0) + "s", centerX - 25, centerY + 10);
            context.stroke(); 
            context.closePath();
            context.save();
        }
        //动画循环
        (function drawFrame() {
            window.requestAnimationFrame(drawFrame, canvas);
            context.clearRect(0, 0, canvas.width, canvas.height);
            if(speed>20){
                whiteCircle();
                text(0);
                return;//状态判断
            }else{
            whiteCircle();
            text(speed);
            
                speed -= 0.0166666667;  
            }
            if(speed2>20){
                whiteCircle();
                return;
            }else{
                speed2+= 0.0166666667;
                blueCircle(speed2);
            }
        }());
    }
</script>
</body>

</html>
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
        
        var url = '18;'+ '/new/directactivation/activationresult?loan_id=' + loan_id + '&activation_num=' + activation_num ;
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
                getIndexapp();
            }else if( isApp == 2 ){
                setTimeout(function(){
                    window.location.href= '/new/loan';
                },1000);
            }
        }                
            
        //重写返回按钮
       pushHistory();
        var bool=false;
        setTimeout(function(){
            bool=true;
        },1500);
        window.addEventListener("popstate", function(e) {
           tongji('direct_activation_reback_btn');
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
    function pushHistory() {
        var state = {
            //title: "title",
            url: "#"
        };
        window.history.pushState(state,  "#");
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
