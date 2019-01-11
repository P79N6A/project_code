<!DOCTYPE html>
<html style="font-size: 40px;">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php echo $this->title;?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Cache-Control" content="no-transform">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="layoutmode" content="standard">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="renderer" content="webkit">
    <meta name="wap-font-scale" content="no">
    <meta content="telephone=no" name="format-detection">
    <meta http-equiv="Pragma" content="no-cache">
    <script type="text/javascript">
        var _htmlFontSize = (function () {
            var clientWidth = document.documentElement ? document.documentElement.clientWidth : document.body.clientWidth;
            if (clientWidth > 640) clientWidth = 640;
            document.documentElement.style.fontSize = clientWidth * 1 / 16 + "px";
            return clientWidth * 1 / 16;
        })();
    </script>
    <link rel="stylesheet" type="text/css" href="/newdev/css/lottery/scrape/base.min.css?version=20180516"/>

</head>

<body class="main_box" >
    <img src="<?php echo $activity->banner_url ? $img_url.$activity->banner_url : '/newdev/images/lottery/scrape/banner.png';?>">
<div class="box">
    <div class="shengdan12">
		<div class="indexfour">
			<h3>剩余刮卡次数：<em id="chance"><?php echo $lottery_number;?></em>次</h3>
		</div>
	</div>	
    <div class="content">
        <div id="mask_img_bg"><span class="cont-span"></span></div>
        <img id="redux" src="/newdev/images/lottery/scrape/layer.png"/>
    </div>
</div>

<div class="yaoqbo">
	<p class="bobao"><img src="/newdev/images/lottery/scrape/bgone.png"></p>
	<div id="content" class="phooneym">
	    <div id="scroll">
            <?php foreach($broadcast_list as $broadcast){ ?>
                <div><?php echo $broadcast;?></div>
            <?php }?>
	    </div>
	</div>
</div>

<img src="<?php echo $activity->prize_url ? $img_url.$activity->prize_url : '/newdev/images/lottery/scrape/hdgz1.png';?>">
<img src="<?php echo $activity->rule_url ? $img_url.$activity->rule_url : '/newdev/images/lottery/scrape/hdgz2.png';?>">


<div class="Hmask hidden"></div>
<div class="dl_tcym hidden">
    <p class="qjfbz">很遗憾，未中奖</p>
    <p class="qwcky hidden">前往我的奖品进行查看</p>
    <button>确定</button>
</div>
<input id="csrf" type="hidden" name="_csrf" value="<?php echo $csrf; ?>"/>
<input id="activity_id" type="hidden" name="activity_id" value="<?php echo $activity->id; ?>">
<input id="activity_condition_rule" type="hidden" name="activity_condition_rule" value="<?php echo $activity->condition->rule_condition; ?>">


<script type="text/javascript" src="/js/jquery.min.js"></script>
<script type="text/javascript" src="/newdev/js/lottery/scrape/jquery.eraser.js"></script>

<script type="text/javascript">
    $(function(){
        var h = $("#scroll div").height() ? $("#scroll div").height() : 25;
        $("#scroll div").clone().appendTo("#scroll").end().clone().appendTo("#scroll");
        var interval = setInterval(scrolling,1000);
        function scrolling(){
            var $scroll=$("#scroll");         
            if($scroll.height()<=25) return false;
            $scroll.animate({top:'-=25px'});
            if(parseInt($scroll.css("top"))+$scroll.height()==h){
                $scroll.stop().css({'top':'0'});
                // clearInterval(interval);
            }
        }
    });
</script>
<script type="text/javascript">
    $('#redux').eraser({
        size: 50,   //设置橡皮擦大小
        completeRatio: .1, //设置擦除面积比例
        completeFunction: showResetButton   //大于擦除面积比例触发函数
    });

    var csrf = $("#csrf").val();
    var activity_id = $('#activity_id').val();
    var flag = true;
    function hasChance(){
        var num_of_chance = parseInt($('#chance').text());
        if(num_of_chance > 0){
            return true;
        }else{
            return false;
        }
    }
    var activity_condition_rule = $('#activity_condition_rule').val();
    function showResetButton(){
        tongji("scrape");
        statistics_user_send();
        if(flag){
            if(hasChance()){
                $.ajax({
                    type: "post",
                    url: "/new/lottery/draw",
                    data: {activity_id: activity_id, _csrf: csrf},
                    async: false,
                    success: function (res) {
                        var datas = eval("(" + res + ")");
                        if (datas.rsp_code == '0000') {
                            flag = false;
                            $('#chance').text(parseInt($('#chance').text()) - 1);
                            $("#mask_img_bg>span").text(datas.rsp_data.title);
                            $('.qjfbz').text('恭喜您，抽中' + datas.rsp_data.title);
                        } else {
                            $('.qjfbz').text('活动暂停,请稍后再试');
                        }
                        setTimeout(function(){
                            $('.Hmask,.dl_tcym,.qwcky').show();
                            $('#scroll').append("<div>" + datas.rsp_data.broad_info + "</div>");
                        },1000);
                    }
                });
            }else{
                $('.qjfbz').text('亲，暂无抽奖次数');
                $('.Hmask,.dl_tcym').show();
            }
        }
    }

    $('.dl_tcym button').click(function(){
        flag = true;
        $('.Hmask,.dl_tcym').hide();
        // $('#redux').eraser('reset');
        window.location.reload();
    });

    $('.qwcky').click(function(){
        window.location = '/new/prize';
    });

    function addLotteryNum(){
        $.post("/new/lottery/share", {activity_id:activity_id,'_csrf':csrf}, function (data) {
            var datas = eval("(" + data + ")");
            if (datas.rsp_code == '0000') {
                $('#chance').text(parseInt($('#chance').text()) + datas.num);
            }
        });
    }

</script>

<script type="text/javascript">
    function tongji(event) {
        <?php \app\common\PLogger::getInstance('weixin','',$user->id); ?>
        <?php $json_data = \app\common\PLogger::getJson();?>
        var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
        baseInfoss.url = baseInfoss.url+'&event='+event;
        console.log(baseInfoss);
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
<script  src='/new/st/statisticssave?type=<?php echo $activity->statistics_pv;?>'></script>
<script type="text/javascript">
    function statistics_user_send(){
        $.get('/new/st/statisticssave?type=<?php echo $activity->statistics_user;?>');
    }
</script>

<script src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
<script type="text/javascript">
        wx.config({
            debug: false,
            appId: "<?php echo $jsinfo['appid']; ?>",
            timestamp: "<?php echo $jsinfo['timestamp']; ?>",
            nonceStr: "<?php echo $jsinfo['nonceStr']; ?>",
            signature: "<?php echo $jsinfo['signature']; ?>",
            jsApiList: [
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'showOptionMenu'
            ]
        });

        wx.ready(function () {
            wx.showOptionMenu();
            // 2. 分享接口
            // 2.1 监听“分享给朋友”，按钮点击、自定义分享内容及分享结果接口
            wx.onMenuShareAppMessage({
                title: '<?php echo $share_info['title']; ?>',
                desc: '<?php echo $share_info['desc']; ?>',
                imgUrl: '<?php echo $share_info['imgUrl']; ?>',
                link: '<?php echo $share_info['link']; ?>',

                trigger: function (res) {
                    // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
                },
                success: function (res) {
                    // countsharecount();
                    if(activity_condition_rule == 3){
                        addLotteryNum();
                    }
                },
                cancel: function (res) {
                },
                fail: function (res) {
                }
            });

            // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
            wx.onMenuShareTimeline({
                title: '<?php echo $share_info['title']; ?>',
                desc: '<?php echo $share_info['desc']; ?>',
                imgUrl: '<?php echo $share_info['imgUrl']; ?>',
                link: '<?php echo $share_info['link']; ?>',

                trigger: function (res) {
                    // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
                },
                success: function (res) {
                    // countsharecount();
                    if(activity_condition_rule == 3){
                        // addLotteryNum();
                        setTimeout(addLotteryNum,500);
                    }
                },
                cancel: function (res) {
                },
                fail: function (res) {
                    alert(JSON.stringify(res));
                }
            });
        })
</script>
</body>
</html>