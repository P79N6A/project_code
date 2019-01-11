<?php
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
		    <a class="add" ><img src="/images/on.png" id='test1_<?php echo $v['id'];?>' alt=""  onclick='tog(<?php echo $v['id'];?>)'/></a>
			<!--<img src="/images/add2.png" alt="" class="add"/>-->
			
			<a class="icon" href="/background/webunion/detial?user_id=<?php echo $v['user']['user_id'];?>"><img src="<?php echo $v['user']['company'];?>" alt="" /></a>
			<a class="name"  href="/background/webunion/detial?user_id=<?php echo $v['user']['user_id'];?>"><div class="name"><?php echo $v['user']['realname'];?></div></a>
			<a class="phone"  href="/background/webunion/detial?user_id=<?php echo $v['user']['user_id'];?>"><div class="phone"><?php echo $v['user']['mobile'];?></div></a>

			<a class="state" href="/background/webunion/detial?user_id=<?php echo $v['user']['user_id'];?>"><div class="state  <?php if ($v['user']['status']==1): ?> greengray <?php endif; ?> "><?php echo $arr[$v['user']['status']];?></div></a>
		</section>
        <?php if (!empty($v['two'])): ?> 
		<?php foreach ($v['two'] as $k1 => $v1): ?>
		<div class="erjide_<?php echo $v1['id'];?>" style="display:none;">
			<section class="list list_del">
				<div class="add"></div>
				<a class="icon" href="/background/webunion/three"><img src="/images/on.png" alt="" /></a>
				<a class="icon" href="/background/webunion/detial?user_id=<?php echo $v1['user']['user_id'];?>"><img src="<?php echo $v1['user']['company'];?>" alt=""/></a>
				<a class="name" href="/background/webunion/detial?user_id=<?php echo $v1['user']['user_id'];?>"><div class="name"><?php echo $v1['user']['realname'];?></div></a>
				<a class="phone" href="/background/webunion/detial?user_id=<?php echo $v1['user']['user_id'];?>"><div class="phone"><?php echo $v1['user']['mobile'];?></div></a>
			   
			
			</section>
		</div>
       <?php endforeach; ?>
	   <?php endif; ?> 
       <?php endforeach; ?>
	   <?php endif; ?> 
		
	</div>
	<div class="nonefriend">
		<div class="disitem weirz">
			<img src="/images/weirz.png">
			<p>未实名认证的好友</p>
		</div>
		 <?php if (!empty($haoytwo)): ?> 
		<?php foreach ($haoytwo as $key => $v): ?>
		<div class="disitem nonefre">
			<img src="<?php echo $v->user->company;?>">
			<p><?php echo $v->user->mobile;?></p>
		</div>
		 <?php endforeach; ?>
	   <?php endif; ?> 
	</div>
</div>
<script>
function tog(user_id){
	alert(user_id);
   var test1 = document.getElementById('test1_'+user_id);
   //alert(test1);
   if(test1.src.indexOf('on')>=0){
      test1.src='/images/off.png';
	  $('.erjide').show();
   }else{
      test1.src='/images/on.png';
	  $('.erjide').hide();
   }
}

</script>