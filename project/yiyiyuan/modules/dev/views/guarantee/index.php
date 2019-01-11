<?php 
    $startTime = Yii::$app->params['newyear_start_time'];
    $endTime   = Yii::$app->params['newyear_end_time'];
    $time = time();
    if($time >= $startTime && $time <= $endTime){
        include '../modules/dev/views/loan/newyear.php';
    }
?>
<script  src='/dev/st/statisticssave?type=18'></script>
<div class="my_dbcard">
	<div class="mydb_title">
		<p class="mydb_edu">我的担保额度：</p>
		<p class="mydb_dianshu"><?php echo floor($gua_num);?><em>点</em></p>
            <p class="mydb_onclickbut" style="text-align: center;">
			<?php if($now_time >= $start_time && $now_time <= $end_time):?>
                <a href="javascript:void(0);" class="mydb_one">
                    <?php else:?>
                    <a href="<?php if($gua_num*0.99>=100):?>/dev/loan/borrowing<?php else:?>/dev/loan/mdbk<?php endif;?>" class="mydb_one"><?php endif;?>
                <img src="/images/db_jk1.png?v=2015101401" style="margin: 0px;" ></a>
            </p>
	</div>
	<div class="mydb_buyhis">
		<div class="mybuy_title">
			<img src="/images/mybd.png">
			<span>购买记录</span>
		</div>
		<?php foreach ($guarantee as $key=>$val):?>
		<div class="mybuy_content">
			<span class="contone"><?php echo intval($val->total_amount);?>点  </span>
			<span class="conttwo">剩余<?php echo intval($val->remain_amount);?>点</span>
			<span class="contfour"><?php echo date('Y年m月d日',  strtotime($val->pay_time));?></span>
            <?php if($time <= $startTime || $time >= $endTime):?>
                <?php if( intval($val->remain_amount) > 0): ?><span class="conthree"><?php if(in_array($val->bank_id, $userBankArr)): ?><a href="/dev/guarantee/backcard?card_id=<?php echo $val->id;?>">退卡</a><?php else:?><a href="javascript:void(0);" onclick="checkBank();" >退卡</a><?php endif;?></span><?php endif;?>
            <?php else:?>
                <span class="newyearcard"><a href="javascript:void(0);" >退卡</a></span>
            <?php endif;?>
		</div>
		<?php endforeach;?> 		
	</div>
	<a href="/dev/guarantee/buycard"><button class="mybuy_dbk fixeddwe" >购买担保卡</button></a>
</div>   
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    $(function(){
        $(".newyearcard").click(function(){
            //春节放假期间担保卡借款界面进来弹层
            var startTime = parseInt(<?php echo  Yii::$app->params['newyear_start_time'] ?>);
            var endTime = parseInt(<?php echo  Yii::$app->params['newyear_end_time'] ?>)
            var time = parseInt(<?php echo  time() ?>)
            if(time >= startTime && time <= endTime){
                $(".show_new_year").show();
            }else{
                $(".show_new_year").hide();
            }
        })
        $(".sureyemian").click(function(){
            window.location.href='/dev/loan';
        })
    })
    
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

	function checkBank(){
		$.Zebra_Dialog('您购买该担保卡的银行卡已解绑，无法进行退卡操作，立即绑卡完成退卡吧！', {
		    'type':     'question',
		    'title':    '退卡',
		    'buttons':  [
//		                    {caption: '取消', callback: function() {}},
		                    {caption: '确定', callback: function() {}},
		                ]
		});
	}
</script>