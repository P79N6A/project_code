
<ul class="payout_record">
    <?php if (!empty($loan_list)): ?>
        <?php foreach ($loan_list as $key => $value) { ?>
            <li class="payout_record_text">
                <span class="payout_num">借款金额 <?php echo sprintf("%.2f", $value['amount']) ?>元</span>
                <span class="payout_time"><?php echo $value['create_time'] ?></span>
                <a href="/borrow/loanup/index?source=<?php echo $source; ?>&loan_id=<?php echo $value->loan_id; ?>">
                    <span class="pay_state">上传</span>
                    <img src="/borrow/310/images/arrow.png" alt="" class="arrow"></a>
            </li>
        <?php } ?>
    <?php else: ?>
        <p style="font-size:0.45rem;width: 100%; text-align: center; padding: 1rem 0; ">您不存在需上传消费凭证截图的借款</p>

    <?php endif; ?>
</ul>
