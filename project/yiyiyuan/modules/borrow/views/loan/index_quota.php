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
    body{
        height: 25.2rem;
    }
/*    .w_change_cash_title{
            opacity: 0.8;
            font-family: "微软雅黑";
            font-size: 0.37rem;
            color: #999999;
            letter-spacing: 0;
            line-height: 0.37rem;
            position: absolute;
            right: 0.67rem;      
            bottom: 0.47rem;
    }
    .w_jiantou{
        width: 0.37rem;
        height: 0.37rem;
        position: absolute;
            right: 0.27rem;      
            bottom: 0.44rem;
    }
    .w_jihua_ss{
        width: 0.45rem;
        height: 0.45rem;
        float: left;
        margin-right: 0.2rem;
    }
    .w_title_txt{
        font-family: "微软雅黑";
        font-size: 0.37rem;
        color: #444444;
        letter-spacing: 0;
        line-height: 0.45rem;
    }
    .jiantou_ss{
        margin-top: 0.03rem;
    display: inline-block;
    float: right;
    margin-right: 0.1rem;
    }
    body {
    height: 25rem;
}
.bannerApply {
    position: absolute;
    left: 0;
    top: 12.4rem;
    height: 3.49rem;
}
.bannerMethods {
    position: absolute;
    left: 0;
    top: 16.5rem;
    height: 6.43rem;
}
.w_cash_title{
    width: 100%;
    height: 1.9rem;
    position: absolute;
    top: 9.98rem;
    display: flex;
    justify-content: space-around;
    background: #fff;
    align-items: center;
}
.sel-box .btn{
    width: 100%;
    margin: 0;
    border-radius: 0;
}*/
    .w_title_txt{
        display: block;
        float: left;
    }
    .rocket{
        margin-top: 0.095rem;
    }
</style>

<?php
if(isset($_GET['type'])){
    $urlType = 1;
}else{
    $urlType = 2;
} ?>
<div class="home_wrap">
    <img src="/borrow/310/images/itemIcon.png" alt="" class="itemIcon">
    <p class="item_title">信用借款服务</p>
    <div class="cash_home">
        <span class="cash_title" style="color:#444444">申请额度</span>
        <span class="cash_count" id="max_money" style="left:0;"><span style="font-size: 0.8rem;">￥</span><span id="moneyTitle"><?php echo $can_max_money; ?></span></span>
        <span class="w_change_cash_title" id="w_title_txt" style="color:#444444">更改额度</span>
        <img src="/borrow/310/images/jiantou_ss.png" class="w_jiantou">
    </div>
    <div class="cash_home_title">
        <div class="qixianone_q">
            <img src="/borrow/310/images/jihua_ss.png" class="w_jihua_ss">
            <span class="w_title_txt">借款期限</span>
        </div>
        <div class="qixianc_q">
            <span class="w_title_txt" id="qixian" >30天x3期</span>
           <img src="/borrow/310/images/jiantou_ss.png" class="jiantou_ss">
        </div>
    </div>
    <div class="getpay_Btn" id="get_quota">
        立即获取额度
    </div>
     
</div>
<div class="w_cash_title">
          <div>
            <img src="/borrow/310/images/rocket.png" class="rocket">
            <span class="title_txt">3分钟审批</span>
        </div>
        <div>
            <img src="/borrow/310/images/book.png" class="rocket">
            <span class="title_txt">超快到账</span>
        </div>
        <div>
            <img src="/borrow/310/images/home.png" class="rocket">
            <span class="title_txt">无需抵押</span>
        </div>
</div>
   <!--        申请条件-->
     <div class="bannerApply">
      <div class="bannerTitle">
        <span>申请条件</span>
      </div>
      <div class="bannerImg"></div>
    </div>
    <div class="bannerMethods">
      <div class="bannerTitle">
        <span>逾期处理办法</span>
      </div>
      <div class="bannerImg"></div>
    </div>
<div class="alert" hidden >
    <div class="box">
        <h4>信息同步</h4>
        <p>检测到您是智融钥匙用户，为了保证您的信息安全，5秒后将为您同步信用资料，同步后可立即发起借款。</p>
        <div class="btn" id="sync">一键同步</div><span id="second">5s</span>
    </div>
</div>
   <div class="help_service" style="bottom:1.5rem;">
    <img src="/borrow/310/images/tip.png" alt="" class="contact_service_tip">
    <a href="javascript:void(0);" onclick="doHelp('/borrow/helpcenter?user_id=<?php echo $user_id;?>')"><span class="contact_service_text">获取帮助</span></a>
</div>
<?= $this->render('/layouts/footer', ['page' => 'loan','log_user_id'=>$user_id]) ?>
<script src="/290/js/jquery-1.10.1.min.js"></script>
<!--<script src="/borrow/310/js/renzheng.js"></script>-->
<script src="/borrow/310/js/picker.js"></script>
<script>
    <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
    var urlType = "<?php echo $urlType; ?>";
    var typeInfo = "商城首页借款按钮";
    if(urlType == 1){
        typeInfo = "商城首页借款购买按钮";
    }
    zhuge.track('借款首页', {
        '来源': typeInfo,
        '状态': '未获取额度',
    });
    var csrf = '<?php echo $csrf; ?>';
    var canLoan = "<?php echo $canLoan ?>";
    var isShow = "<?php echo $isShow ?>";    
    $(function () {
 
        //console.log(isShow);
        //同步数据
        if(isShow){
            var num = 5;
            var timer = setInterval(function(){
               num--;
               if(num <= 0){
                   $("#second").html('');
                   $('#sync').css("background","red");
                   doSync();
               }else{
                    $("#second").html(num+'s');
               }
              
            },1000);
            $('.alert').show();
        }
        
        $("#get_quota").bind('click', function () {
            zhuge.track('首页点击', {
                '按钮名称': '立即获取额度',
            });
            if (canLoan != '1') {
                alert("今日借款已满，明天再来吧");
                return false;
            }
            if($('#get_quota').hasClass('dis')){
                return false;
            }
            $('#get_quota').addClass('dis');
            
            tongji('index_quota_get',baseInfoss);
            setTimeout(function(){
                getcanloan();
            },2000);
        });
    });
    
    function doSync(){
        $('#sync').click(function(){
            $('.alert').hide();
        });
    }

    function getcanloan(){
        var black_box = _fmOpt.getinfo();//获取同盾指纹
        $.ajax({
            url: "/borrow/loan/getcanloan",
            type: 'post',
            async: false,
            data: {_csrf: csrf,type:1,black_box:black_box},
            success: function (json) {
                json = eval('(' + json + ')');
                console.log(json);
                if (json.rsp_code == '0000') {
                    if (json.is_change == 1) { //有待完善资料 跳转到认证页
                        window.location.href = '/borrow/userinfo/requireinfo';
                    } else if (json.is_change == 2) { //跳转到额度审核中页面
                        window.location.href = '/borrow/loan';
                    }
                } else {
                    alert(json.rsp_msg);
                }
            },
            error: function (json) {
                alert('请十分钟后发起评测');
            }
        });
    }
    
    function doHelp(url) {
        tongji('do_help',baseInfoss);
        setTimeout(function(){
            window.location.href = url;
        },100);
    }
    

    $.scrEvent({
        data: ['500元','1000元','1500元','2000元','2500元','3000元','3500元','4000元','4500元','5000元'],   // 数据
        //data: desc_lists,   // 数据
        evEle: '#w_title_txt',            // 选择器
        title: '选择申请额度',            // 标题
        defValue: '1000元',             // 默认值
        afterAction: function(data) { 
            console.log(data)//  点击确定按钮后,执行的动作
            if(data == '500元'){
                data = 500;
            }else if(data == '1000元'){
                data = '1,000';
            }else if(data == '1500元'){
                data = '1,500';
            }else if(data == '2000元'){
                data = '2,000';
            }else if(data == '2500元'){
                data = '2,500';
            }else if(data == '3000元'){
                data = '3,000';
            }else if(data == '3500元'){
                data = '3,500';
            }else if(data == '4000元'){
                data = '4,000';
            }else if(data == '4500元'){
                data = '4,500';
            }else if(data == '5000元'){
                data = '5,000';
            }
             $('#moneyTitle').html(data);
             $('#w_title_txt').html('更改额度');
        }
    });
    
      $.scrEvent({
        data: ['30天x3期','30天x6期','30天x9期','56天x1期'],   // 数据
        //data: desc_lists,   // 数据
        evEle: '#qixian',            // 选择器
        title: '选择期限',            // 标题
        defValue: '30天x3期',             // 默认值
        afterAction: function(data) { 
            console.log(data)//  点击确定按钮后,执行的动作
             $('#qixian').html(data);
          
        }
    });
</script>

