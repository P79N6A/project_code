var e = {
    getParams: function(){
        console.info(window.location.href);
        var url = window.location.href.split("?")[1];
        var theRequest = new Object();
        var strs = url.split("&");
        for(var i = 0; i < strs.length; i ++) {
            theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);
        }
        return theRequest;
    },
    handleUrl: function(){
        var request = e.getParams();
        if( request.url ){
            window.location.replace( request.url );
        }
    },
    isWeiXin: function(){
        var ua = window.navigator.userAgent.toLowerCase(); 
        if( ua.match(/MicroMessenger/i) == 'micromessenger'){ 
            return true; 
        }else{ 
            return false; 
        } 
    },
    addEvent: function(){
        $('#ios-btn').on('click',function(){
            if( e.isWeiXin() ){
                $('#overDiv').show();
                $('#diolo_warp').show();
            }else{
            	$.get("/dev/st/statisticssave", { type: 37 },function(data){
            		window.location = "https://itunes.apple.com/cn/app/xian-hua-yi-yi-yuan/id986683563?mt=8";
            	});
            }
        });
        $('#android-btn').on('click',function(){
        	$.get("/dev/st/statisticssave", { type: 38 },function(data){
        		window.location = "http://a.app.qq.com/o/simple.jsp?pkgname=com.xianhuahua.yiyiyuan_1";
        	});
        });
        $('#ios-down-btn').on('click',function(){
        	$.get("/dev/st/statisticssave", { type: 39 },function(data){
        		window.location = "http://a.app.qq.com/o/simple.jsp?pkgname=com.xianhuahua.yiyiyuan_1";
        	});
        });
        $('#android-down-btn').on('click',function(){
        	$.get("/dev/st/statisticssave", { type: 40 },function(data){
        		window.location = "http://a.app.qq.com/o/simple.jsp?pkgname=com.xianhuahua.yiyiyuan_1";
        	});
        });
        $('#app-down-btn').on('click',function(){
            var system = $('#system').val();
            var down_type = $('#down_type').val();
            var download_url = $('#download_url').val();
            $.get("/new/st/statisticssave", { type: down_type },function(data){
                if(e.isWeiXin()){
                    window.location = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.xianhuahua.yiyiyuan_1';
                } else{
                    if(system == 'Android'){
                        window.location = download_url;
                    } else if(system == 'iPhone' || system == 'iPad'){
                        window.location = 'https://itunes.apple.com/cn/app/xian-hua-yi-yi-yuan/id986683563?mt=8';
                    }
                }
            });
        });
        $('#overDiv').on('click',function(){
            $(this).hide();
        });
        $('#diolo_warp').on('click',function(){
            $(this).hide();
        });
    },
    init: function(){
        window.onload = function(){
            e.addEvent();
            $('#overDiv').hide();
            $('#diolo_warp').hide();
        }
    }
};
e.init();