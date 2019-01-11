<div class="zuoyminew jk_item">
    <div class="daihukan_cont matop0">
        <div class="haimoney">
            <p class="haititle">借款金额（元）</p>
            <p class="haitxt"><?php echo sprintf('%.2f',$loaninfo->amount);?></p>
            <a class="yiyq">已驳回</a>
        </div>
        <div class="rowym addha">
            <div class="corname"><?php if($loaninfo->status!=4&&$loaninfo->status!=17):?>驳回时间<?php else:?>失效时间<?php endif;?></div>
            <div class="corliyou"><?php echo date('Y年m月d日',  strtotime($loaninfo->last_modify_time));?></div>
        </div>
        <?php if($loaninfo->status!=4&&$loaninfo->status!=17):?>
        <div class="rowym addha">
            <div class="corname">驳回理由</div>
            <div class="corliyou"><span class="red"><?php echo empty($loan_flows->reason)?'不符合借款标准':$loan_flows->reason;?></div>
        </div>
        <?php endif;?>
    </div>
    <?php
    if($loaninfo['business_type'] == 4) {
        ?>
        <button type="submit" class="bgrey" onclick="doLoan(1)">再次借款</button>
        <?php
    }else {
        ?>
        <button type="submit" class="bgrey" onclick="doLoan(2)">再次借款</button>
        <?php
    }
    ?>
</div>
<script>
    function doLoan(type) {
        if(type == 1){
            window.location = '/new/loan/index?gua=1';
        }else{
            window.location = '/new/loan';
        }
    }
</script>
