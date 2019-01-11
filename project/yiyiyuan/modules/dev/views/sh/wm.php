<div class="wrap">
		<img src="/wm/images/banner.png">
		<div class="money_txt">
			<p>赚钱神器火爆来袭！全网招募合作加盟商啦！</p>
			<p>比微商更简单，</p>
			<p>比兼职更轻松。</p>
			<p>利用碎片时间，完美步入豪门！</p>
			<p>你只需通过积累人脉，即能获得变现收益。</p>
			<p>玩着玩着把钱赚了的事还不抓紧？！</p>
			<p class="small">报名须知：</p>
			<p class="small">1.个人、组织均可。</p>
			<p class="small">2.正确填写报名资料，我们尽快与你取得联系。</p>
			<p class="small">3.做好赚钱的准备！</p>
		</div>
		<div class="lysptime"><a href="<?php echo Yii::$app->request->hostInfo;?>/background/webunion/index"><img src="/wm/images/lysptime.png?v=2016030201"></a></div>
		<div class="linerff"></div>
		<div class="team">
			<img src="/wm/images/threejt.png?v=2016030201">
		</div>
		<p class="choose">请先选择：</p>
		<div class="gerentd">
			<span id="onePeople">个人</span>
			<span id="group" class="geren">团队</span>
		</div>

		<form class="onself"  style="display:block;" >
		<div class="formColor">
			<div class="commWid clearfix">
				<p class="inputName">姓名：</p>
				<input type="text" name="dname" id="oname" maxlength="20" placeholder="请输入姓名"/>
			</div>
			<div class="commWid clearfix">
				<p class="inputName">电话：</p>
				<input type="text" name="dmobile" id="omobile" maxlength="20" placeholder="请输入电话"/>
			</div>
			<div class="commWid clearfix">
				<p class="inputName">公司名称: </p>
				<input type="text" name="company" id="ocompany" maxlength="20" placeholder="请输入公司名称"/>
			</div>
			<div class="commWid clearfix">
				<p class="inputName">公司地址: </p>
				<input type="text" name="address" id="oaddress" maxlength="30" placeholder="请输入公司地址"/>
			</div>
			<div class="commWid clearfix smallee">
				<p class="yesno">是否有过小额信贷行业经验？</p>
				<input name="credit" class="ocredit" type="radio" value="1" />是<input name="credit" class="ocredit" type="radio" value="2" />否
			</div>
			<div class="commWid clearfix smallee">
				<p class="yesno">是否可承担催收工作？</p>
				<input name="collection" class="ocollection" type="radio" value="1" />是<input name="collection" class="ocollection" type="radio" value="2" />否
			</div>
			<div class="commWid clearfix smallee">
				<p class="yesno">是否可承担坏账风险(连带责任)？</p>
				<input name="joint_responsibility" class="ojoint_responsibility" type="radio" value="1" />是<input name="joint_responsibility" class="ojoint_responsibility" type="radio" value="2" />否
			</div>
			<div class="commWid clearfix">
				<p class="inputName">备注 </p>
				<input type="text" name="dmark" id="omark" maxlength="50" placeholder="请输入备注"/>
			</div>
		</div>
		</form>
		<form class="onselfteam" style="display:none;" >
		<div class="formColor">
			<div class="commWid clearfix">
				<p class="inputName">姓名：</p>
				<input type="text" name="dname" id="gname" maxlength="20" placeholder="请输入姓名"/>
			</div>
			<div class="commWid clearfix">
				<p class="inputName">电话：</p>
				<input type="text" name="dmobile" id="gmobile" maxlength="20" placeholder="请输入电话"/>
			</div>
			<div class="commWid clearfix">
				<p class="inputName">城市：</p>
				<input type="text" name="darea" id="garea" maxlength="20" placeholder="请输入城市"/>
			</div>
			
			<div class="commWid clearfix smallee">
				<p class="yesno">是否有过小额信贷行业经验？</p>
				<input name="credit" class="gcredit" type="radio" value="1" />是<input name="credit" class="gcredit" type="radio" value="2" />否
			</div>
			<div class="commWid clearfix smallee">
				<p class="yesno">是否可承担催收工作？</p>
				<input name="collection" class="gcollection" type="radio" value="1" />是<input name="collection" class="gcollection" type="radio" value="2" />否
			</div>
			<div class="commWid clearfix smallee">
				<p class="yesno">是否可承担坏账风险(连带责任)？</p>
				<input name="joint_responsibility" class="gjoint_responsibility" type="radio" value="1" />是<input name="joint_responsibility" class="gjoint_responsibility" type="radio" value="2" />否
			</div>
			<div class="commWid clearfix">
				<p class="inputName">备注 </p>
				<input type="text" name="dmark" id="gmark" maxlength="50" placeholder="请输入备注"/>
			</div>
		</div>
		</form>
		<div class="tjsq">
			<button id="d_button_save">提交申请</button>
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

    wx.ready(function () {
    	wx.showOptionMenu();
        // 2. 分享接口
        // 2.1 监听“分享给朋友”，按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareAppMessage({
            title: '赚钱新技能来袭，招募合作加盟商了！！',
            desc: '比微商更简单，比兼职更轻松的赚钱方式，赶快报名吧！利用碎片时间，完美步入豪门。',
            link: 'http://mp.yaoyuefu.com/dev/sh/wm',
            imgUrl: 'http://mp.yaoyuefu.com/wm/images/banner.png',
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
            title: '赚钱新技能来袭，招募合作加盟商了！！',
            desc: '比微商更简单，比兼职更轻松的赚钱方式，赶快报名吧！利用碎片时间，完美步入豪门。',
            link: 'http://mp.yaoyuefu.com/dev/sh/wm',
            imgUrl: 'http://mp.yaoyuefu.com/wm/images/banner.png',
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
    });
</script>
<script src="/js/zebra_dialog.js"></script>
<script>
var _mobileRex = /^(1(([3578][0-9])|(47)))\d{8}$/;
var _tel =/^(0[0-9]{2,3})([2-9][0-9]{6,7})$/;
$(function(){
	var type=2;
	$("#onePeople").click(function(){
		type=1;
		$(this).addClass("geren");
		$("#group").removeClass("geren");
		$(".onselfteam").show(500);
		$(".onself").hide(500);
	});
	$("#group").click(function(){
		type=2;
		$(this).addClass("geren");
		$("#onePeople").removeClass("geren");
		$(".onself").show(500);
		$(".onselfteam").hide(500);
	});
	// alert(1);
	$("#d_button_save").click(function(){
		$(this).attr('disabled',true);
		var area=0;var company=0;var address=0;
		if (type==1) {
			var name=$("#gname").val();
			area=$("#garea").val();
			var mobile=$("#gmobile").val();
			var mark=$("#gmark").val();
			var credit=$(".gcredit:checked").val();
			var collection=$(".gcollection:checked").val();
			var joint_responsibility=$('.gjoint_responsibility:checked').val();
		}else{
			var name=$("#oname").val(); 
			var mobile=$("#omobile").val();
			company=$("#ocompany").val();
			address=$("#oaddress").val();
			var mark=$("#omark").val();
			var credit=$(".ocredit:checked").val();
			var collection=$(".ocollection:checked").val();
			var joint_responsibility=$('.ojoint_responsibility:checked').val();
		}

		
		if(name == "" || name == null ){
			$.Zebra_Dialog('请填写真实姓名', {
			    'type':     'question',
			    'title':    '申请加盟商',
			    'buttons':  [
//			                    {caption: '取消', callback: function() {}},
			                    {caption: '确定', callback: function() {}},
			                ]
			});
			$(this).attr('disabled',false);
			return false;
		}
		if (type==2) {
			if(mobile == "" || mobile == null || !((_mobileRex.test(mobile)) || (_tel.test(mobile)))){
				$.Zebra_Dialog('请填写正确手机号码或者固话号码(带区号)', {
				    'type':     'question',
				    'title':    '申请加盟商',
				    'buttons':  [
	//			                    {caption: '取消', callback: function() {}},
				                    {caption: '确定', callback: function() {}},
				                ]
				});
				$(this).attr('disabled',false);
				return false;
			}
			if(company == "" || company == null ){
				$.Zebra_Dialog('请填写公司名称', {
				    'type':     'question',
				    'title':    '申请加盟商',
				    'buttons':  [
	//			                    {caption: '取消', callback: function() {}},
				                    {caption: '确定', callback: function() {}},
				                ]
				});
				$(this).attr('disabled',false);
				return false;
			}
			if(address == "" || address == null ){
				$.Zebra_Dialog('请填写公司地址', {
				    'type':     'question',
				    'title':    '申请加盟商',
				    'buttons':  [
	//			                    {caption: '取消', callback: function() {}},
				                    {caption: '确定', callback: function() {}},
				                ]
				});
				$(this).attr('disabled',false);
				return false;
			}
		}else if (type==1) {
			if(mobile == "" || mobile == null || !(_mobileRex.test(mobile))){
				$.Zebra_Dialog('请填写正确手机号码', {
				    'type':     'question',
				    'title':    '申请加盟商',
				    'buttons':  [
	//			                    {caption: '取消', callback: function() {}},
				                    {caption: '确定', callback: function() {}},
				                ]
				});
				$(this).attr('disabled',false);
				return false;
			}
			if(area == "" || area == null ){
				$.Zebra_Dialog('请填写您所在城市', {
				    'type':     'question',
				    'title':    '申请加盟商',
				    'buttons':  [
	//			                    {caption: '取消', callback: function() {}},
				                    {caption: '确定', callback: function() {}},
				                ]
				});
				$(this).attr('disabled',false);
				return false;
			}
		}
		if(credit == "" || credit == null ){
			$.Zebra_Dialog('请选择是否有过小额信贷行业经验', {
			    'type':     'question',
			    'title':    '申请加盟商',
			    'buttons':  [
//			                    {caption: '取消', callback: function() {}},
			                    {caption: '确定', callback: function() {}},
			                ]
			});
			$(this).attr('disabled',false);
			return false;
		}
		if(collection == "" || collection == null ){
			$.Zebra_Dialog('请选择是否可承担催收工作', {
			    'type':     'question',
			    'title':    '申请加盟商',
			    'buttons':  [
//			                    {caption: '取消', callback: function() {}},
			                    {caption: '确定', callback: function() {}},
			                ]
			});
			$(this).attr('disabled',false);
			return false;
		}
		if(joint_responsibility == "" || joint_responsibility == null ){
			$.Zebra_Dialog('请选择是否可承担坏账风险(连带责任)', {
			    'type':     'question',
			    'title':    '申请加盟商',
			    'buttons':  [
//			                    {caption: '取消', callback: function() {}},
			                    {caption: '确定', callback: function() {}},
			                ]
			});
			$(this).attr('disabled',false);
			return false;
		}
		
		
		
		
		$.post("/dev/sh/wmsave",{type:type,name:name,mobile:mobile,area:area,company:company,address:address,credit:credit,collection:collection,joint_responsibility:joint_responsibility,mark:mark},function(result){
    		var data = eval("("+ result + ")" ) ;
    		if( data.ret == '0' ){
    			$.Zebra_Dialog('恭喜您，申请成功！', {
				    'type':     'question',
				    'title':    '申请加盟商',
				    'buttons':  [
//				                    {caption: '取消', callback: function() {}},
				                    {caption: '确定', callback: function() {
				                    	$("#oname").val(''); 
										$("#omobile").val('');
										$("#ocompany").val('');
										$("#oaddress").val('');
										$("#omark").val('');
										$("#gname").val('');
										$("#garea").val('');
										$("#gmobile").val('');
										$("#gmark").val('');
										$(".ocredit").removeAttr('checked');
										$(".ocollection").removeAttr('checked');
										$('.ojoint_responsibility').removeAttr('checked');
										$(".gcredit").removeAttr('checked');
										$(".gcollection").removeAttr('checked');
										$('.gjoint_responsibility').removeAttr('checked');
				                    	$("#d_button_save").attr('disabled',false);
					                 }},
				                ]
				});
			}else{
				$.Zebra_Dialog('请检查你输入的信息', {
				    'type':     'question',
				    'title':    '申请加盟商',
				    'buttons':  [
//				                    {caption: '取消', callback: function() {}},
				                    {caption: '确定', callback: function() {}},
				                ]
				});
				$("#d_button_save").attr('disabled',false);
	    		return false;
			}
    	});
	});
});
</script>