<!DOCTYPE html>
<?php
$url = '/new/account/distribute';
$newurl = '/new/account/renzheng';
?>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>我的资料</title>
    <link rel="stylesheet" type="text/css" href="/newdev/css/coupon/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/newdev/css/coupon/style302.css"/>
    <script type="text/javascript"></script>
</head>
<body>
<div class="my_ziliao">
    <h3>借款必填</h3>
    <div class="newself">
        <a href='<?php echo $pinfo == '修改' ?  $url.'?type=2&from=nameauth' : $url.'?type=1&from=nameauth'; ?>'>
            <div class="dbk_inpL">
                <label>实名认证</label>
                <?php if($pinfo == '修改'){?>
                    <p><img src="/newdev/images/yyy302/rightgray.png"><span>已认证</span> </p>
                <?php }else{?>
                    <p class="black rightred"><img src="/newdev/images/yyy302/rightred.png"><span>去认证</span> </p>
                <?php }?>
            </div>
         </a>
        <a href='<?php echo $cinfo == '修改' ?  $url.'?type=2&from=workinfo' : $url.'?type=1&from=workinfo'; ?>'>
                <div class="dbk_inpL">
                    <label>工作信息</label>
                <?php if($cinfo == '修改'){?>
                    <p><img src="/newdev/images/yyy302/rightgray.png"><span>已认证</span> </p>
                <?php }else{?>
                    <p class="black rightred"><img src="/newdev/images/yyy302/rightred.png"><span>去认证</span> </p>
                <?php }?>
            </div>
        </a>
        <a href='<?php echo $userinfo->status == 4 ||  $userinfo->status == 1  ?  $url.'?type=1&from=pic' : '#'; ?>'>
            <div class="dbk_inpL">
                <label>视频认证</label>
                <p>已认证</p>
            </div>
         </a>
        <div class="dbk_inpL">
            <label>联系人信息</label>
            <p >已认证</p>
        </div>
        <div class="dbk_inpL">
            <label>手机号认证</label>
            <p >已认证</p>
        </div>
    </div>
    <h3>提额必填</h3>
    <div class="newself">
        <div class="dbk_inpL">
            <label>学历</label>
            <p ></p>
            <p class="black rightred"><img src="/newdev/images/yyy302/rightred.png"><span>去认证</span> </p>
        </div>
        <div class="dbk_inpL">
            <label>社保</label>
            <p class="black rightred"><img src="/newdev/images/yyy302/rightred.png"><span>去认证</span> </p>

        </div>
        <div class="dbk_inpL">
            <label>公积金</label>
            <p class="black" ><img src="/newdev/images/yyy302/rightgray.png"><span>去认证</span> </p>
        </div>
        <div class="dbk_inpL">
            <label>信用卡</label>
            <p>已认证</p>
        </div>
        <div class="dbk_inpL">
            <label>京东卡认证</label>
            <p >已认证</p>
        </div>
    </div>
</div>


</body>
</html>