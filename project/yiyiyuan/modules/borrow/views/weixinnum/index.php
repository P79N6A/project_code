
<div class="wraper wzg-bg">
    <p class="wzg-txt wzg-tp">关注“先花一亿元”公众号</p>
    <p class="wzg-txt">更多好礼等你拿！</p>
    <button class="wzg-copy" id="copys">复制</button>
    <img class="wzg-ss-img" src="/borrow/310/images/wanzhan.png" alt="">
    <button class="big345-button wzg-gz-btn" style="display: none">马上去关注</button>
</div>
<div class="toast_tishi" id="xtfmang" style="top: 63%;" hidden>复制成功</div>
<script src="/js/clipboard.min.js?v=10001" type="text/javascript"></script>
<script>

    var clipboard = new Clipboard('#copys', {
     text: function () {  
         tongji('weixin_num_copy');
         $('#xtfmang').show();
         $("#xtfmang").text('复制成功！');
         setTimeout(function () {
             $("#xtfmang").hide();
             $('#xtfmang').text('');
         }, 1000);
         return "xianhuayyy";
     }

 });
 clipboard.on('success', function (e) {
 });
</script>
<script>
    function tongji(event) {
        <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
        <?php $json_data = \app\common\PLogger::getJson();?>
        var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
        baseInfoss.url = baseInfoss.url+'&event='+event;
        // console.log(baseInfoss);
        var ortherInfo = {
            screen_height: window.screen.height,//分辨率高
            screen_width: window.screen.width,  //分辨率宽
            user_agent: navigator.userAgent,
            height: document.documentElement.clientHeight || document.body.clientHeight,  //网页可见区域宽
            width: document.documentElement.clientWidth || document.body.clientWidth,//网页可见区域高
        };
        var baseInfos = Object.assign(baseInfoss, ortherInfo);

        var turnForm = document.createElement("form");
        turnForm.id = "uploadImgForm";
        turnForm.name = "uploadImgForm";
        document.body.appendChild(turnForm);
        turnForm.method = 'post';
        turnForm.action = baseInfoss.log_url+'weixin';
        //创建隐藏表单
        for (var i in baseInfos) {
            var newElement = document.createElement("input");
            newElement.setAttribute("name",i);
            newElement.setAttribute("type","hidden");
            newElement.setAttribute("value",baseInfos[i]);
            turnForm.appendChild(newElement);
        }
        var iframeid = 'if' + Math.floor(Math.random( 999 )*100 + 100) + (new Date().getTime() + '').substr(5,8);
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.id = iframeid;
        iframe.name = iframeid;
        iframe.src = "about:blank";
        document.body.appendChild( iframe );
        turnForm.setAttribute("target",iframeid);
        turnForm.submit();
    }
</script>