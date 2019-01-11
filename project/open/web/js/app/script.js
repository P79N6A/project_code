$(document).ready(function(){	
	$('.layer .item').each(function(){
        $(this).click(function(){
            //点击改变样式
            $('.layer .item').find('img').attr('src','/images/unchoosered.png');
            $(this).find('img').attr('src','/images/choosered.png');
            $('.layer .item').find($('p.black')).removeClass("white");
            $(this).find($('p.black')).addClass("white");
            $('.layer .item').find($('p.green ')).removeClass("basise");
            $(this).find($('p.green')).addClass("basise");
            $('.layer .item').find($('p.green ')).addClass("rgbf");
            $(this).find($('p.green')).removeClass("rgbf").addClass("bgrf");
            //点击相对应的radio变为checked
            $('input[type="radio"]').prop('checked',false);
            $('input[type="radio"]').removeAttr('checked');
            $(this).find('input[type="radio"]').prop('checked',true);
            $(this).find('input[type="radio"]').attr('checked','checked');
        });
    });
});