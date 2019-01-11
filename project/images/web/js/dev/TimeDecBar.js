/**
 * 倒计时功能
 * @param int diffTime
 * @param function callDoing 处理中回调
 * @param function callDone  处理完成回调
 */
function TimeDecBar(diffTime,callDoing,callDone){
    var me=this;
    me.timer = null;

    // 两个处理函数
    me.callDoing = callDoing || function(){};
    me.callDone  = callDone  || function(){};

    // 初始化
    me.init = function(){
        me.initDiffTime = parseInt(diffTime,10);
        me.initTime = me.getClientTime();
        me.run();
    };

    /**
     * 获取剩余时间
     */
    me.getDiffTime = function(){
        var diff = me.getClientTime() - me.initTime;
        var diffTime = me.initDiffTime - diff;
        return diffTime > 0 ? diffTime : 0;
    };

    /**
     * 获取当前时间戳，精确到秒
     * @returns int
     */
    me.getClientTime = function(){
        return parseInt( new Date().getTime() / 1000, 10 );
    };

    // 倒计时运行
    me.run = function(){
        // 时间差: 时间戳表示，精确到秒
        var diffTime = me.getDiffTime();

        // 页面显示时间差
        var times = me.formatTime(diffTime);
        me.callDoing( times );

        // 后续处理
        if( diffTime > 0 ){
            me.timer = setTimeout( function(){ me.run(); }, 1000 );
        }else{
            if(me.timer){
                clearTimeout(me.timer);
            }
            me.callDone(times);
        }
    };
    /**
     * 格式化时间戳
     */
    me.formatTime = function(diffTime){
		var tempTime = diffTime;
        // 时间差: 计算时间差的时分秒
        var day=0,hour=0,minute=0,second=0;//时间默认值
        if(diffTime > 0){
            //剩余天数
            day = parseInt( diffTime/86400 );

            //取模 得到剩余的毫秒数, 剩余小时数
            diffTime %= 86400;
            hour = parseInt( diffTime/3600 );

            //取模 得到剩余的毫秒数, 剩余分钟数
            diffTime %= 3600;
            minute = parseInt( diffTime/60 );

            second = diffTime %= 60;
        }
        if (hour <= 9){ hour = '0' + hour;};
        if (minute <= 9){ minute = '0' + minute;};
        if (second <= 9){ second = '0' + second;};

        return {
            diffTime:tempTime,
            day:day,
            hour:hour,
            minute:minute,
            second:second
        };
    }

    // 执行初始化
    me.init();
}
