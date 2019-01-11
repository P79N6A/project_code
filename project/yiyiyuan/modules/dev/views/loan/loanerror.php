<style type="text/css">
    body{ background: #dfe7ec;}
    .heheples p{ text-align: center;}
    .heheples .helpone{  padding-top: 10%; font-size: 1.8rem; color: #e74747;     padding: 19% 15%;}
    .heheples .helpone span{ display:inline-block; width: 15%;position:relative; }
    .heheples .helpone span img{ width: 75%; position: absolute; top:-24px;right: 7%;}
    .heheples .helptwo{ font-size: 1.2rem;}
    .heheples .helptwo span{ color: #e74747;}
    .heheples .helpthree{ padding-top:3%;font-size: 1.25rem; color: #2eabf3;}


</style>
<script>
    var secs = 3; //倒计时的秒数 
    var URL;
    function Load(url) {
        URL = url;
        for (var i = secs; i >= 0; i--)
        {
            window.setTimeout('doUpdate(' + i + ')', (secs - i) * 1000);
        }
    }
    function doUpdate(num)
    {
        document.getElementById('nums').innerHTML = num;
        if (num == 0) {
            window.location = URL;
        }
    }
</script>
<div class="heheples">
    <p class="helpone"><span><img src="/images/error.png"></span>花二哥暂不能受理您的请求，谢谢</p>
    <p class="helptwo">系统将在<span id="nums"> 3 </span>秒后自动跳转，如果不想等待</p>
    <p class="helpthree"><a href="/dev/loan">点击这里跳转 >></a></p>
    <script type="text/javascript">Load('/dev/loan');</script> 
</div>