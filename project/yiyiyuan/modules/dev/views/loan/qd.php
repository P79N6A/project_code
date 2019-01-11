
<form action='/dev/loan/qued' method='POST'>
        <div class="Hcontainer">
            <img src="/images/title.png" width="100%"/>
           <div class="con">
                <div class="details">
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">借款用途</div>
                        <div class="col-xs-8 text-right n26 "><?php echo $desc;?></div>
						<input type='hidden' name='desc' value=<?php echo $desc;?>>
                    </div>
                    <div class="border_bottom_1 mb20"></div>
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">期限（天）</div>
                        <div class="col-xs-4">
                        <?php if($days==1):?>
						<div class="assureC">隔夜还</div>
						 <?php endif;?> 
						</div>
                        <div class="col-xs-4 text-right n26"><span class="red"><?php echo $days;?></span> 天</div>
						<input type='hidden' name='days' value=<?php echo $days;?>>
                    </div>
                    <div class="border_bottom_1 mb20"></div>
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">金额（元）</div>
                        <div class="col-xs-4"><div class="assureC">担保</div></div>
                        <div class="col-xs-4 text-right n26 red">&yen;<?php echo $amount;?></div>
						<input type='hidden' name='amount' value=<?php echo $amount;?> id='amount'>
                    </div>
                    <div class="border_bottom_1 mb20"></div>
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">应还金额 </div>
                        <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo ceil($amount/0.99*100)/100;?></span></div>
                    </div>

					<div class="border_bottom_1 mb20"></div>
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">最后还款日 </div>
                        <div class="col-xs-8 text-right n26"><?php echo date('Y-m-d',time()+$days*24*3600);?></div>
                    </div>
                    <div class="border_bottom_red mt40"></div>
					
                    <div class="col-xs-12 nPad mt20" onclick='hlz()'>
					    <div class="col-xs-12 txkswhat" style="padding-bottom:10px;">提现卡</div>
                        <div class="col-xs-2 nPad"><img src="/images/bank_logo/<?php echo $user_bankinfo->bank_abbr;?>.png" width="100%" style="max-width:100px;" id='yhtp'></div>
                        <div class="col-xs-10 bank_cont mt10" style="padding-left:10px; line-height:40px;">
                            <span class="n30" id='yhm'><?php echo $user_bankinfo->bank_name;?></span>
                            
							<span style="display:inline-block; margin-left:10px; width:30%; height:20px; line-height:20px; border-radius:10px; background:#e74747; color:#fff; text-align:center; font-size:12px;">借记卡</span>

							<span class="n26 grey2" id='yhk' style="display:block;font-size:12px; margin-top: 5%;color:#aaa;" ><?php echo '尾号'.substr($user_bankinfo->card,-4);?></span>

                            <input type='hidden' name='bank_id' value=<?php echo $user_bankinfo->id;?>
							id='bank_ids'>
							<input type='hidden' name='user_ids' value=0 id='user_ids'>
							<input type='hidden' name='button_ids' value=<?php echo $guarantee_amount;?> id='button_ids'>
                            <p class="n24 grey4 mt10"></p>
                            <i></i>  
							 
                        </div>
                    </div>
					
                </div>
                <img src="/images/bottom.png" width="100%" style="vertical-align:top"/>
           </div>
           <div class="main">

		   <div class="n26">
                <input type="checkbox" checked="checked" id="checkbox-1" class="regular-checkbox" name="agreement">
                <label for="checkbox-1"></label>
                阅读并同意
                <a href="/dev/loan/agreeloan?type=loan&loan_type=danbao&desc=<?php echo urlencode($desc);?>&days=<?php echo $days;?>&amount=<?php echo $amount;?>&repay_amount=<?php echo $amount;?>" target="_blank"" class="underL aColor">《先花一亿元居间协议及借款协议》</a>
            </div>
		   <?php if($isexist==1):?>
		   <div class="btn mt20 mb40" style="width:100%" onclick = 'qd()'>确定</div>
		   <?php else:?>
		   <button class="btn mt20 n26" style="width:100%;" id='button_qd'><span class="white">确定</span></button>
		   <?php endif;?>
		   </div>
		   <div class="Hmask" style="display:none;"></div>
           <div class="layer_border layer2" style="display:none;">
                <div class="padlr625">
                    <p class="n26">自提现后<span class="red"><?php echo $days;?></span>日内，若您未全额还款，一亿元将抵消您的<span class="red"><?php echo ceil($amount/0.99*100)/100;?></span>点担保额度。</p>
                    <div class="choose mt20 float-left">
                        <input type="checkbox" name="is_d" checked class="regular-radio" id="radio-1" value='1'>
                        <label for="radio-1" id='loanajax'></label>
                    </div>
                    <p class="float-left mt20" style="margin-left: 5px;">不再提示</p>
                </div>
                <div class="clearfix"></div>
                <div class="border_top_2 mt20 text-center">
                    <button class="n26" style="width:100%;background:#fff;"><span class="red">朕知道了</span></button>
                </div>
           </div> 
		   
		   
       <div class="layer highlight" style="top:30%;display:none;" >
            <i class="on"></i> 
            <ul class="banksC dBlock">
       
                <?php if(!empty($user_bankinfo1)):?>
                <?php foreach ($user_bankinfo1 as $key=>$v):?>
                <li onclick='ajax(<?php echo $v->id;?>,<?php echo $v->user_id;?>)'>
                    <img src="/images/bank_logo/<?php echo $v->bank_abbr;?>.png" width="10%" id='loan_img_<?php echo $v->id;?>'>
                    <span class="n26 grey2" id='loan_bank_name_<?php echo $v->id;?>'><?php echo $v->bank_name;?></span>
					<b class="redLight" style="margin-right: 2%;"><?php if($v->type==1){echo '信用卡';}else{ echo '储蓄卡';}?>
					</b>
					<span class="n22 grey4" id="loan_card_<?php echo $v->id;?>"><?php echo '尾号'.substr($v->card,-4);?></span>
                    <input type="radio" name="" />
                </li>
               
                <?php endforeach;?>
                <?php endif;?>     
          
            </ul>
       </div>
	   <div class="layer_border overflow noBorder layer1" id="gyh_mask" style="display:none;">
            <p class="n28 mb30 padlr625">借款额度不能大于担保额度哦！快去购买担保卡获得担保额度吗！</p>
			
            <div class="border_top_2 nPad overflow">
                <a class="n30 boder_right_1 text-center" id='next_loan'><span class="grey2">返回</span></a>
                <a href='/dev/guarantee' class="n30 red text-center bRed" id="second_loan"><span class="white">去购买</span></a>
            </div>
        </div>
       </div>
       </form>
	   <script>
	   $(window).load(function(){
			var lineH = $('.highlight img').height();
			$('.bank_cont').css('lineHeight',lineH + 'px');
		});
	   $('.Hmask').click(function(){
			$('.layer').css('display','none');
			$('#gyh_mask').css('display','none');
			$('.layer2').css('display','none');
	   });
	    function hlz(){
			$('.Hmask').css('display','block');
			$('.layer').css('display','block');
		}
		$('#next_loan').click(function(){
			$('.Hmask').css('display','none');
			$('#gyh_mask').css('display','none');
		});
		$('#button_qd').click(function(){
		   $amount = $('#amount').val();

           $zamount = $('#button_ids').val();
		   //alert($zamount);
		   if(parseInt($amount)>parseInt($zamount)){
			  //alert('借款额度不能大于担保额度哦！快去购买担保卡获得担保额度吗！');
			  $('.Hmask').css('display','block');
			  $('#gyh_mask').css('display','block');
		      return false;
		   }
		});

		$('.Hmask').click(function(){
           $('.Hmask').hide();
		   $('.layer').hide();
		});

		function ajax(num,user_id){
		   //alert(user_id);
		   
		   var img = $('#loan_img_'+num).attr('src');
		   var bank_name = $('#loan_bank_name_'+num).html();
		   var card = $('#loan_card_'+num).html();
		   $('#yhm').html(bank_name);
		   $('#yhk').html(card);
		   $('#bank_ids').attr('value',num);
           $('#user_ids').attr('value',user_id);
		   $('#yhtp').attr('src',img);
		   //alert(card);
		   $('.Hmask').hide();
		   $('.layer').hide();
		  
		}

		$('#loanajax').click(function(){
                if($("#radio-1").val()==1){
                    $("#radio-1").attr('value','0');//没点为0
                }else{
					$("#radio-1").attr('value','1');//点了为1
                }
            });

	   </script>
	   <script>
	   $(function(){
			$('.Hmask').css('display','none');
			$('.layer2').css('display','none');
			$('.layer').css('display','none');
			var lineH = $('.highlight img').height();
			$('.bank_cont').css('lineHeight',lineH + 'px');

			$('#button_qd').click(function(){
				if (!$("#checkbox-1").is(':checked')) {
					alert('必须同意借款协议');
					return false;
				}
			});
			
	   })
	    function qd(){
		 $('.Hmask').css('display','block');
	     $('.layer2').css('display','block');
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

