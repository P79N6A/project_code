
    <style type="text/css">
    /*优惠卷*/
            .Hcontainer_coupon .boldera{height:50px;line-height:50px; font-size:18px; padding-left:40%; font-weight: bold;border-bottom: 1px solid #c4bfbe;}
            .Hcontainer_coupon .boldera span{ display: inline-block; float: right;padding-right: 10%; font-weight: normal; font-size: 14px; color: #e74747;}
            .overflow{overflow: hidden;}
            .Hcontainer_coupon{padding-bottom: 78px;}
            .Hcontainer_coupon .Hmask{width: 100%;height: 100%;background: rgba(0,0,0,.7);position: fixed;top: 0;left:0;z-index: 100;}
            .Hcontainer_coupon .layer{width:95%;position: fixed;top:15%;left:45%;margin-left: -43%;background: #fff;border-radius: 10px;z-index: 110;}
            .Hcontainer_coupon .layer .item,.layer .item{overflow: hidden;position: relative; margin: 10px auto;}
            .Hcontainer_coupon .layer .item input{display:none;}
            .Hcontainer_coupon .layer .choose{float: left;position: absolute;top:65%;margin-top: -20px;}
            .Hcontainer_coupon .layer .available2{margin:0 auto; width:94%;max-width:502px;display:block;}
            .Hcontainer_coupon .layer .price_left{text-align: center;width: 40%; position: absolute;top: 20%;left: 0%;color: #fff;padding: 0;}
            .Hcontainer_coupon .layer .price_left.left3{ left:3%;}
            .Hcontainer_coupon .layer .price_left p.black{ font-size: 20px; font-weight: bold; color: #444;}
            .Hcontainer_coupon .layer .price_left p.black span{ font-size: 14px;}
            .Hcontainer_coupon .layer .price_left p.green{background: #ffc24d;font-size:12px;height: 20px;line-height: 20px;border-radius: 10px;margin: 10px auto 0;text-align: center;width: 60px;}
            .Hcontainer_coupon .layer .price_left p.white{ color: #fff; }
            .Hcontainer_coupon .layer .price_left p.ftsz24{ font-size: 24px;}
            .Hcontainer_coupon .layer .price_left p.baishes{ color: #fff; font-size: 24px;}
            .Hcontainer_coupon .layer .price_left p.basise{ background: #fff; color: #ffc24d;}
            .Hcontainer_coupon .layer .price_left p.bgrf{ background: #fff; color: #f56a45;}
            .Hcontainer_coupon .layer .price_left p.rgbf{ background: #f56a45; color: #fff;}
            .Hcontainer_coupon .layer .price_right{width: 60%;position: absolute;top:18%;right: 2px;font-size: 12px;color: #fff;line-height: 22px; text-align: center;}
            .Hcontainer_coupon .layer .price_right .one_one{ color: #444; font-size: 16px;}
            .Hcontainer_coupon .layer .price_right .one_two{color: #ffc24d}
            .Hcontainer_coupon .layer .price_right .redred{ color: #f56a45;}
            .Hcontainer_coupon .layer .price_right .one_three{color: #c2c2c2;}
    </style>

<div class="Hcontainer">
	<div>
    <img src="/images/banner.png" width="100%"/>
    <ul class="nav_jk overflow">
        <li class="col-xs-6">
            <div class="item on" type="friend">好友借款</div>
        </li>
        <li class="col-xs-6">
            <div class="item" type="danbao">担保借款</div>
        </li>
    </ul>
     </div>
    <div class="main">
    	<?php if($userinfo->status == 3 && $is_auth > 2):?>
    		<div id="current_amount" style="margin-bottom:5px; height:2.8rem; line-height:2.5rem;"><span style="float:left;">可用额度<?php echo sprintf("%.2f", $current_amount);?>点<em style="color:#aaa;">(额度内免筹款)</em></span>
        		<a href="/dev/account/remain"><button style="float:right; width:20%; background:#fff;color:#e74747; border:1px solid #e74747; border-radius:5px;">去提额</button></a>
        	</div>
        <?php endif;?>
        <ul>
            <li class="jk_item on">
                <form class="form-horizontal" role="form" method="post" action="/dev/withdraw/second" id="loan_form">
                	<div class="form-group p_ipt">
                        <div class="col-xs-4"><span id="mon_col">金额（元）</span></div>
                        <div class="col-xs-8 ch">
                            <input type="text" name="amount" value="<?php echo $loan_amount; ?>" id="loan_amount" class="ipt" placeholder="500~10000整"/>
                        </div>
                    </div>
                    <div class="form-group p_ipt">
                        <div class="col-xs-4"><span id="day_col">期限（天）</span></div>
                        <div class="col-xs-8 ch">
                            <input type="text" name="days" value="<?php echo $loan_days; ?>" id="loan_days" class="ipt" placeholder="7~31天"/>
                        </div>
                    </div>
                    <div class="form-group p_ipt">
                        <div class="col-xs-4"><span id="desc_col">借款用途</span></div>
                        <div class="col-xs-8 ch">
                            <input type="text" name="desc" value="<?php echo $loan_desc; ?>" id="loan_desc" class="ipt" placeholder="请输入5~25个字"/>
                        </div>
                    </div>
                    <?php if ($isexist == '1'): ?>
                        <div class="form-group p_ipt grey">
                            <div class="col-xs-4">优惠券</div>
                            <div class="col-xs-8 ch">
                                使用优惠券可减免服务费
                            </div>
                            <i></i>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($couponlist)): ?>
                            <div class="form-group p_ipt highlight">
                                <div class="col-xs-4 red">优惠券</div>
                                <div class="col-xs-8 cor" id="use_coupon">
                                    使用优惠券可减免服务费
                                </div>
                                <i></i>
                            </div>
                        <?php else: ?>
                            <div class="form-group p_ipt grey">
                                <div class="col-xs-4">优惠券</div>
                                <div class="col-xs-8 ch">
                                    使用优惠券可减免服务费
                                </div>
                                <i></i>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <p class="n22 red mb70" id="loan_error_tip"></p>
                    <p class="f30 mt40 text-right">到期应还款：<label class="red n30" id="loan_repay_amount">0.00</label>元</p>
                    <input type="hidden" name="coupon_id" id="coupon_id" value="">
                    <input type="hidden" name="coupon_amount" id="coupon_amount" value="">
                    <input type="hidden" name="coupon_limit" id="coupon_limit" value="">
                    <input type="hidden" name="day_rate" id="day_rate" value='<?php echo $dayratestr;?>'>


                    <!--<?php if ($is_bank == '0'): ?>
                                <button type="button" id="loan_button" <?php if ($isexist == '1'): ?>class="bgrey btn mt20" disabled="disabled"<?php else: ?>class="btn mt20"<?php endif; ?>><?php if ($isexist == '1') { ?>您有未完成的借款<?php } else { ?>确定<?php } ?></button>
                    <?php else: ?>
                                <a href="javascript:;" class="btn mt20 " style="width:100%" id="geh_sure1" value='确定'>确定</a>
                    <?php endif; ?>-->
                    <button type="button" id="loan_button" <?php if ($isexist == '1'): ?>class="bgrey btn mt20" disabled="disabled"<?php else: ?>class="btn mt20"<?php endif; ?>><?php if ($isexist == '1') { ?>您有未完成的借款<?php } else { ?>确定<?php } ?></button>

                    <?php if ($isexist == '1'): ?><a href="/dev/loan/succ?l=<?php echo $loan_id; ?>"><button type="button" class="btn1 mt20" style="width:100%;" >查看当前借款</button></a><?php endif; ?>
                    <div>
                            <img src="/images/1001.png" width="100%" style="margin-top:15px;">
                            <p style="line-height:25px;">
                                <span>第一步：</span>申请好友借款
                                <p style="color:#878787;">邀请三位好友对你发起的借款进行信用投资。且6小时内筹满。</p>
                                <p style="width:100%;"><img width="100%" src="/images/zz1.png?v=2015102801"></p>
                                <span >第二步：</span>好友投资筹满<br/>
                                 <p style="color:#878787;">当信用点筹满后，且通过审核，“先花一亿元”将在1个工作日内放款，借款完成。</p>
                                <p style="width:100%;"><img width="100%" src="/images/zz2.png"></p>
                                <span style="text-align:center;display:block;" >or</span>
                                <p style="color:#878787;">若6小时内未筹满。但已筹得大于100信用点时，可根据已筹到的信用点进行借款。</p>
                                <p style="width:100%;"><img width="100%" src="/images/zz3.png"></p>
                                <p style="width:100%;"><img width="100%" src="/images/zz4.png"></p>
                            </p>
                   </div>   
                </form>
            </li>
            <li class="jk_item">
                <div class="text-center">
                    <!--这里为点击跳转链接-->
                    <?php if ($exist == '1'): ?>
                        <a href="/dev/loan/borrowing"><img src="/images/dbk.png" width="70%"></a>
                    <?php else: ?>
                        <a href="/dev/loan/mdbk"><img src="/images/dbk.png" width="70%"></a>
                    <?php endif; ?>    
                    <?php if ($userinfo->user_type == 1): ?>
                        <a href="javascript:void(0);" id="verifyGuater"><img src="/images/dbr.png" width="70%" class="mt20"></a>
                    <?php endif; ?>
                    <!--/dev/loan/guarantee-->
                </div>
            </li>
        </ul>
    </div>
    <?= $this->render('/layouts/_page', ['page' => 'loan']) ?>

     
        <div class="Hcontainer_coupon" style="display:none">
        <div class="Hmask"></div>
        <div class="layer overflow" style="position:absolute;">
            <div class="boldera">优惠券 <span id="use_loan_coupon" class="queding">确定</span></div>
            <div class="content padlr">
            
                <?php if (!empty($couponlist)): ?>
                <?php foreach ($couponlist as $key => $value): ?>
                <div class="item">
                    <img src="/images/unchoosered.png" class="available2">
                    <div class="price_left">
                        <?php if ($value['val'] != 0): ?><p class="black ftsz24"><?php echo intval($value['val']); ?><span>元</span><?php else: ?><p class="black ftsz24">全免<span>券</span><?php endif; ?></p>
                        <p class="green rgbf">好友借款</p>
                    </div>
                    <div class="price_right">
                        <p class="one_one"><?php echo $value['title']; ?></p>
                        <p class="redred"><?php if ($value['limit'] == 0): ?>不限金额<?php else: ?>满<?php echo $value['limit']; ?>元可用<?php endif; ?></p>
                        <p class="one_three">有效期：<?php echo date('Y' . '年' . 'm' . '月' . 'd' . '日', (strtotime($value['end_date']) - 24 * 3600)); ?></p>
                    </div>
                    <input type="radio" name="discount" cid="<?php echo $value['id']; ?>" min="<?php echo intval($value['limit']); ?>" value="<?php echo $value['val']; ?>" id="radio-<?php echo $key + 1; ?>">
                </div>
                <?php endforeach; ?>
                <?php endif; ?> 
                
            </div>                    
        </div>
    </div>



    <div class="layer_border layer3" style="display: none;">
        <div class="padlr625">
            <p class="n30 text-indent">您尚未绑定借记卡，不能完成借款，快去添加吧。</p>
        </div>
        <div class="clearfix"></div>
        <div class="border_top_red mt20 text-center">
            <a href="/dev/bank/addcard" class="n26 bRed borRad5" style="display:block;width:80%;margin: 10px auto;margin-left: 10%;"><span class="white">去绑定</span></a>
        </div>
    </div>
    <div class="yhj" style="display: none;">
        <img src="/images/yinhj2.png" width="100%">
        <img src="/images/icon_close3.png" class="yhj_close">
		<a class="ing_imgone"  href="/dev/activity/banker"
		 style="display: block;width: 40%;height: 38px;position:absolute; bottom: 12px;left: 29%;"></a>
    </div>

</div>


<script type="text/javascript">

    var user_id = "<?php echo $userinfo->user_id; ?>";
    $('.Hmask').click(function() {
        $('.Hmask').toggle();
        $('.layer_border').css('display', 'none');
        $('.yhj').css('display', 'none');
    });
    $('.yhj_close').click(function() {
        $('.yhj').css('display', 'none');
        $('.Hmask').toggle();
    });

    $(document).ready(function() {
//底部导航
        $('input').focus(function() {
            $('footer').css('display', 'none');
        });
        $('input').blur(function() {
            $('footer').css('display', 'block');
        });
//点击切换
        $('.nav_jk .item').each(function(index) {
            $(this).click(function() {
                $('.nav_jk .item').removeClass('on');
                $(this).addClass('on');
                if($(this).attr('type') == 'danbao'){
					$('#current_amount').hide();
                }else{
                	$('#current_amount').show();
                }
                $('.jk_item').removeClass('on');
                $('.jk_item').eq(index).addClass('on');
            });
        });
        $('#geh_sure1').click(function() {
            $('.Hmask').show();
            $('.layer3').css('display', 'block');
        });
        //点击黑色层，弹层内容消失
        $('.Hmask').click(function() {
            $('.layer').hide();
            $('.layer3').hide();
        })
    })
</script>
<script>
    $("#use_coupon").click(function() {
        $(".Hmask").css('display', 'block');
        $(".layer").css('display', 'block');
//         $('.Hcontainer .yhq_btn').css('background', '');
    	$(".Hcontainer_coupon").css('display', 'block');
//         var width = $('.available2').width();
//         $('.layer .item').css('width', width);
//         //点击关闭按钮
        $('.Hcontainer_coupon .queding').click(function() {
            setTimeout(function() {
                $('.Hcontainer_coupon').css('display', 'none');
                $('.Hmask').css('display', 'none');
            }, 100)

        });
    });
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