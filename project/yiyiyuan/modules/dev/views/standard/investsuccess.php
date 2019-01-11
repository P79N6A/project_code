    <div class="succsee">
        <div class="success_titlt">
            <img src="/images/touzi_true2.png">
            <span>投资成功！</span>
        </div>
        <img class="succsee_true margin0" src="/images/touzi_true.png">
        <div class="succsee_txtx">
            <p>预期收益<em><?php echo sprintf('%.2f',$standard_order->achieving_interest);?>元</em>，<?php echo date('n'.'月'.'j'.'日', strtotime($standard_order->end_date));?>期满</p>
            <p>收益随时可提，本金返还您的担保账户</p>
        </div>
        <button class="success_share" id="investsuccess_share">分享</button>
        <p class="success_wenzi">*分享给好友，您和好友都将获得双倍收益券哦！</p>
        <div class="succsee_dldt">
            <a href="/dev/guarantee/buycard">
            <dl>
                <dt><img src="/images/touzi_true3.png"></dt>
                <dd>买担保卡</dd>
            </dl>
            </a>
            <a href="/dev/loan">
            <dl>
                <dt><img src="/images/touzi_true4.png"></dt>
                <dd>担保借款</dd>
            </dl>
            </a>
        </div>
    </div>
	<input type="hidden" name="mobile" id="mobile" value="<?php echo $userinfobywx->user->mobile;?>"/>
	<input type="hidden" name="standard_id" id="standard_id" value="<?php echo $standar_information->id;?>"/>
	<input type="hidden" name="user_id" id="user_id" value="<?php echo $userinfobywx->user->user_id;?>"/>
    <div id="overDiv" style="display:none;"></div>
    <div id="diolo_warp" class="guide_img" style="display:none;">
        <img src="/images/guide.png">
    </div> 
    
 <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
 <script>

	//点击投资成功页的分享按钮
	$("#investsuccess_share").click(function(){
		$("#overDiv").css('display','block');
		$("#diolo_warp").css('display','block');
	});

	$("#overDiv").click(function(){
		$("#overDiv").hide();
		$("#diolo_warp").hide();
	});

	$("#diolo_warp").click(function(){
		$("#overDiv").hide();
		$("#diolo_warp").hide();
	});

	function getcoupon()
	{
		var mobile = $("#mobile").val();
		var user_id = $("#user_id").val();
		var standard_id = $("#standard_id").val();
		$.post("/dev/standard/mygetcoupon", { mobile : mobile, user_id : user_id , standard_id : standard_id },function(data){
				return true;
		});
	}
 
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
            title: '<?php echo $title;?>',
            desc: '投资“园丁计划”，可使用双倍收益券获得双倍理财收益！',
            link: '<?php echo $shareurl; ?>',
            imgUrl: '<?php echo empty($userinfobywx['head']) ? '/images/dev/face.png' : $userinfobywx['head']; ?>',
            trigger: function(res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function(res) {
            	getcoupon();
            },
            cancel: function(res) {
            },
            fail: function(res) {
            }
        });

        // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareTimeline({
            title: '<?php echo $title;?>',
            desc: '投资“园丁计划”，可使用双倍收益券获得双倍理财收益！',
            link: '<?php echo $shareurl; ?>',
            imgUrl: '<?php echo empty($userinfobywx['head']) ? '/images/dev/face.png' : $userinfobywx['head']; ?>',
            trigger: function(res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function(res) {
            	getcoupon();
            },
            cancel: function(res) {
            },
            fail: function(res) {
                alert(JSON.stringify(res));
            }
        });
    });
</script>