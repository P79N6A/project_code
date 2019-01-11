<div class="ture_sh">
        <div class="form_wrapper"> 
            <div class="form_left">金额</div> 
            <div class="form_right"> 
                <div class="form_content">
                    <input type="text" name="outincome" id="outincome" placeholder="可提现<?php echo $realIncome;?>元" class="phone-input">
                    <p onclick="showAll()">All</p>
                </div> 
            </div> 
        </div>
        <div class="form_wrapper bankinfoselect"> 
            <div class="form_left">银行卡</div> 
            <div class="form_right idemingz"> 
                <div class="form_content bankinfoshow"><?php echo $bankDefault->bank_name;?><?php if($bankDefault->type==0){echo '银行卡';}else if($bankDefault->type==1){ echo '信用卡';}?> <em>尾号<?php echo substr($bankDefault->card,-4);?></em></div>
                <i></i>
            </div> 
        </div>
        <input type="hidden" name="outbank" value="<?php echo $bankDefault->id;?>" id="outbank">
        <input type="hidden" name="userId" value="<?php echo $userId;?>" id="userId" />
        <div class="sytx_txt"></div>
        <?php if($now_time >= $start_time && $now_time <= $end_time):?>
        <div style="padding: 10px 5%;color:red;">受春节期间（2月5日－2月15日）银行系统影响，收益提现功能暂停服务，敬请谅解</div>
        <?php endif;?>
        <button class="resetpay-sub" id="incomewd" obst="<?php echo $limitStatus;?>" obig="<?php echo $realIncome;?>" >确定提现</button>
    </div>
	
    <div class="highlightchux" style="top:20%">
                <i class="on"></i> 
                <ul class="banksC dBlock">
                <?php if(!empty($bankinfo)): ?>
                <?php foreach ($bankinfo as $bank):?>
                    <li class="selectBank" bk="<?php echo $bank->id;?>" tx="<?php echo $bank->bank_name;?><?php if($bank->type==0){echo '银行卡';}else if($bank->type==1){ echo '信用卡';}?> <?php echo '<em>尾号'.substr($bank->card,-4)."</em>";?>">
                        <img src="/images/bank_logo/<?php echo $bank->bank_abbr;?>.png" width="10%">
                        <span class="n26 grey2"><?php echo $bank->bank_name;?></span><b class="redLight" style="margin-right: 2%;"><?php if($bank->type==0){echo '银行卡';}else if($bank->type==1){ echo '信用卡';}?></b><span class="n22 grey4">尾号<?php echo substr($bank->card,-4);?></span>
                    </li>
                <?php endforeach;?>
                <?php endif;?>
                </ul>
           </div>
           
    <div id="overDiv"  style="display:none;"></div>
    <div id="diolo_warp" class="diolo_warp widchange" style="display:none;height:192px;">
        <p class="title_cz">申请提现成功</p>
        <div class="twotime bankinfotip">预计两小时到您的<?php echo $bankDefault->bank_name;?><?php if($bankDefault->type==0){echo '银行卡';}else if($bankDefault->type==1){ echo '信用卡';}?> <?php echo '<em>尾号'.substr($bankDefault->card,-4)."</em>";?></div>
        <p class="radious_img" id="radious_img"></p>
        <p class="go_on"></p>
        <div class="true_flase">
            <!--a class="flase_qx"  onclick="closeDiv()">取消</a-->
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
             window.location = '/dev/account/income?user_id=<?php echo $userId;?>';
        }
        function showAll(){
            document.getElementById("outincome").value = "<?php echo $realIncome;?>" ;
        }
        $(function(){
			$('.highlightchux').hide();
            $('.bankinfoselect').click(function(){
				$('#overDiv').show();
				$('.highlightchux').show();
            });
			$('.selectBank').click(function(){
				var bkid = $(this).attr('bk');
				$("#outbank").val(bkid);
				var text = $(this).attr('tx');
				$(".bankinfoshow").html(text);
				$(".bankinfotip").html("预计两小时到您的"+text);
				$('#overDiv').hide();
				$('.highlightchux').hide();
			});

        });
    </script>