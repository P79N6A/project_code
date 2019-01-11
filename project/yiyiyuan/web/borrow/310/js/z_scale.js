//缩放比例
if(/Android (\d+\.\d+)/.test(navigator.userAgent)){
    var version = parseFloat(RegExp.$1);
    if(version>2.3){
        var phoneScale = parseInt(window.screen.width)/375;
        document.write('<meta name="viewport" content="width=375, minimum-scale = '+ phoneScale +', maximum-scale = '+ phoneScale +', target-densitydpi=device-dpi">');
    }else{
        document.write('<meta name="viewport" content="width=375, target-densitydpi=device-dpi">');
    }
}else{
    document.write('<meta name="viewport" content="width=375, user-scalable=no, target-densitydpi=device-dpi">');
}