<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title></title>
    <link rel="stylesheet" type="text/css" href="/css/dev/activityrest.css"/>
    <link rel="stylesheet" type="text/css" href="/css/dev/activityindex.css"/>
    <script src="/js/dev/activityjs.js"></script>
    <script>
        $(function(){
            $('.actvete .dwlhbao').click(function(){
                $('#overDivs').show();
                $('.tanchuceng').show();
            });
        })
    </script>
</head>

<body>
<div class="actvete">
    <div class="bannerimg" >
        <img src="/images/dev/tebanner.jpg">
    </div>

    <div class="buttxt">
        <?php if($count<3):?>
        <div class="mestishi">
            <h3>邀请3位好友即可参与</h3>
            <p>已邀请<?php echo $count?>/3</p>
        </div>
        <button class="dwlhbao">领取免息券</button>
        <?php else:?>
        <div class="mestishi">
            <p>您已经成功获得 </p>
            <h3>198元免息券</h3>
        </div>
        <button class="dwlhbao">发福利给好友</button>
        <?php endif;?>
        <div class="certifn hdguize">
            <div class="bortop"></div>
            <h3>活动规则</h3>
            <p>1. 活动时间：2017年6月30日--7月19日（含）</p>
            <p>2. 点击活动页中按钮开始参与活动，将活动页分享至好友或朋友圈后，成功邀请注册三人，即可点击红包领取198元免息券。</p>
            <p>3. 活动所获得的优惠券系统会自动发送到用户的账户中，优惠券只有在优惠券标示的有效期内使用才会获得相应金额的优惠。</p>
            <p>4. 7月03日24时，系统将在所有参与活动的用户中随机抽取幸运用户获得惊喜福利，7月19日24时系统还将在所有参与活动的用户中（第一批获奖用户无法再次参与）抽取第二批幸运用户获得惊喜福利。</p>
            <p>5. 奖项包含：</p>
            <p>7月03日奖项----<span>688元现金红包、小蚁4K运动相机、华为P10</span></p>
            <p>7月19日奖项---- <span>688元现金红包、1000元提额券、免还款名额；</span>  </p>
            <p>6. 获得小蚁4K运动相机、华为P10、688元现金红包、提额券和免还款名额的用户会在被系统选中后后收到系统发送的提醒短信（转发无效）；小蚁4K运动相机、华为P10、688元现金红包，获得提额券的用户会在活动结束后3个工作日内，由系统统一进行提额。</p>
            <p>7. 本次活动中获得免息券的用户，若在当次还款时逾期，先花一亿元可单方面取消其优惠金额，提额用户若在当次还款时逾期，先花一亿元可单方面取消其提额额度。</p>
            <p>Ps：活动奖励发放过程中，工作人员不会向用户收取任何手续费或其他费用</p>

        </div>
    </div>

</div>
</body>
</html>
<script>
    var type = "<?php echo $type; ?>";
    $('.dwlhbao').click(function () {
        if (type == "app") {
            window.myObj.bannerShare();
        }
    });
    function bannershare() {
        //alert("fff");
    }

</script>