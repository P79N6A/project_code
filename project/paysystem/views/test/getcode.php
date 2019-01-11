<img id="captcha" src="/site/captcha" ></img>
<script>
$(document).ready(function(){
    //刷新验证码
    var _captcha = $('#captcha');
    var _chg_captcha = $('#chg_captcha');
    _captcha.click(function(){
        $.getJSON("/site/captcha?refresh=true",function(res){
            var dataUrl = _captcha.attr("data-url");
            if(!dataUrl){
                dataUrl = _captcha.attr("src");
                _captcha.attr("data-url",dataUrl);
            }
            _captcha.attr("src",dataUrl+"?rd="+Math.random());
        });
    });

});
</script>