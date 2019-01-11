        <div class="Hcontainer nP">
        <div class="top">
            <div class="main">
                <div class="row mb40">
                    <div class="col-xs-3 photo"><img src="<?php echo empty( $userinfo['head'] ) ? '/images/dev/face.png' : $userinfo['head'];?>" class="person_face" /></div>
                    <div class="col-xs-3 pd mt20 n34"><a class="n34"><?php echo $userinfo['nickname'];?></a></div>
                    <div class="col-xs-6 text-right n22 mt20 ch"><?php echo $loaninfo['create_time'];?></div>
                </div>
                <p class="mb20 n44 mt40 text-center"><?php echo $loaninfo['desc'];?></p>
             </div>
             <div class="main infos">
              <div class="row mt40">
                    <div class="col-xs-6 n22">借<span class="red n34"><?php echo sprintf("%.2f", $loaninfo['amount']);?></span>点</div>
                    <div class="col-xs-6 text-right n22 cor_a">借款期限<span class="n34"> <?php echo $loaninfo['days'];?> </span>天</div>
                </div>
             </div>
        </div>
          <div class="main bgf border_bottom">
              <div class="row">
                    <div class="col-xs-3 btn">未筹满</div>
                    <div class="col-xs-5 pd n26 mt5">已筹到<span class="red n26"><?php echo sprintf("%.2f", $loaninfo['current_amount']);?></span>元</div>
                    <div class="col-xs-4 text-right n22 mt5">剩余<span class="n26" id="count_hour"><?php echo $remaintime;?></span>小时</div>
                </div>
           </div>
           <div class="main">
              <p class="n22 mb20 ch">您此次借款未能筹满，请确认是否提现已筹到额度，6小时内如不能进行确认，系统默认为放弃借款！</p>
                <input type="hidden" id="coupon_exist" value=<?php echo $coupon;?> />
                 <input type="hidden" id="loan_id" value=<?php echo $loaninfo->loan_id;?> />
                <button type="button" <?php if($loaninfo['current_amount']<500):?>class="btn mb20 bgrey"<?php else:?>class="btn mb20" id="withdraw_button"<?php endif;?> style="width:100%">提现</button>
                <div id="cancle_loan" style="color:#279cff;font-size:15px; float:right; height:2.5rem; font-weight:bold;">取消借款》 </div>
                <img src="/images/10007.png" width="100%"/>
                <?php if($loaninfo->credit_amount > 0):?>
                <div class="border_bottom mt20 pb20">
                    <img class="face" src="<?php echo empty( $userinfo->head ) ? "/images/dev/face.png" : $userinfo->head ;?>">
                   <div class="info_list">
                     <div class="row n28">
                            <div class="col-xs-12"><a><?php if(!empty($userinfo->nickname)):?><?php echo $userinfo->nickname;?><?php else:?><?php echo $userinfo->user->realname;?><?php endif;?></a></div>
                       </div>
                       <div class="row n22">
                            <div class="col-xs-12 ch mt3"><?php echo $loaninfo['create_time'];?></div>
                       </div>
                   </div>
                   <div class="money">
                   <img src="<?php if( $loaninfo['type'] == '1'){?>/images/good.png<?php }else{?>/images/edunei.png<?php }?>" width="45%" class="float-left" style="vertical-align:text-bottom;"/> <div class="float-right mt10"><span class="red"><?php echo sprintf('%.2f',$loaninfo['credit_amount']);?></span>点</div>
                   </div>
               </div>
                <?php endif;?>
                <?php if( $loanrecord ){?>
                <?php foreach ( $loanrecord as $loan){ ?>
                <div class="border_bottom mt20 pb20">
                    <img class="face" src="<?php echo empty( $loan['head'] ) ? "/images/dev/face.png" : $loan['head'] ;?>">
                   <div class="info_list">
                     <div class="row n28">
                            <div class="col-xs-12"><a><?php echo $loan['nickname'];?></a></div>
                       </div>
                       <div class="row n22">
                            <div class="col-xs-12 ch mt3"><?php echo $loan['create_time'];?></div>
                       </div>
                   </div>
                   <div class="money">
                   <img src="<?php if( $loan['type'] == '1'){?>/images/good.png<?php }else{?>/images/borrow.png<?php }?>" width="35%" class="float-left" style="vertical-align:text-bottom;"/> <div class="float-right mt10"><span class="red"><?php echo sprintf('%.2f',$loan['amount']);?></span>点</div>
                   </div>
               </div>
               <?php }?>
               <?php }?> 
           </div>
           <?php if($coupon == 'yes'):?>
           <div class="Hmask" style="display: none;"></div>
           <div class="layer t10" id="coupon_error" style="display: none;">
                <p class="border_bottom pad n26 lh30">由于当前借款未筹满，优惠券不能使用，将返还您的账户。</p>
                <div class="text-center pad"><span class="sure_btn red n30" id="withdraw_error_button">确定</span></div>
            </div>
            <?php endif;?>
            <div class="Hmask" style="display: none;"></div>
           <div class="layer_border overflow noBorder" style="display: none;">
	            <p class="n28 padlr625" style="text-align:center;">确定取消借款吗？</p>
	            <p class="n28 mb30 padlr625" style="text-align:center; color:#aaa; padding-top:10px;">已筹集<?php echo sprintf('%.2f',$loaninfo->current_amount);?>元，借款总额<?php echo sprintf('%.2f',$loaninfo->amount);?>元</p>
	            <p class="n28 mb30 padlr625" id="cancle_error" style="text-align:center; color:#aaa;color:red;"></p>
	            <div class="border_top_2 nPad overflow">
	                <a href="javascript:;" id="close_cancle" class="n30 boder_right_1 text-center"><span class="grey2">取消</span></a>
	                <a href="javascript:;" id="cancle_button" lid="<?php echo $loaninfo->loan_id;?>" class="n30 red text-center"><span style="color:#e74747;">确定</span></a>
	            </div>
          </div> 
       </div>
        <script>
       	count = <?php echo $remaintime;?> ;
		counthour = setInterval(countHour, 1000*3600);
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

	$("#cancle_loan").click(function(){
		$(".Hmask").show();
		$(".layer_border").show();
	});

	$("#close_cancle").click(function(){
		$(".Hmask").hide();
		$(".layer_border").hide();
	});

	$("#cancle_button").click(function(){
		var loan_id = $(this).attr('lid');
		$("#cancle_button").attr('disabled', true);
		$.post("/dev/loan/cancleloan", {loan_id: loan_id}, function (result) {
			var data = eval("(" + result + ")");
			if (data.ret == '1'){
                $("#cancle_error").html('参数错误');
                $("#cancle_button").attr('disabled', false);
                return false;
            }else if(data.ret == '2'){
            	$("#cancle_error").html('取消借款失败');
            	$("#cancle_button").attr('disabled', false);
                return false;
            }else if(data.ret == '3'){
            	$("#cancle_error").html('暂时不能取消借款，等待审核中');
            	$("#cancle_button").attr('disabled', false);
                return false;
            }else{
            	window.location = "/dev/loan/succ?l="+loan_id;
            }                
		});
	});
</script>