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