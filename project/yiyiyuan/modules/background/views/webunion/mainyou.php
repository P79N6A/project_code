<?php
use yii\widgets\LinkPager;
$arr =array('1'=>'未认证','2'=>'借款中','3'=>'已还款','0'=>'已认证');
?>
<div class="wrap wrapwidth">
		<section>
		<div class="left left_know">
			<img src="/images/people.png" style="width: 25%;margin:0 8%">
			<a href='/background/webunion/one'>
			<div class="image_conet">
				<div><em><?php echo $haoyou1;?></em><span>人</span></div>
				<div>一级好友</div>
				<div class="index_img">
					<img src="/images/index_img.png">
				</div>
			</div>
			</a>
		</div>
		<div class="line"></div>
		<div class="right right_know">
			<img src="/images/people2.png" style="width: 25%;margin:0 8%">
			<a href='/background/webunion/two'>
			<div class="image_conet">
				<div><em><?php echo $haoyou2;?></em><span>人</span></div>
				<div>二级好友</div>
				<div class="index_img" >
					<img src="/images/index_img.png">
				</div>
				</a>
			</div>
		</div>
	</section>
	<div class="friends">
		<section class="state state_del">
			<div class="add"></div>
			<div class="icon">头像</div>
			<div class="name">姓名</div>
			<div class="phone">手机号</div>
			<div class="state">状态</div>
		</section>
        <?php if (!empty($haoyone)): ?> 
		<?php foreach ( $haoyone as $key => $v): ?>
		<section class="list list_del" style="margin-top:0;">
		    <a class="add" ><img src="/images/on.png" id='test1_<?php echo $v['id'];?>' alt=""  onclick='tog(<?php echo $v['id'];?>,<?php echo $v['user_id'];?>)'/></a>
			<a class="icon" href="/background/webunion/detial?user_id=<?php echo $v['user']['user_id'];?>"><img src="<?php echo $v['heads'];?>" alt="" /></a>
			<a class="name"  href="/background/webunion/detial?user_id=<?php echo $v['user']['user_id'];?>"><div class=""><?php echo $v['user']['realname'];?></div></a>
			<a class="phone"  href="/background/webunion/detial?user_id=<?php echo $v['user']['user_id'];?>"><div class=""><?php echo substr_replace($v['user']['mobile'],'****',3,4);?></div></a>

			<a class="state" href="/background/webunion/detial?user_id=<?php echo $v['user']['user_id'];?>"><div class="<?php if ($v['user']['zstatus']==1): ?> greengray <?php endif; ?> "><?php echo $arr[$v['user']['zstatus']];?></div></a>
		</section>
       <div class="erjide" style="display:none;" id="erjide_<?php echo $v['id'];?>">
			
	   </div>
       <?php endforeach; ?>
	   <?php endif; ?> 
		
	</div>


	<div class="nonefriend">
		<div class="disitem weirz">
			<img src="/images/weirz.png">
			<p>未实名认证的好友</p>
		</div>
		 <?php if (!empty($haoywei)): ?> 
		<?php foreach ($haoywei as $key => $v): ?>
		<div class="disitem nonefre">
			<img src="<?php echo $v->user->company;?>">
			<p><?php echo $v->user->realname;?></p>&nbsp;&nbsp;
			<p><?php echo $v->user->mobile;?></p>
		</div>
		 <?php endforeach; ?>
	   <?php endif; ?> 
	</div>
   <div class="panel_pager">
	<?=LinkPager::widget(['pagination' => $pages]); ?>
   </div>
</div>

<script>
function tog(user_id,id){
   var test1 = document.getElementById('test1_'+user_id);
   if(test1.src.indexOf('on')>=0){
      $.post("/background/webunion/websave",{id:id},function(data){
		$('#erjide_'+user_id).html('');
		var data = eval("(" + data + ")");
		test1.src='/images/off.png';
	    $('#erjide_'+user_id).show();
		for(var i=0; i<data.length; i++){
		  var name = data[i]['user']['realname']==null?'':data[i]['user']['realname'];
		  $('#erjide_'+user_id).append("<section class='list list_del'><a class='add' href='/background/webunion/three'><img src='/images/on.png' alt=''/></a><img src="+data[i]['user']['company']+" alt='' class='icon' /><div class='name'>"+name+"</div><div class='phone'>"+data[i]['user']['mobile'].replace(/^(\d{4})\d{4}(\d+)/,"$1****$2")+"</div></section>");
		}
	    
	});
   }else{
      test1.src='/images/on.png';
	  $('#erjide_'+user_id).hide();
   }
}

</script>
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