$(document).ready(function(){	
	$('.layer .item').each(function(){
        $(this).click(function(){
            //点击改变样式
            $('.layer .item').find('img').attr('src','images/unchoose.png');
            $(this).find('img').attr('src','images/choose.png');
            //点击相对应的radio变为checked
            $('input[type="radio"]').prop('checked',false);
            $('input[type="radio"]').removeAttr('checked');
            $(this).find('input[type="radio"]').prop('checked',true);
            $(this).find('input[type="radio"]').attr('checked','checked');
        });
    });
});