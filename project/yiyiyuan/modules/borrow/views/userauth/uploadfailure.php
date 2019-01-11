
<div class="wraper">
    <section class="process-bar">
        <div class="prcess" style="width: 60%"></div>
        <!-- 进度条控制css样式.process-bar .prcess 的width属性20%,40%,60%....100% -->
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </section>
    <!-- 视频认证7 -->
    <section class="sp-one">
        <img src="/borrow/310/images/rz-faile.png" alt="">
        <p class="sp-title">上传失败</p>
        <p class="sp-failed-txt">您上传的视频为18MB，超过16MB，请重新上传</p>
          <p class="sp-warn-txt">切记：拍摄时长为3-5s</p>
      </section>
    <button class="big345-button rz-filed" id="reset">重新拍摄</button>
</div>
<script src="/newdev/js/jquery-1.10.1.min.js"></script>
<script>
    $('#reset').click(function () {
        window.location='/borrow/userauth/pic';
    })
</script>