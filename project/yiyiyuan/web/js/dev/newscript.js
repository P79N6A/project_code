$(document).ready(function(){


    $('.usere_cont .user_gzgz .gzgz_right').each(function(){
        $(this).click(function(){
            $(this).toggleClass("two");
            $('.usere_cont .user_txtxdt').toggle();

        });
    });

     $('.usere_cont .user_gzgz .gzgz_rights').each(function(){
        $(this).click(function(){
            $(this).toggleClass("two");
            $('.usere_cont .user_txtxdts').toggle();
        });
    });
    $('.messtg').click(function(){
        $('.moveline').animate({"left":"57.5%"});
        $('.messtg_con').show();
        $('.messtg_xiax').hide();
        $('.message a.messtg').css({"color":"#e74747"});
        $('.message a.messxx').css({"color":"#444"});

    })
    $('.messxx').click(function(){
        $('.moveline').animate({"left":"9%"});
        $('.messtg_con').hide();
        $('.messtg_xiax').show();
        $('.message a.messxx').css({"color":"#e74747"});
        $('.message a.messtg').css({"color":"#444"});

    })

 });

$.fn.RangeSlider = function(cfg){
    this.sliderCfg = {
        min: cfg && !isNaN(parseFloat(cfg.min)) ? Number(cfg.min) : null,
        max: cfg && !isNaN(parseFloat(cfg.max)) ? Number(cfg.max) : null,
        step: cfg && Number(cfg.step) ? cfg.step : 1,
        callback: cfg && cfg.callback ? cfg.callback : null
    };

    var $input = $(this);
    var min = this.sliderCfg.min;
    var max = this.sliderCfg.max;
    var step = this.sliderCfg.step;
    var callback = this.sliderCfg.callback;

    $input.attr('min', min)
        .attr('max', max)
        .attr('step', step);

    $input.bind("input", function(e){
        $input.attr('value', this.value);
        $input.css( 'background', 'linear-gradient(to right, #059CFA, white ' + this.value + '%, white)' );

        if ($.isFunction(callback)) {
            callback(this);
        }
    });
};
