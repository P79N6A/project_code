$.fn.sliderNav = function(options) {
    var defaults = {
        items:
                ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"
                ], debug: false, height: null, arrows: true};
    var opts = $.extend(defaults, options);
    var o = $.meta ? $.extend({}, opts, $$.data()) : opts;
    var slider = $(this);
    if (pinyin) {
        $(slider).addClass('slider');//add sidebar's class
        $('.slider-content li:first', slider).addClass('selected').css('margin-top', '50px');//firstItem
        $(slider).append('<div class="slider-nav"><ul></ul></div>');//添加侧边栏
        for (var i in o.items)
            $('.slider-nav ul', slider).append("<li><a id='link" + o.items[i] + "' href='#" + o.items[i] + "'>" + o.items[i] + "</a></li>");
        var height = $('.slider-nav', slider).height();
        if (o.height) {
            height = o.height;
        }
        $('.slider-content', slider).css('height', document.body.clientHeight - 55);
        $('.slider-nav', slider).css('height', document.body.clientHeight - 100);
        if (o.debug) {
            $(slider).append('<div id="debug">Scroll Offset: <span>0</span></div>');
        }
        sendParams(slider, o);
        $('.slider-nav a', slider).mouseover(function(event) {
            var target = $(this).attr('href');
            var target = $(this).attr('id');
            document.getElementById(target).click();
            var cOffset = $('.slider-content', '#peopleList').offset().top;
            var tOffset;
            if ($('.slider-content ' + target, '#peopleList').offset() == undefined) {
            } else {
                tOffset = $('.slider-content ' + target, '#peopleList').offset().top;
                var height = $('.slider-nav', '#peopleList').height();
                if (o.height)
                    height = o.height;
                var pScroll = (tOffset - cOffset) - height / 8;
                $('.slider-content li', '#peopleList').removeClass('selected');
                $(target).addClass('selected');
                $('.slider-content', '#peopleList').stop().animate({scrollTop: '+=' + pScroll + 'px'}, 1);
                if (o.debug)
                    $('#debug span', '#peopleList').html(tOffset);
            }
        });
    }
};
function sendParams(e, o) {
    var slidernav = document.getElementsByClassName('slider-nav')[0];
    var uls = slidernav.getElementsByTagName('ul')[0];
    var liItems = uls.getElementsByTagName('li');
    var azArr = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"
    ];
    var liHeight = liItems[0].offsetHeight;//每一个字母所在区域的高度
    uls.addEventListener('touchstart', touchstart);
    function touchstart(e) {
        e.preventDefault();
        nStartY = e.targetTouches[0].pageY;
        nStartX = e.targetTouches[0].pageX;
        touchY = nStartY - 105;//手指触碰的Y值(起始)
        slider(touchY);
    }
    uls.addEventListener('touchmove', touchmove);
    function touchmove(e) {
        nMoveY = e.changedTouches[0].pageY;
        nMoveX = e.changedTouches[0].pageX;
        touchY = nMoveY - 105;//手指触碰的Y值(滑动)
        slider(touchY);
        //end
    }
    function slider(touchY) {
        azIndex = parseInt((touchY) / liItems[0].offsetHeight);
        //start
        var target = $($('.slider-nav a')[azIndex]).attr('href');
        var cOffset = $('.slider-content', '#peopleList').offset().top;
        var tOffset;
        if ($('.slider-content ' + target, '#peopleList').offset() == undefined) {
        } else {
            tOffset = $('.slider-content ' + target, '#peopleList').offset().top;
            var height = $('.slider-nav', '#peopleList').height();
            if (o.height)
                height = o.height;
            var pScroll = (tOffset - cOffset) - height / 8;
            $('.slider-content li', '#peopleList').removeClass('selected');
            $(target).addClass('selected');
            $('.slider-content', '#peopleList').stop().animate({scrollTop: '+=' + pScroll + 'px'}, 1);
            if (o.debug)
                $('#debug span', '#peopleList').html(tOffset);
        }
    }
}
