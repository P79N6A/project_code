// JavaScript Document
function previewImage(file, callFun) {
    /* file：file控件
     * prvid: 图片预览容器
     */
    var tip = "只能上传图片!"; // 设定提示信息
    var filters = {
        "jpeg": "/9j/4",
        //"gif": "R0lGOD",
        "png": "iVBORw"
    };
    if (window.FileReader) { // html5方案
        for (var i = 0, f; f = file.files[i]; i++) {
            var fr = new FileReader();
            fr.onload = function(e) {
                var src = e.target.result;
                if (!validateImg(src)) {
                    callFun(false,tip);
                } else {
                    callFun(true,src);
                }
            };
            fr.readAsDataURL(f);
        }
    } else { // 降级处理
        if (!/\.jpg$|\.png$|\.gif$/i.test(file.value)) {
            callFun(false,tip);
        } else {
            callFun(true,file.value);
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
}
/**
 * 绑定上传图片
 * @param  string id file表单的id,同时图片的为id_img
 * @return null
 */
function bindimgfunc(id){
    var oImage = $('#'+id+'_img');
    var oInput = $('#'+id);
    oImage.click(function(){
        oInput.click();
    });
    var callFun = function(status, src){
        if(status){
            oImage.attr("src", src);
        }else{
            alert(src);
        }
    }
    oInput.change(function(){
        previewImage(oInput[0],callFun);
    });
}
