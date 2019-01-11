<style>
    .can-title{
        text-align: center;
        background: #ffffff;
        padding: 2.5rem 0;
            box-sizing: border-box;
    }
    .waiting_img{
        width: 1rem;
    }
    .can-title p{
        font-size: .35rem;
    color: #a2a2ae;
    line-height: 1.7;
        letter-spacing: 2px;
    }
    .can-title .one{
            font-size: 0.47rem;
    color: black;
    padding: .2rem 0;
    font-weight: 600;
    }
</style>
<div class="jihuo_wrap">
    <div class="can-title">
        <div class="timer" id="timer"></div>
<!--        <img class="depository_waiting_img" style="display: table;margin: 0 auto;width: 2.2rem;" src="/borrow/310/images/waitingIcon.png">
        <p class="one" style="color: #555;font-size: 1rem;">借款审核中</p>
        <p style="font-size: 0.9rem;">培养良好的习惯，可提高额度申请成功率</p>
        <p style="margin-top:-13px;font-size: 0.9rem;">预计24小时审核完成</p>-->
        
        <img class="waiting_img"  src="/borrow/310/images/waitingIcon.png">
        <p class="one" >借款审核中</p>
        <p >培养良好的习惯，可提高额度申请成功率</p>
        <p >预计24小时审核完成</p>
    </div>
    <div class="jh-btom"></div>
</div>
<?= $this->render('/layouts/footer', ['page' => 'loan','log_user_id'=>$user_id]) ?>



