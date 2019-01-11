<?php \app\common\PLogger::getInstance('weixin', '', $user_id); ?>
<?php $json_data = \app\common\PLogger::getJson(); ?>
<script>
    var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');
</script>
<style>
    .alert{
        position: fixed;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,.3);
        z-index: 999;
        top: 0;
        left: 0;
    }
    .box{
        width: 70vw;
        height: 50vw;
        background: #ffffff;
        margin: 50vw auto 0;
        border-radius: 0.3rem;
        text-align: center;
        color: #4d4c4c;
    }
    .box h4{
        font-size: 0.46rem;
        padding: 0.35rem;
    }
    .box p{
        font-size: 0.4rem;
        text-align: left;
        padding: 0 0.3rem;
        box-sizing: border-box;
        line-height: 1.5;
    }
    .btn{
        width: 30vw;
        height: 10vw;
        background: #999;
        /*background: red;*/
        margin: 0.4rem auto;
        border-radius: 0.5rem;
        text-align: center;
        line-height: 10vw;
        color: #ffffff;
        font-size: 0.35rem;
        display: inline-block;
    }
    .box span{
        position: relative;
        left: 0.4rem;
        font-size: 0.4rem;
        color: red;
    }
    .bill-active{
        width: 100%;
        padding: 0 0.43rem;
        background: #fff;
        box-sizing: border-box;
    }
    .bill-one {
        padding-bottom: 0.59rem;
        margin-bottom: 0.27rem;
        display: flex;
        justify-content: space-between;
    }
    .bill-repay-btn {
        background: -webkit-linear-gradient(-90deg, #ff4b17 0%, #f00d0d 100%);
        background: linear-gradient(-90deg, #ff4b17 0%, #f00d0d 100%);
        -webkit-border-radius: 0.13rem;
        border-radius: 0.13rem;
        width: 2.35rem;
        height: 0.96rem;
        line-height: 0.96rem;
        color: #fff;
        display: block;
        float: right;
        margin-top: 0.8rem;
        font-size: 0.37rem;
        text-align: center;
        margin-right:0;
    }
    .bill-one:after{
        display: none;
    }
</style>

<div class="bill-wrap">
    <div class="bill-repayment-area">
        <label class="bill-repay">待还总金额</label>
        <div class="area-wrap">
            <i class="bill-money-icon">￥</i>
            <span class="bill-money-num"><?php echo $total_amount?></span>
            <a href="/borrow/tradinglist/index" class="bill-loan">借款记录 ></a>
        </div>
    </div>
    <div class="bill-repayment-bill">
        <p class="bill-repay1">待还账单<span><?php echo $loan_count?></span>笔</p>
        <p class="bill-repay2">逾期账单<span><?php echo $expect_num?></span>笔</p>
    </div>
    <?php if($ious_id==0 && $loan_id==0){ ?>
     <div class="bill-empty">
        <img src="/borrow/310/images/bill-nobill.png" alt="">
        <p>当前没有待还账单哦~</p>
    </div>
    <button class="bill-borrow">借一笔</button>
    <?php } ?>
    <div class="bill-state-wrap">
        <!-- 激活账单块 -->
        <?php if($ious_id != 0):?>
        <div class="bill-active">
            <div class="bill-active-top">
                <p class="active-bill icon1">激活账单
                    <input name = "billtype" type="hidden" value="激活账单">
                    <?php if($ious_expect_status==2){?>
                        <span class="repay">（已逾期）</span></p>
                        <input name = "billstatus" type="hidden" value="已逾期">
                        <p class="repay-time">逾期<?php echo $ious_expect_day ?>天</p>
                    <?php }else{ ?>
                        <input name = "billstatus" type="hidden" value="待还款">
                        <p class="repay-deadline">最后还款日 <?php echo $ious_last_day;?></p>
                <?php } ?>
            </div>
            <div class="bill-one">
                <div class="bill-one-left">
                    <label>待还金额</label>
                    <i>￥</i>
                    <span><?php echo $ious_amount?></span>
                </div>
                <?php if($ious_status==1){?>
                <button id="ious_ljhg" class="bill-repay-btn" >立即还款</button>
                <?php }elseif($ious_status==2){?>
                <!-- D1-账单5 -->
                <div class="bill-confirm" >
                    <p class="confirm-state">还款确认中</p>
                    <input name = "billstatus" type="hidden" value="还款确认中">
                    <p class="confirm-time"><?php echo $ious_time_desc;?></p>
                </div>
                <?php }?>
            </div>
        </div>
        <?php endif;?>
        <!-- 借款账单块 -->
        <?php if($loan_id != 0):?>
        <div class="bill-active">
            <div class="bill-active-top">
                <p class="active-bill icon2">
                <?php if($business_type == 9){ ?>
                  商城账单
                    <input name = "billtype" type="hidden" value="商城账单">
                <?php }else{ ?>
                  借款账单
                    <input name = "billtype" type="hidden" value="借款账单">
                <?php } ?>
                <?php if($loan_expect_status==2){?>
                    <span class="repay">（已逾期）</span></p>
                    <p class="repay-time">逾期<?php echo $loan_expect_day; ?>天</p>
                    <input name = "billstatus" type="hidden" value="已逾期">
                <?php } ?>
                <?php if($loan_status==3){?>
                    <p class="repay-deadline">续期申请处理中</p>
                    <input name = "billstatus" type="hidden" value="续期中">
                <?php } ?>
                <?php if($loan_status==4){?>
                    <span class="renewal">（已续期）</span></p>
                    <input name = "billstatus" type="hidden" value="已续期">
                <?php } ?>
                <?php if (($loan_status==1 || $loan_status==4) && $loan_expect_status!=2 ){?>
                    <p class="repay-deadline">最后还款日 <?php echo $loan_last_day; ?></p>
                    <input name = "billstatus" type="hidden" value="待还款">
                <?php } ?>

            </div>
            <div class="bill-one">
                <div class="bill-one-left">
                    <label>待还金额</label>
                    <i>￥</i>
                    <span><?php echo $loan_amount;?></span>
                </div>
                <?php if($loan_status==1 || $loan_status==4){?>
                     <button id="loan_ljhg" class="bill-repay-btn">立即还款</button>
                    <input name = "billstatus" type="hidden" value="待还款">
                <?php }elseif($loan_status==2){?>
                    <div  class="bill-confirm">
                        <p class="confirm-state">还款确认中</p>
                        <input name = "billstatus" type="hidden" value="还款确认中">
                        <p class="confirm-time"><?php echo $loan_time_desc; ?></p>
                    </div>
                <?php }elseif($loan_status==3){ ?>
                    <div  class="bill-confirm">
                        <p class="confirm-state">续期中</p>
                        <input name = "billstatus" type="hidden" value="续期中">
                        <p class="confirm-time"><?php echo $loan_time_desc; ?></p>
                    </div>
               <?php }?>
            </div>
        </div>
        <?php endif;?>
    </div>
    <?= $this->render('/layouts/footer', ['page' => 'bill','log_user_id'=>$user_id]) ?>
</div>
<div class="bill-mask" style="display: none"></div>
<div class="bill-popup" hidden>
    <img class="bill-close" src="/borrow/310/images/bill-close.png" alt="">
    <img class="bill-isok" src="/borrow/310/images/bill-ok.png" alt="" id="bill_ok">
    <img class="bill-isok" src="/borrow/310/images/failIcon.png" alt="" style="display: none;" id="bill_error">
    <p class="popup-tips" id="alert_title"></p>
    <p class="popup-txt" id="alert_desc"></p>
    <button class="bill-popup-btn" id="sync">再借一笔</button>
</div>
<style>
    .help_service{
        position: absolute;
        width: 100%;
        left: 0;
        bottom: 1.81rem;
        height: 0.37rem;
        text-align: center;
    }
    .contact_service_tip{
        width: 0.40rem;
        height: 0.43rem;
        position: absolute;
        left: 3.97rem;
        top: 0;
    }
    .contact_service_text{
        height: 0.37rem;
        position: absolute;
        left:4.59rem;
        font-family: "微软雅黑";
        font-size: 0.37rem;
        color: #3D81FF;
        letter-spacing: 0;
        line-height: 0.43rem;
    }
</style>
<div class="help_service" style="z-index:-1">
    <img src="/borrow/310/images/tip.png" alt="" class="contact_service_tip">
    <a href="javascript:void(0);" onclick="doHelp('/borrow/helpcenter/list?position=12&user_id=<?php echo $user_id;?>')"><span class="contact_service_text">获取帮助</span></a>
</div>
<script type="text/javascript">
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
    var uid = "<?php echo $user_id; ?>";
    zhuge.track('账单列表页面', {
        '来源': "首页查看账单按钮",
        'user_id': uid,
    });
    $("#ious_ljhg").click(function () {
        tongji('billlist_ious_ljhg', baseInfoss);
        zhuge.track('账单列表页-立即还款按钮');
        var url = '<?php echo $ious_url;?>';
        setTimeout(function(){
           window.location =url;
        },100);
        
    });
    $(".bill-borrow").click(function () {
        tongji('billlist_jieyibi',baseInfoss);
        
        setTimeout(function(){
           window.location ='/borrow/loan/index';
        },100);
    });
    $('.bill-active').click(function(){
        var billtype = $('input[name="billtype"]').val();
        var billstatus = $('input[name="billstatus"]').val();
        zhuge.track('账单列表-点击事件', {
            '账单类型' : billtype,
            '账单状态' : billstatus,});
    })
    $("#loan_ljhg").click(function () {
        tongji('billlist_loan_ljhg',baseInfoss);
        zhuge.track('账单列表页-立即还款按钮');
        setTimeout(function(){
           window.location ='/borrow/billlist/detail?loan_id=<?php echo $loan_id?>';
        },100);
    });
    $(function(){
        $('#sync').css("background","red");
        var loan_dialog_status=<?=$loan_dialog_status;?>;
        var dialog_desc="<?=$dialog_desc;?>";
       if(loan_dialog_status==1){//还款失败
           $('#alert_title').text('还款失败');
           $('#sync').text("知道了");
           $('#bill_ok').hide();
           $('#bill_error').show();
           $('#sync').click(function(){
               $('.bill-mask').hide();
               $('.bill-popup').hide();
           });
           $('.bill-mask').show();
           $('.bill-popup').show();
       }else if(loan_dialog_status==2){//续期成功
           $('#alert_title').text('续期成功');
           $('#sync').text("知道了");
           $('#alert_desc').text(dialog_desc);
           $('#bill_ok').show();
           $('#bill_error').hide();
           $('#sync').click(function(){
               $('.bill-mask').hide();
               $('.bill-popup').hide();
           });
           $('.bill-mask').show();
           $('.bill-popup').show();
       }else if(loan_dialog_status==3){//还款成功(部分)
           $('#alert_title').text('还款成功');
           $('#alert_desc').text(dialog_desc);
           $('#sync').text("继续还款");
           $('#bill_ok').show();
           $('#bill_error').hide();
           $('#sync').click(function(){
               $('.bill-mask').hide();
               $('.bill-popup').hide();
           });
           $('.bill-mask').show();
           $('.bill-popup').show();
       }else if(loan_dialog_status==4){//还款成功(全部)
           $('#alert_title').text('还款成功');
           $('#alert_desc').text(dialog_desc);
           $('#sync').text("再借一笔");
           $('#bill_ok').show();
           $('#bill_error').hide();
           $('#sync').click(function(){
               zhuge.track('借款首页', {
                   '来源': '侧导航还款成功再借一笔按钮',
                   '状态': '额度已过期',
               });
               window.location ='/borrow/loan/index';
           });
           $('.bill-mask').show();
           $('.bill-popup').show();
       }else if(loan_dialog_status==5){//白条未还清且亿元已还清
           $('#alert_title').text('还款成功');
           $('#alert_desc').text(dialog_desc);
           $('#sync').text("查看账单列表");
           $('#bill_ok').show();
           $('#bill_error').hide();
           $('#sync').click(function(){
               $('.bill-mask').hide();
               $('.bill-popup').hide();
           });
           $('.bill-mask').show();
           $('.bill-popup').show();
       }else if(loan_dialog_status==6){//续期失败
           $('#alert_title').text('续期失败');
           $('#alert_desc').text(dialog_desc);
           $('#sync').text("知道了");
           $('#bill_ok').hide();
           $('#bill_error').show();
           $('#sync').click(function(){
               $('.bill-mask').hide();
               $('.bill-popup').hide();
           });
           $('.bill-mask').show();
           $('.bill-popup').show();
       }
    })
    $('.bill-close').click(function () {
        $('.bill-mask').hide();
        $('.bill-popup').hide();
    });
    function doHelp(url) {
        tongji('do_help',baseInfoss);
        setTimeout(function(){
            window.location.href = url;
        },100);
    }
</script>
