<div class="Hcontainer">
         <div class="bWhite overflow" style="padding-top:15px;">
          <div class="border_bottom_1">
            <div class="main overflow">
              <div class="col-xs-12">
                <div class="col-xs-4 text-right relative nPad">
				  <?php if($dstatus==7){?>
                  <img src="/images/frost.png" alt="" width="80%" id="change">
				  <?php } else {?>
                   <img src="/images/star.png" alt="" width="80%" id="change"> 
				  <?php } ?>
                </div>
                <div class="col-xs-4 text-center nPad">
                  <div class="face_gender">
                    <img src="<?php echo $head;?>" alt="" width="100%" class="dbr_face">
                    <!-- 男孩 -->
					<?php if($sexs==1){?>
                    <img src="/images/icon_boy.png" class="gender">
                    <!-- 女孩 -->
					<?php } else {?>
                    <img src="/images/icon_girl.png" class="gender">
					<?php } ?>
                  </div>
                  
                </div>
                <div class="col-xs-4 text-left nPad"><p class="n34" style="margin-top:15%;"><?php echo $username['realname'];?></p><p><img src="/images/approved.png" alt="" width=60%;></p></div>
              </div>
              <div class="col-xs-12 mt40" style="padding:0 12%;">
                <p class="red n26 text-center">总担保额度<?php echo $amount;?>点</p>
                    <div class="proWrap mb10">
                      <progress max="<?php echo $amount;?>" value="<?php echo number_format($amount-$current_amount,2,'.','');?>" style="width:100%" id="progress1"></progress>
                      <div class="probg"></div>
                      <span class="proBar red"></span>
                    </div> 
                    <div class="row mb20">
                        <div class="col-xs-6 grey4 n22" style="padding-left:4%;">已用<?php echo number_format($amount-$current_amount,2,'.','');?>点</div>
                        <div class="col-xs-6 text-right grey4 n22" style="padding-right:4%;">还剩<?php echo $current_amount;?>点可用</div>
                    </div>
              </div>  
            </div>
            
            
          </div>
          <ul class="nav_dbr overflow">
              <li class="col-xs-6">
                  <div class="item">已完成的借款</div>
              </li>
              <li class="col-xs-6">
                  <div class="item on">担保中的借款</div>
              </li>
          </ul>           
         </div>
         <!-- 已完成的借款 -->
         <div class="dbr_cont">
            <div class="main overflow">
            <div class="col-xs-6 n24">累计担保金额：<span class="red"><?php echo number_format($total,2,'.','');?></span>元</div>
            <div class="col-xs-6 n24 text-right">累计获得收益：<span class="red"><?php echo number_format($total*0.01,2,'.','');?></span>元</div>
           </div>
           <div class="bWhite overflow">

              <?php if( $user_info ){
		        foreach ( $user_info as $v ){
	          ?>
                <div class="border_bottom mt20 pb20">
                  <div class="padlr625">
                    <div class="gender_wrap">
                      <img class="face2" src="<?php echo $v['user']['head'];?>">
                       <?php if($v['user']['sex']==1){?>
						<img src="/images/icon_boy.png" class="gender">
						<!-- 女孩 -->
						<?php } else {?>
						<img src="/images/icon_girl.png" class="gender">
						<?php } ?>
                    </div>
                     <div class="info_list2">
                       <div class="row n26" style="margin-top:2%;">
                              <div class="col-xs-12">借款金额：<?php echo number_format($v['loan']['amount'],2,'.','') ;?>元</div>
                         </div>
                         <div class="row n24">
                              <div class="col-xs-12 ch mt3">收益金额：<?php echo number_format($v['loan']['amount']*0.01,2,'.','') ;?>元</div>
                         </div>
                     </div>
                    <div class="money2">
                          <div class="float-right mt10"><a href="/dev/guarantoraccount/income?loan_id=<?php echo $v['loan']['loan_id'];?>"><span class="state bGreen1">已完成</span></a></div>
                    </div>
                 </div>
                </div> 
                <?php } }else{ ?>
                <!--这里为没有完成显示的内容-->
                <?php } ?>
           </div> 
         </div>
         <!-- 担保中的借款 -->
         <div class="dbr_cont on">
            <div class="main overflow">
            <div class="col-xs-6 n24"><span class="red"><?php echo $user_counts ;?></span>笔进行中的借款</div>
            <div class="col-xs-6 n24 text-right">预计获得收益：<span class="red"><?php echo number_format($totals*0.01,2,'.','');?></span>元</div>
           </div>
           <div class="bWhite overflow">
              
              <?php if( $user_infos ){
		        foreach ( $user_infos as $v ){
	          ?>
              <div class="border_bottom mt20 pb20">
                <div class="padlr625">
                    <span class="gender_wrap">
                      <img class="face2" src="<?php echo $v['user']['head'];?>">
                        <?php if($v['user']['sex']==1){?>
						<img src="/images/icon_boy.png" class="gender">
						<!-- 女孩 -->
						<?php } else {?>
						<img src="/images/icon_girl.png" class="gender">
						<?php } ?>
                    </span>
                    <div class="info_list2">
                        <div class="row n26" style="margin-top:2%;">
                          <div class="col-xs-12">应还款金额：<?php echo number_format($v['loan']['amount']+$v['loan']['interest_fee']+$v['loan']['withdraw_fee'],2,'.','') ;?>元</div>
                        </div>
                        <div class="row n24">
                          <!--<?php echo date('Y-m-d',strtotime($v['loan']['end_date'])-24*3600) ;?>-->
                          <!--<div class="col-xs-12 ch mt3">应还款日期：<?php echo substr($v['loan']['end_date'],0,10) ;?></div>-->
						  <div class="col-xs-12 ch mt3">应还款日期：<?php echo !empty($v['loan']['end_date'])?date('Y-m-d',strtotime($v['loan']['end_date'])-24*3600):'以实际审核为准' ;?></div>
                        </div>
                     </div>
                    <div class="money2">
					    <?php if(($v['status'] == 12 && strtotime($v['loan']['end_date'])<=time()) || $v['status'] == 13){ ?>
                        	 <div class="float-right mt10"><a href='/dev/guarantoraccount/examine?loan_id=<?php echo $v['loan']['loan_id'];?>'><span class="state bRed">已逾期</span></a></div>
                        <?php }else if(($v['status']==12 && strtotime($v['loan']['end_date'])>time()) || $v['status'] == 9 || $v['status'] == 11){?>
                        	 <div class="float-right mt10"><a href='/dev/guarantoraccount/examine?loan_id=<?php echo $v['loan']['loan_id'];?>'><span class="state bOrange2">待还款</span></a></div>
                        <?php }else if($v['status'] == 5 || $v['status'] == 10 || $v['status'] == 6){?>
                        	<div class="float-right mt10"><a href='/dev/guarantoraccount/examine?loan_id=<?php echo $v['loan']['loan_id'];?>'><span class="state bBlue">审核中</span></a></div>
						<?php }?>
                    </div>
                 </div>
              </div>
			  
               <?php } }else{ ?>
                <!--这里为没有完成显示的内容-->
                <?php } ?>

           </div> 
         </div>
		 
		 <footer class="redline">
			<ul class="text-center">
				<li style="margin-left: 16%;">
				<!--https://open.weixin.qq.com/connect/oauth2/authorize?appid=<?php echo Yii::$app->params['AppID'];?>&redirect_uri=<?php echo Yii::$app->params['app_url'];?>/dev/guarantoraccount/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect-->
					<a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=<?php echo Yii::$app->params['AppID'];?>&redirect_uri=<?php echo Yii::$app->params['app_url'];?>/dev/sponsor/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect"><img src="/images/011.png" width="33%"/><div class="cor n26">投资</div></a>
				</li>
				<li style="float:right;margin-right:16%;">
					<a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=<?php echo Yii::$app->params['AppID'];?>&redirect_uri=<?php echo Yii::$app->params['app_url'];?>/dev/guarantoraccount/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect"><img src="/images/03.png" width="33%"/><div class="red n26">账户</div></a>
				</li>
			</ul>
		</footer>
   </div>
<script>
$(document).ready(function(){
  var proValue = $('#progress1').attr('value');
  var proMax = $('#progress1').attr('max');
  var proPercent = (proValue/proMax)*100;
  $('.Hcontainer .proWrap .proBar').css('width',proPercent + '%');
  if($('#progress1').val()==100){
    $('#change').attr('src','/images/frost.png');
    $('#change').css({'width':'100%','position':'absolute','right':'0','top':'0'})
  }
  $('.bWhite .border_bottom:last-child').css('border','none');
  $('.nav_dbr .item').each(function(index){
      $(this).click(function(){
          $('.nav_dbr .item').removeClass('on');
          $(this).addClass('on');
          $('.dbr_cont').removeClass('on');
          $('.dbr_cont').eq(index).addClass('on');
      });
  });
  
})
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
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

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>