<body class="g3">
<div class="mJiedong">
    <div class="header">
        <p class="p1">距离一亿元还差</p>
        <h3><strong><?php echo sprintf("%.2f", $userinfo['account']['remain_amount']);?></strong> 点</h3>
        <p class="p2">已解冻：<?php echo sprintf("%.2f", $userinfo['account']['amount']);?>点</p>
        <a href="/html/jiedong.html" class="link"><i class="icon"></i>查看攻略</a> </div>
    <div class="tips">解冻更多额度，投资给熟人，就可以获取丰厚现金收益哦～</div>
    <ul class="mJiedongList">
        <li> <img src="/img/icon_j_01.png" class="icon" alt="" />
            <div class="text"> <strong>微信授权登录</strong> <em>微信授权登录等，你会获得<b>200</b>点信用值</em> </div>
            <a href="#" class="link dis">已完善</a> </li>
        <li> <img src="/img/icon_j_02.png" class="icon" alt="" />
            <div class="text"> <strong>完善身份信息</strong> <em>注册为先花花一亿元会员，并完善个人信息就领取<b>300-1200</b>点信用值</em> </div>
            <?php if( $userinfo->user_type == '1'):?>
            <?php if(empty($userinfo->school)):?>
            <a href="/dev/reg/shtwo?url=<?php echo urlencode('/dev/account/remain');?>" class="link">去完善</a> </li>
            <?php else:?>
            <a href="#" class="link dis">已完善</a> </li>
            <?php endif;?>
            <?php else:?>
            <?php if(empty($userinfo->company)):?>
            <a href="/dev/reg/two?url=<?php echo urlencode('/dev/account/remain');?>" class="link">去完善</a> </li>
            <?php else:?>
            <a href="#" class="link dis">已完善</a> </li>
            <?php endif;?>
            <?php endif;?>
        <?php if($userinfo->user_type == 2):?>
        <?php if(!empty($userinfo->school)):?>
        <li> <img src="/img/icon_xueji.png" class="icon" alt="" />
            <div class="text"> <strong>学籍验证</strong> <em>作为社会人的你，只要验证学籍信息通过就可以领取<b>100</b>点信用值</em> </div>
            <a href="#" class="link dis">已完善</a> 
        </li>
        <?php else:?>
        <li> <img src="/img/icon_j_04.png" class="icon" alt="" />
            <div class="text"> <strong>学籍验证</strong> <em>作为社会人的你，只要验证学籍信息通过就可以领取<b>100</b>点信用值</em> </div>
            <a href="/dev/reg/shthree?url=<?php echo urlencode('/dev/account/remain');?>" class="link">去完善</a> 
        </li>
        <?php endif;?>
        <?php endif;?>
        <li> <img src="/img/icon_j_03.png" class="icon" alt="" />
            <div class="text"> <strong>持证自拍照</strong> <em>按照我们的要求拍摄并上传清晰的自拍照，可领取<b>100</b>点信用值</em> </div>
            <?php if( $userinfo->status == '3'):?>
            <a href="#" class="link dis">已完善</a> </li>
            <?php elseif( $userinfo->status == '2'):?>
            <a href="#" class="link dis">审核中</a> </li>
            <?php else:?>
            <a href="/dev/reg/pic?url=<?php echo urlencode('/dev/account/remain');?>" class="link">去拍照</a> </li>
            <?php endif;?>
        <li> <img src="/img/icon_j_04.png" class="icon" alt="" />
            <div class="text"> <strong>首次借款</strong> <em>只要您首次借款成功，并在规定的时间范围内还款，就可以提升借款额度的信用值，最高可得，<b>5000</b>点</em> </div>
            <?php if($loan_count == 0):?>
            <a href="/dev/loan" class="link">去借款</a> </li>
            <?php else:?>
            <a href="#" class="link dis">已提额</a> </li>
            <?php endif;?>
        <li> <img src="/img/icon_j_05.png" class="icon" alt="" />
            <div class="text"> <strong>再次借款</strong> <em>首次借款并按时还款后，您后续每次借款并按时还款，都会获得，借款金额<b>50％</b>的信用值</em> </div>
            <a href="/dev/loan" class="link">去借款</a> </li>
        <li> <img src="/img/icon_j_06.png" class="icon" alt="" />
            <div class="text"> <strong>熟人认证</strong> <em>每次成功认证熟人或被熟人认证都可获得<b>30</b>点信用点<br>
                已认证 <b><?php echo $auth_count[0]['count'];?></b> 人　被认证 <b><?php echo $auth_count[1]['count'];?></b> 次</em> </div>
            <a href="/dev/auth/index" class="link">去认证</a> </li>
        <li> <img src="/img/icon_j_07.png" class="icon" alt="" />
            <div class="text"> <strong>邀请好友</strong> <em>通过邀请码邀请好友来先花花一亿元赚钱，每邀请一位好友就可以获得<b>10</b>点信用值</em> </div>
            <a href="/dev/share/invite" class="link">去邀请</a> </li>
    </ul>
    <div class="moreway"><i class="icon"></i>更多解冻方式，敬请期待～</div>
</div>
</body>
       
                             <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  wx.config({
	debug: false,
	appId: '<?php echo $jsinfo['appid'];?>',
	timestamp: <?php echo $jsinfo['timestamp'];?>,
	nonceStr: '<?php echo $jsinfo['nonceStr'];?>',
	signature: '<?php echo $jsinfo['signature'];?>',
	jsApiList: [
		'hideOptionMenu'
	  ]
  });
  
  wx.ready(function(){
	  wx.hideOptionMenu();
	});
</script>