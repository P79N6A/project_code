<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>还款卷</title>
    <link rel="stylesheet" type="text/css" href="/newdev/css/coupon/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/newdev/css/coupon/style302.css"/>
</head>
<body>
<div class="yyhuqmeg">
   <?php if(!empty($couponlist)) {?>
       <?php foreach ($couponlist as $key=>$value): ?>
           <div class="vipquan hkquan" onclick="changeCoupon(<?=$value['id']?>)">
               <div class="vipqzuo bsered">
                   <h3 class="h3chagebh"><em><?=$value['val']?></em>元 <?=$value['title']?></h3>
                   <p class="yxioqi">有效期：至<?=date('Y',strtotime($value['end_date'])-24*3600)?>年<?=date('m',strtotime($value['end_date'])-24*3600)?>月<?=date('d',strtotime($value['end_date'])-24*3600)?>日</p>
               </div>
               <div class="vipqyou changegb">
                   借<br/>
                   款<br/>
                   券
               </div>
               <div class="main">
                   <div class="noehtyxh">
                       <input type="checkbox" id="checkbox-<?= $value['id']?>" class="regular-checkbox" >
                       <label id="checkbox-<?= $value['id']?>" for="checkbox-<?= $value['id']?>" class="after"></label>
                   </div>
               </div>
           </div>
       <?php endforeach; ?>
   <?php }else{ ?>
       <div id="znawu1" class="bdfalse">
           <img src="/newdev/images/yyy302/none.png">
           <p>暂无可用优惠券</p>
       </div>
    <?php }?>

</div>
</body>
<script>
var changeCoupon = function (couponId) {
$('#checkbox-'+couponId).after();
window.location.href = '/new/loan/second?coupon_id='+couponId;
};
</script>
</html>