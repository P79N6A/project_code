<img src="/sevenday/images/bannerbg.png">
<img class="edu" src="/sevenday/images/edu.jpg">
<div class="eduText">
    <div>借款周期</div>
    <div class="date">7天</div>
</div>
<div class="buttonyi" onclick="getCredit()">
    立即借款
</div>
<script type="text/javascript">
    function getCredit() {
        zhuge.track('首页-获取额度');
        location.href = '/day/loan';
    }
</script>
<style>
    .buttonyi{
        margin: 0 5%;
        margin-top: 20px;
        background: #fe5400;
        color: #fff;
        font-size:16px; 
        padding: 10px 0; 
        border-radius: 50px;
        background:-webkit-linear-gradient(left,#fc8700,#fe5400);
        text-align: center;
    } 
    .eduText{
        width: 100%;
        padding-top: 20px;
        background: #fff;
        display: flex;
        justify-content:flex-start;
        align-items:center;
    }
    .eduText div{
        font:16px/45px "微软雅黑";
        color: #000;
        height: 45px;
        margin-left: 20px;
    }
    .eduText .date{
        padding: 0 18px;
        color: #fff;
        height: 30px;
        font:16px/30px "微软雅黑";
        border-radius: 15px;
        background:-webkit-linear-gradient(left,#fc8700,#fe5400);
    }
</style>