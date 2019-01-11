$(function(){   
    $('.bank_sure').click(function(){
        if($('.banksC').css('display')=='none'){
            show();
        }else{
            hide();
        }
        $('.banksC li').each(function(){
            $(this).click(function(){                
                $('.banksC li').removeClass('on');
                $(this).addClass('on');
                var chooseHtml = $(this).html();
                $('.bank_sure').html(chooseHtml);
                hide();
                var bankId = $(this).attr('id');
                $('.bank_choose').find('input[type="hidden"]').attr('value',bankId);
                var bank_type = $(this).attr('bid');
                $("#bank_type").val(bank_type);
            }); 
        });   
    });
});
function show(){
    $('.banksC').css({'display':'block'});
    $('.bank_choose').css({'borderBottomRightRadius':'0','borderBottomLeftRadius':'0'});
    $('.highlight i').addClass('on');
}
function hide(){
    setTimeout(function(){
        $('.banksC').css({'display':'none'});
        $('.bank_choose').css({'borderBottomRightRadius':'5px','borderBottomLeftRadius':'5px'});
        $('.highlight i').removeClass('on'); 
    },30)
    
}