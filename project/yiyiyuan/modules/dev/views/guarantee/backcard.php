<?php
$bank = array('ABC', 'ALL', 'BCCB', 'BCM', 'BOC', 'CCB', 'CEB', 'CIB', 'CMB', 'CMBC', 'GDB', 'HXB', 'ICBC', 'PAB', 'PSBC', 'SPDB', '中信');
?>
<div class="tuika tuika">
        <div class="woytuika">将退<em><?php echo sprintf( "%.2f",$cardOrder->remain_amount);?></em>点担保卡至您的购卡中</div>
        <div class="form_wrapper bank_nn"> 
            <img src="/images/bank_logo/<?php
                            if (!empty($cardBank['bank_abbr']) && in_array($cardBank['bank_abbr'], $bank)) {
                                echo $cardBank['bank_abbr'];
                            } else {
                                echo 'ALL';
                            }
                            ?>.png" width="10%">
            <span class="n26 grey2"><?php echo $cardBank->bank_name; ?></span>
            <span class="redLight"><?php echo $cardBank->type == 0 ? '借记卡' : '信用卡'; ?></span>
            <span class="grey4">尾号<?php echo substr($cardBank->card, strlen($cardBank->card) - 4, 4) ?></span>
        </div>
        <div class="form_wrapper"> 
            <div class="form_left">手机号</div> 
            <div class="form_right"> 
                <div class="form_content">
                    <?php echo substr($cardBank->bank_mobile, 0,3)."****".substr($cardBank->bank_mobile, 7);?>
                </div> 
            </div> 
        </div>
        <div class="free_code">
        	<input type="hidden" name="coid" value="<?php echo $cardOrder->id;?>" id="coid" />
            <input type="text" name="bccode" placeholder="验证码" class="yzm_input" id="bccode">
            <?php if($now_time >= $start_time && $now_time <= $end_time):?>
            <button class="btn code-obtain" disabled id="backcardGetcode" mb="<?php echo $cardBank->bank_mobile;?>" lt="<?php echo $limitStatus; ?>" ra="<?php echo sprintf( "%.2f",$cardOrder->remain_amount);?>">获取验证码</button>
            <?php else:?>
            <button class="btn code-obtain" id="backcardGetcode" mb="<?php echo $cardBank->bank_mobile;?>" lt="<?php echo $limitStatus; ?>" ra="<?php echo sprintf( "%.2f",$cardOrder->remain_amount);?>">获取验证码</button>
            <?php endif;?>
        </div>
        <?php if($now_time >= $start_time && $now_time <= $end_time):?>
        <div style="padding: 10px 5%;color:red;">受春节期间（2月5日－2月15日）银行系统影响，收益提现功能暂停服务，敬请谅解</div>
        <?php endif;?>
        <?php if($cardOrder->user_id == 284574 || $cardOrder->user_id == 318310 || $cardOrder->user_id == 562548 || $cardOrder->user_id == 555837 || $cardOrder->user_id == 562779 || $cardOrder->user_id == 622498 || $cardOrder->user_id == 1788313):?>
        <button class="resetpay-sub" id="backcard" disabled="disabled" >确定赎回</button>
        <?php else:?>
        <button href="javascript:;" class="resetpay-sub" id="backcard" lt="<?php echo $limitStatus; ?>" >确定赎回</button>
        <?php endif;?>
    </div>

     <div id="overDiv"  style="display:none;"></div>
    <div id="diolo_warp" class="diolo_warp zhibuytk"style="display:none;">
        <p class="txt14" id="txt14_one">为保障您的资金安全，</p>
        <p class="txt14" id="txt14_second">请于07:00-24:00进行退卡操作.</p>
        <p class="radious_img"></p>
        <p class="go_on"></p>
        <div class="true_flase">
            <a class="true_qr" onclick="closeDiv()">确定</a>
        </div>
    </div>  
    
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
    
    <script type="text/javascript">
        function show(){
            document.getElementById("overDiv").style.display = "block" ;
            document.getElementById("diolo_warp").style.display = "block" ;
        }
        function closeDiv(){
            document.getElementById("overDiv").style.display = "none" ;
             document.getElementById("diolo_warp").style.display = "none" ;
        }
    </script>