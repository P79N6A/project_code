<style>
    html,body{width:100%;height:100%; font-family: "Microsoft YaHei";  position: relative;}
    a{color:#fff}
    .wape {background: url(/images/bgbgds.jpg) no-repeat; width: 100%; height: 100%; background-size: 100%;}
    .wape .wap5{ padding: 40px 0 10px;}
    .wape img { width: 100%;padding: 0;display: block;margin-top: -1px;}
    .wape .button{margin: 0 15%; margin-top: 12px;background:rgba(0,0,0,0); position: absolute;bottom: 10%;}
    .wape .button  button{width: 100%;background:rgba(0,0,0,0);}

</style>
<div class="wape">
    <script  src='/new/st/statisticssave?type=<?php echo $type;?>'></script>
    <div class="button" id="app-down-btn">
        <button style="background:rgba(0,0,0,0);">
            <img src="/images/ljzhuce.png">
            <input type="hidden" id="system" value="<?php echo $system;?>">
            <input type="hidden" id="type" value="<?php echo $type;?>">
            <input type="hidden" id="down_type" value="<?php echo $down_type;?>">
            <input type="hidden" id="download_url" value="<?php echo $download_url;?>">
        </button>
    </div>
</div>