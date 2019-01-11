<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!--IE Compatibility modes-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--Mobile first-->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Login Page</title>
    
    <meta name="description" content="Free Admin Template Based On Twitter Bootstrap 3.x">
    <meta name="author" content="">
    
    <meta name="msapplication-TileColor" content="#5bc0de" />
    <meta name="msapplication-TileImage" content="/static/img/metis-tile.png" />
    
    <!-- Bootstrap -->
    <link rel="stylesheet" href="/static/lib/bootstrap/css/bootstrap.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/static/lib/font-awesome/css/font-awesome.css">
    
    <!-- Metis core stylesheet -->
    <link rel="stylesheet" href="/static/css/main.css">
    
    <!-- metisMenu stylesheet -->
    <link rel="stylesheet" href="/static/lib/metismenu/metisMenu.css">
    
    <!-- animate.css stylesheet -->
    <link rel="stylesheet" href="/static/lib/animate.css/animate.css">


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<?php 
$_csrf = \Yii::$app->request->getCsrfToken();
 ?>
<body class="login">

      <div class="form-signin">
    <div class="text-center" style="background-color: #4A5B7D">
        <img src="http://www.xianhuahua.com/index/images/logo1.png" alt="Metis Logo">
    </div>
    <hr>
    <div class="tab-content">
        <div id="login" class="tab-pane active">
            <form id="login-form" action="/backend/default/login" method="post">
                <p class="text-muted text-center">
                    输入用户名密码
                </p>
                <input name="_csrf" type="hidden" id="_csrf" value="<?= \Yii::$app->request->csrfToken ?>">
                <input type="text" name="username" placeholder="用户名" class="form-control top">
                <input type="password" name="password" placeholder="密码" class="form-control bottom">
                <div class="checkbox">
          <label>
            <input type="checkbox"> 记住我
          </label>
        </div>
                <button class="btn btn-lg btn-primary btn-block" type="submit">登录</button>
            </form>
        </div>
        <div id="forgot" class="tab-pane">
            <form action="index.html">
                <p class="text-muted text-center">Enter your valid e-mail</p>
                <input type="email" placeholder="" class="form-control">
                <br>
                <button class="btn btn-lg btn-danger btn-block" type="submit">Recover Password</button>
            </form>
        </div>
    </div>
    <hr>
    <div class="text-center">
        <ul class="list-inline">
            <li><a class="text-muted" href="#login" data-toggle="tab">登录</a></li>
            <li><a class="text-muted" href="#forgot" data-toggle="tab">忘记密码</a></li>
        </ul>
    </div>
  </div>


    <!--jQuery -->
    <script src="/static/lib/jquery/jquery.js"></script>

    <!--Bootstrap -->
    <script src="/static/lib/bootstrap/js/bootstrap.js"></script>


    <script type="text/javascript">
        (function($) {
            $(document).ready(function() {
                $('.list-inline li > a').click(function() {
                    var activeForm = $(this).attr('href') + ' > form';
                    //console.log(activeForm);
                    $(activeForm).addClass('animated fadeIn');
                    //set timer to 1 seconds, after that, unload the animate animation
                    setTimeout(function() {
                        $(activeForm).removeClass('animated fadeIn');
                    }, 1000);
                });
            });
        })(jQuery);
    </script>
</body>

</html>


<!--
<script>
	chkTel = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-35-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/;
	function showErr(text){
		$(".showError").html(text);
	}
    var _csrf = $('#_csrf').val();
    $('#mobile').blur(function(){
        chkMobile();
    });

    function chkMobile(){
        var mobile = $('#mobile').val();
        if( !mobile ){
            showErr('请填写手机号');
            $('#login').attr('disabled',true);
            return false;
        }
        if(!chkTel.test(mobile)){
            showErr('手机号不合法');
            $('#login').attr('disabled',true);
            return false;
        }
        showErr('&nbsp;');
        $('#login').attr('disabled',false);
        $.post('isreg',{mobile:mobile,_csrf:_csrf},function(data){
            if (data && parseInt(data.res_code,10) === 2) {
                showErr('未注册,请注册后登录');
                $('#login').attr('disabled',true);
            }else if(data && parseInt(data.res_code,10) === 1){
                showErr(data.res_data);
                $('#login').attr('disabled',false);
            }
        },'json')
    }




    $('#login').click(function(){
        var mobile = $('#mobile').val();
        if( !mobile ){
            showErr('请填写手机号');
            $('#login').attr('disabled',true);
            return false;
        }
        if(!chkTel.test(mobile)){
            showErr('手机号不合法');
            $('#login').attr('disabled',true);
            return false;
        }
        showErr('&nbsp;');
        $('#login').attr('disabled',false);
        var password = $('#password').val();
        if (!password) {
            showErr('请输入密码');
            $('#login').attr('disabled',true);
            return false;
        };
        $('#login').attr('disabled',true);
        $.post('/default/login',{_csrf:_csrf,mobile:mobile,password:password},function(data){
            if (data && parseInt(data.res_code,10) === 0) {
            	window.location.href=data.res_data;
            }else{
                showErr(data.res_data);
                $('#login').attr('disabled',false);
            }
        },'json')
    })
</script>-->