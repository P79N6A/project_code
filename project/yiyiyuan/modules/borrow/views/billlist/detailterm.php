<?php if(!empty($billlistInfo)): ?>
    <div class="w_account_home">
        <div class="w_payAll">
            <span>总待还</span>
            <span>¥<?php echo sprintf('%.2f',$billlistInfo['allrepay']);?></span>
        </div>
        <div class="w_accountTable">
            <div class="w_table1">
                <?php if(isset($billlistInfo['yetbilllist']) && count($billlistInfo['yetbilllist']) == 1):?>
                    <span class="w_radius" data="1" style="display: none"></span>
                    <img src="/borrow/350/images/w_checked.png" alt="" class="w_checkedIcon" style="display:block;" data="1">
                <?php else: ?>
                    <span class="w_radius" data="1"></span>
                    <img src="/borrow/350/images/w_checked.png" alt="" class="w_checkedIcon" style="display:none;" data="1">
                <?php endif; ?>
                <span class="w_txtL">分期方案</span>
                <span class="w_txtR"><?=$billlistInfo['days']?>天×<?=$billlistInfo['terms']?>期</span>
            </div>
            <?php if(isset($billlistInfo['yetbilllist']) && !empty($billlistInfo['yetbilllist'])): ?>
                <?php $count = count($billlistInfo['yetbilllist']); ?>
                <?php foreach ($billlistInfo['yetbilllist'] as $k=>$v): ?>
                    <div class="w_table2">
                        <input type="hidden" name="billids" value="<?=$v['id']?>">
                        <?php if($v['repay_type'] == 1 || $count == 1): ?>
                            <span class="w_radius" data="<?php echo $k+2; ?>"></span>
                            <img src="/borrow/350/images/w_checked.png" alt="" class="w_checkedIcon" style="display:block;" data="<?php echo $k+2; ?>">
                        <?php else: ?>
                            <span class="w_radius" data="<?php echo $k+2; ?>"></span>
                            <img src="/borrow/350/images/w_checked.png" alt="" class="w_checkedIcon" style="display:none;" data="<?php echo $k+2; ?>">
                        <?php endif; ?>
                        <span class="w_txtL"><?=$v['phase']?></span>
                        <span class="w_txt_r"><em>¥<?php echo sprintf('%.2f',$v['actual_amount']);?></em><em>最后还款日<?=date('Y-m-d',strtotime($v['end_time'])-24*3600)?></em></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="w_account_btn" id="subrepay">合并还款</div>
<!--        <div class="w_accout_back"><span>续期还款</span><img src="/borrow/350/images/w_right.png" alt=""></div>-->
        <div class="w_account_pro" onclick="doHelp('/borrow/helpcenter/list?position=12&user_id=<?php echo $userId;?>')">还款遇到问题？</div>
    </div>
<?php endif; ?>
<!-- 弹窗 -->
<div class="popMask" style="display:none;"></div>
<div class="w_account_Box" style="display:none;">
    <img src="/borrow/350/images/delte.png" alt="">
    <p>什么是续期还款</p>
    <div class="w_mask_box">
        <div>1、续期周期以页面展示期限为准。</div>
        <div>2、续期账单起息日以实际续期成功结果为准。（举个例子：您12月1日操作续期成功，本期账单将从12月1日开始往后延长一定时间）</div>
    </div>
</div>
<script src="/borrow/350/javascript/z_scale.js"></script>
<script>
    var count = "<?=$count?>";
    //选择
    $('.w_radius').click(function(){
        var thisElement = $(this).parent('.w_table2');
        var index = $('.w_radius').index(this);
        if(index == 0){
            $('.w_radius').css('display','none');
            $('.w_checkedIcon').css('display','block');
        } else {
            if(index != 1){
                var preInfo = thisElement.prevAll();
                preInfo.each(function () {
                    var datainfo = $(this).find('span').attr('data');
                    if(datainfo !=1){
                        var imgdisplay = $(this).find('img').css('display');
                        if(imgdisplay == 'none'){
                            console.log(datainfo+"none为隐藏");
                            return false;
                        }else{
                            $('.w_checkedIcon').eq(index).css('display','block');
                            if(index == count){
                                $('.w_checkedIcon').eq(0).css('display','block');
                                $('.w_radius').eq(0).css('display','none');
                            }
                        }
                    }
                });
            }else{
                $(this).css('display','none');
                $('.w_checkedIcon').eq(index).css('display','block');
                if(index == count){
                    $('.w_checkedIcon').eq(0).css('display','block');
                    $('.w_radius').eq(0).css('display','none');
                }
            }
        }
    })
    //取消
    $('.w_checkedIcon').click(function(){
        var thisElementqx = $(this).parent('.w_table2');
        var index = $('.w_checkedIcon').index(this);
        if(index == 0){
            //取消全选，功能暂时关闭
//            $('.w_checkedIcon').css('display','none');
//            $('.w_radius').css('display','block');
        }else if(index == count){
            $(this).css('display','none');
            $('.w_radius').eq(index).css('display','block');
            $('.w_checkedIcon').eq(0).css('display','none');
            $('.w_radius').eq(0).css('display','block');
        }else {
            var nextInfo = thisElementqx.nextAll();
            nextInfo.each(function () {
                var datainfoqx = $(this).find('span').attr('data');
                var imgdisplay = $(this).find('img').css('display');
                if(imgdisplay == 'block'){
                    console.log(datainfoqx+"block选中");
                    return false;
                }else{
                    console.log(datainfoqx);
                    $('.w_checkedIcon').eq(index).css('display','none');
                    $('.w_radius').eq(index).css('display','block');
                }
            });
        }
    });
    //提交还款
    $('#subrepay').click(function(){
        var billids = "";
        $('.w_accountTable img').filter(function(){
            if($(this).css('display')==='block'){
                var id = $(this).parent('div').find('input[name="billids"]').val();
                console.log(id); console.log(billids);
                if(billids != "" && (typeof(billids) != "undefined") && id){
                    billids = billids+","+id;
                }else{
                    billids = id;
                }
                console.log(billids);
            }
        })
        if(billids == ""){
            alert("请选择还款期数");
            return false;
        }else{
            window.location ='/borrow/repay/repaychoose?loan_id=<?php echo $loanId?>'+'&goods_bill='+billids;
        }

    })
    function doHelp(url) {
        setTimeout(function(){
            window.location.href = url;
        },100);
    }
</script>