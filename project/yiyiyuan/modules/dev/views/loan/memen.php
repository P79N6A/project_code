
    <div class="Hcontainer nP">
        <!--<div class="bRed overflow padtb">
            <p class="n28 text-center white mt10">您有<?php echo $dtotal;?>点担保额度，可担保借款<?php echo $guarantee_amount;?>元</p>
            <p class="n22 pink text-center mt10">其中银行收取1%的通道费，与先花一亿元无关</p>
        </div>-->
		<img src="/images/sxed_head.jpg" width="100%">
        <div class=" overflow" style="position: absolute;top: 15px;color:#444; margin:10px 6.25% 0; padding-left:5px;">
            <p class="n28 mt10" style="color:#444;">您有<?php echo $dtotal; ?>点担保额度,可担保借款<span id='money'><?php echo $guarantee_amount; ?></span>元</p>
            <p class="n22 pink mt10" style="color:#c2c2c2;">*银行会扣去您1%的通道费哦！</p>
        </div>

        <div class="main">
            <div class="col-xs-12 nPad">
                <div class="dbk_inpL mt20">
                    <label class="n26">借款用途</label><input type="text">
                </div>
            </div>
            <div class="col-xs-12 nPad mt20">
                <div class="col-xs-6" style="padding-right: 2%;">
                    <div id="qx" class="dbk_inpS">
                        <label>期限(天)</label><input type="text" placeholder="7－21" disabled="disabled">
                        <div class="dis_mask"></div>
                    </div>
                </div>
                <div class="col-xs-6" style="padding-left: 2%;">
                    <div id="geh" class="dbk_inpS">
                        <label class="geh_ques"><a href="#">隔夜还<img src="/images/icon_ques2.png" alt="" width="20%" onclick = "help()"></a></label>
                        <div class="onoffswitch">
                            <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="myonoffswitch" checked>
                            <label class="onoffswitch-label" for="myonoffswitch">
                                <div class="onoffswitch-inner">
                                    <div class="onoffswitch-active"></div>
                                    <div class="onoffswitch-inactive"></div>
                                </div>
                                <div class="onoffswitch-switch"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 nPad">
                <div class="dbk_inpL mt20">
                    <label class="n26">金额（元）</label><input type="text" placeholder="可担保借款<?php echo $guarantee_amount;?>元">
                </div>
            </div>
            <div class="col-xs-12 nPad mt40">            
                <p class="text-right n30">到期应还款<span class="red">0.00</span>元</p>
            </div>
            <button class="btn bgrey mt20 mb40" style="width:100%" disabled="disabled">确定</button>
        </div>
		
		<div class="Hmask" style="display:none;"></div>
        <div class="xhb_layer pad" style="display:none;">
            <img src="/images/icon_wt.png" style="width:30%;position: absolute;top:-84px;left:-5px;width:100px;">
            <p class="n26 mt20"><span class="red">隔夜还</span>为担保卡借款的专属功能，<span class="red">光速出款</span>。借款时间为1天，若您到期未全额还款，一亿元将抵消您的担保额度。</p>
            <button href='' class="btn_red" id="zzdl">朕知道了</button>
        </div>              	
   </div>
<script>
    $(function(){
        $('.onoffswitch-checkbox').click(function(){
            if($('.onoffswitch-checkbox').prop('checked')==true){
                //隔夜还
                setTimeout(function(){
                    $('#qx .dis_mask').css('display','block');
                },300);
                    $('#qx').find('input').attr("disabled",true);
            }else{
                //期限
                setTimeout(function(){
                    $('#qx .dis_mask').css('display','none');
                },300);
                $('#qx').find('input').attr("disabled",false);
            }
        });
    });

	function help(){
	   $('.Hmask').show();
	   $('.xhb_layer').show();
	}

	$('#zzdl').click(function(){
		$('.Hmask').hide();
	   $('.xhb_layer').hide();
	});
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