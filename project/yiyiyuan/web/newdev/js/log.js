function tongji(event,baseInfoss) {
    url_const= baseInfoss.url;
    baseInfoss.url = url_const+'&event='+event;
    console.log(baseInfoss);
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
    baseInfoss.url = url_const;
}

//Object.assign兼容问题
if (typeof Object.assign != 'function') {
    Object.defineProperty(Object, "assign", {
        value: function assign(target, varArgs) {
            'use strict';
            if (target == null) {
                throw new TypeError('Cannot convert undefined or null to object');
            }
            var to = Object(target);
            for (var index = 1; index < arguments.length; index++) {
                var nextSource = arguments[index];
                if (nextSource != null) {
                    for (var nextKey in nextSource) {
                        if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
                            to[nextKey] = nextSource[nextKey];
                        }
                    }
                }
            }
            return to;
        },
        writable: true,
        configurable: true
    });
}