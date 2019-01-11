        <style>
        body{background: #f0f0f0;}
        .shareH{height:410px;background: url(/images/s_header_bg.jpg)no-repeat;background-size: 100%;}
        </style>
     	
        <script type="text/javascript">
        $(window).load(function (){
            var banHeight = $('.s_cont img').height();
            $('.s_cont').css('height',banHeight);
        })
        </script>
        <div class="Hcontainer nP">
        	<div class="shareH">
                <div class="main">
                    
                    <div class="col-xs-12 float-right n26 mt037" style="padding-left:0">
                        <img src="<?php echo empty( $userinfo['userwx']['head'] ) ? "/images/dev/face.png" : $userinfo['userwx']['head'] ;?>" alt="" class="float-right shareH_photo" width="24%">
                        <span class="float-right mt9 mr2">— — <?php if(!empty($userinfo['userwx']['nickname'])):?><?php echo $userinfo['userwx']['nickname'];?><?php else:?><?php echo $userinfo['realname'];?><?php endif;?> </span>
                    </div>
                    
                    
                    <div class="col-xs-12 s_cont mt10">
                        <img src="/images/s_cont_bg.png">
                        <div class="s_btn">
                        <?php if($userinfo['user_id'] == $logininfo['user_id']):?>
                        <?php if($loaninfo['status'] == 9):?>
                            <button class="btn1 mt20 noBorder" id="freecoupon_share" style="width:84%;">分享</button>
                            <!--<a href="/dev/repay/cards?loan_id=<?php //echo $loaninfo['loan_id'];?>"><button class="btn mt20" style="width:84%;">我要还款</button></a>-->
                            <a href="/dev/repay/repaychoose?loan_id=<?php echo $loaninfo['loan_id'];?>"><button class="btn mt20" style="width:84%;">我要还款</button></a>
                        <?php else:?>
                        	<button class="btn mt20" id="freecoupon_share" style="width:84%;">分享</button>
                        <?php endif;?>
                        <?php else:?>
                        	<a href="#"><button class="btn1 mt20 noBorder" style="width:84%;">去投资</button></a>
                            <a href="/dev/loan?atten=1"><button class="btn mt20" style="width:84%;">去借款</button></a>
                        <?php endif;?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="bottom"></div>
               <div class="Hmask" style="display: none;"></div>
                <img src="/images/guide.png" class="guide_share" style="display: none;">
            </div>
       </div>
       
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>

    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'onMenuShareTimeline',
            'onMenuShareAppMessage',
            'showOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.showOptionMenu();
        // 2. 分享接口
        // 2.1 监听“分享给朋友”，按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareAppMessage({
            title: '先花一亿元',
            desc: '我借了这么多钱，没有花一分服务费，你投资我还有丰厚收益，这么爽！！',
            link: '<?php echo $shareurl; ?>',
            imgUrl: '<?php echo empty($userinfo['userwx']['head']) ? '/images/dev/face.png' : $userinfo['userwx']['head']; ?>',
            trigger: function(res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function(res) {
// 	    	  window.location = "/dev/invest";
            },
            cancel: function(res) {
            },
            fail: function(res) {
            }
        });

        // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareTimeline({
            title: '我借了这么多钱，没有花一分服务费，你投资我还有丰厚收益，这么爽！！',
            desc: '我借了这么多钱，没有花一分服务费，你投资我还有丰厚收益，这么爽！！',
            link: '<?php echo $shareurl; ?>',
            imgUrl: '<?php echo empty($userinfo['userwx']['head']) ? '/images/dev/face.png' : $userinfo['userwx']['head']; ?>',
            trigger: function(res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function(res) {
// 	    	  window.location = "/dev/invest";
            },
            cancel: function(res) {
            },
            fail: function(res) {
                alert(JSON.stringify(res));
            }
        });
    });
</script>