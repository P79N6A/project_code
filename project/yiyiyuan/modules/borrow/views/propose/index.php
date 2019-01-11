<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="keywords" content="花生米富 一亿元">
    <meta name="description" content="一亿元">
    <title>投诉建议</title>
    <script src="/borrow/310/js/flexible.js"></script>
    <script src="/newdev/js/log.js" type="text/javascript" charset="utf-8"></script>
    <link rel="stylesheet" href="/borrow/310/css/reset.css">
    <link rel="stylesheet" href="/borrow/311/css/lyq-style.css">
    <script src="/js/jquery-1.10.1.min.js"></script>
    <style>
    	.toast_tishi {
		  	position: fixed;
		  	top: 20%;
		    color: #fff;
		    z-index: 15;
		    padding: 0.25rem 0;
		    border-radius: 5px;
		    text-align: center;
		    background: rgba(0,0,0,0.5);
		    width: 80%;
		    left: 10%;
		    font-size: 0.45rem;
		}
    </style>
</head>

<body>
    <div class="wraper" style="min-height: auto;">
        <div class="suggest-content">
            <div class="box-textarea-block">
                <textarea id="content" class="box-textarea" placeholder="请输入投诉或意见内容"></textarea>
            </div>
            <img id="camera_picture" class="sst-sm-crema" src="/borrow/311/images/small-crema@2x.png" alt="">
            <span>添加图片</span>
        </div>
        <button class="big345-button sst-comfirm-btn">确认提交</button>
    </div>
    <div class="toast_tishi" id="xtfmang" hidden></div>
</div>


</body>

</html>

<script src="/js/upload/resizeimg/dist/lrz.bundle.js?v=20180822" type="text/javascript"></script>
<?php if (SYSTEM_ENV == 'prod'): ?>
    <script src="/js/upload/imgupload.js?v=2018111" type="text/javascript"></script>
<?php else: ?>
    <script src="/js/upload/imguploadnew.js?m=2018111" type="text/javascript"></script>
<?php endif; ?>
<style>
    .help_service{
        margin-top: 8rem;
        width: 100%;
        left: 0;
        bottom: 1.81rem;
        height: 0.43rem;
        text-align: center;
    }
    .help_service a{
        position: relative;
    }
    .contact_service_tip{
        width: 0.40rem;
        height: 0.43rem;
        /*position: absolute;*/
        /*left: 3.97rem;*/
        /*top: 0;*/
        position: absolute;
        left:-0.44rem;
        bottom: 0.05rem;
    }
    .contact_service_text{
/*        height: 0.37rem;
        position: absolute;
        left:4.59rem;*/
        font-family: "微软雅黑";
        font-size: 0.37rem;
        color: #3D81FF;
        letter-spacing: 0;
        height:0.43rem;
        line-height: 0.43rem;
        margin:0;
        padding:0;
        /*border:1px solid green;*/
    }
</style>
<div class="help_service">
    <!-- <img src="/borrow/310/images/tip.png" alt="" class="contact_service_tip"> -->
    <a href="javascript:void(0);" onclick="doHelp('https://www.sobot.com/chat/h5/index.html?sysNum=f0af5952377b4331a3499999b77867c2&robotFlag=1&partnerId=<?php echo $user_id;?>')">
        <img src="/borrow/310/images/tip.png" alt="" class="contact_service_tip">
        <span class="contact_service_text">未能解决您的问题，联系客服</span>
    </a>
</div>
<script>
    <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
	$(document).ready(function(){
		var has_picture = false;
		var can_submit = true;
		var csrf = "<?php echo $csrf;?>";
		var redirect_url = "<?php echo $redirect_url;?>";
        var user_id = "<?php echo $user_id;?>";
		var postData = {
			_csrf:csrf,
            user_id:user_id
		};
		function show_error(text){
			$('#xtfmang').show();
			$("#xtfmang").text(text);
			setTimeout(function () {
			    $("#xtfmang").hide();
			    $('#xtfmang').text('');
			}, 1000);
		}

		function fnAfter(data){
            var ok = data && parseInt(data.res_code, 10) === 0;
            if(ok){
            	var urls = data.res_data.camera_picture;
            	postData['picture'] = urls;
            }else{
            	show_error('图片上传失败');
            	has_picture = false;
            }
            add_propose();
        }

		var oUpload = new ImageUpload({
            "formid": "uploadImgForm",
            'action': "<?= $img_upload_url ?>/upload",
            "encrypt": "<?= $encrypt ?>",
            "error": function(){},
            'afterSave': fnAfter,
            'onupload':function(){
                show_error('上传中');
            }
        });

		oUpload.add('camera_picture','',function(id,rst){
            tongji('do_propose_img',baseInfoss);
            setTimeout(function(){},100);
			document.getElementById(id).src = rst.base64;
			has_picture = true;
		});

        $('.sst-comfirm-btn').click(function(){
        	if(!can_submit){
        		return false;
        	}
        	can_submit = false;
        	var content = $('#content').val();
        	if(content.length < 1){
        		show_error('内容不能为空');
        		can_submit = true;
        		return false;
        	}
        	postData['content'] = content;

        	if(has_picture){
        		oUpload.save();
        	}else{
        		add_propose();
        	}
        	
        });

        function add_propose(){
            tongji('do_propose',baseInfoss);
            setTimeout(function(){
                $.ajax({
                    url:'/borrow/propose',
                    type:'POST',
                    data:postData,
                    dataType:'json',
                    success:function(res){
                        can_submit = true;
                        show_error(res.res_data);
                        if(res.res_code == 200){
                            window.location.href = redirect_url;
                        }
                    }
                });
            },100);
        }
	});
    function doHelp(url) {
        tongji('do_help',baseInfoss);
        setTimeout(function(){
            window.location.href = url;
        },100);
    }
</script>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js?m=v10"></script>
<script>
	//微信参数
    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'hideOptionMenu'
        ]
    });

    wx.ready(function () {
        wx.hideOptionMenu();
    });
</script>