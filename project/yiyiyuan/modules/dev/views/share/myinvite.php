<div class="container">
        	<div class="contents">
            	<img src="/images/dev/1000.png" width="100%"/>
                <div class="title text-center">
                	<p>邀请小伙伴们来扫描二维码</p>
                </div>
                <div class="img text-center">
                	<img src="/images/dev/ma.png" width="44%"/>
                </div>
                <div class="invite text-center">
                	<span class="b30 invite-txt">邀请码：<?php echo empty($userinfo->invite_code) ? '' : $userinfo->invite_code;?></span>
                </div>
                <div class="send text-center">
                	<button class="btn" onClick="shareTip();"  style="width:80%;">点此发给熟人</button>
                </div>
            </div>
       </div>
<script src="/js/dev/shareWin.js"></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  wx.config({
	debug: false,
	appId: '<?php echo $jsinfo['appid'];?>',
	timestamp: <?php echo $jsinfo['timestamp'];?>,
	nonceStr: '<?php echo $jsinfo['nonceStr'];?>',
	signature: '<?php echo $jsinfo['signature'];?>',
	jsApiList: [
	    'onMenuShareTimeline',
	    'onMenuShareAppMessage'
	  ]
  });
  
  wx.ready(function(){
	  // 2. 分享接口
	  // 2.1 监听“分享给朋友”，按钮点击、自定义分享内容及分享结果接口
	    wx.onMenuShareAppMessage({
	      title: '我的邀请码',
	      desc: '我的邀请码',
	      link: '<?php echo $shareurl;?>',
	      imgUrl: '<?php echo empty($userinfo['userwx']->head) ? '/images/dev/face.png' : $userinfo['userwx']->head ;?>',
	      trigger: function (res) {
	        // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
	      },
	      success: function (res) {
// 	    	  window.location = "/dev/share/invite";
	      },
	      cancel: function (res) {
	      },
	      fail: function (res) {
	      }
	    });

	  // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
	    wx.onMenuShareTimeline({
	      title: '我的邀请码',
	      link: '<?php echo $shareurl;?>',
	      imgUrl: '<?php echo empty($userinfo['userwx']->head) ? '/images/dev/face.png' : $userinfo['userwx']->head ;?>',
	      trigger: function (res) {
	        // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
	      },
	      success: function (res) {
// 	    	  window.location = "/dev/share/invite";
	      },
	      cancel: function (res) {
	      },
	      fail: function (res) {
	        alert(JSON.stringify(res));
	      }
	    });
    // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。
	});





</script>