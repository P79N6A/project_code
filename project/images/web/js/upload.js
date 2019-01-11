// JavaScript Document
function previewImage(file, img, num) {
    /* file：file控件
     * prvid: 图片预览容器
     */
    var tip = "只能上传图片!"; // 设定提示信息
    var filters = {
        "jpeg": "/9j/4",
        "gif": "R0lGOD",
        "png": "iVBORw"
    };
    if (window.FileReader) { // html5方案
        for (var i = 0, f; f = file.files[i]; i++) {
            var fr = new FileReader();
            fr.onload = function(e) {
                var src = e.target.result;
                if (!validateImg(src)) {
                    alert(tip);
                } else {
                    showPrvImg(src);
                }
            };
            fr.readAsDataURL(f);
        }
    } else { // 降级处理
        if (!/\.jpg$|\.png$|\.gif$/i.test(file.value)) {
            alert(tip);
        } else {
            showPrvImg(file.value);
        }
    }

    function validateImg(data) {
        var pos = data.indexOf(",") + 1;
        for (var e in filters) {
            if (data.indexOf(filters[e]) === pos) {
                return e;
            }
        }
        return null;
    }

    function showPrvImg(src) {
        $("#" + img).attr("src", src);
        $("#" + img + "Div").hide();
        $("#" + img).show();
        $('.btn1').attr('src', '/images/july/btn7.png');
        var btn7 = $('.btn1').attr('src');
        if (btn7 == '/images/july/btn7.png') {
            $('.btnF').css('display', 'none');
        }
        if (num == 0) {
            $("#" + img).parent().parent().removeClass("col-xs-offset-4");
            $("#photo_1").show();
        } else if (num == 1) {
            $("#photo_2").show();
        }
    }
}