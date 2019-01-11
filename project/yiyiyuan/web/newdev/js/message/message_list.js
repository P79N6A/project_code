	var flag = true;
	var post_data = {};
	post_data.type = 1;
	post_data.limit = 10;
	post_data.page = 1;
	post_data._csrf = $('#csrf').val();

	$('.xtong_left').click(function(){
		var me = $(this);
		if(me.hasClass('active')){
			return false;
		}
		me.addClass('active').find('.weidu_meage').show();
		me.find('.num').removeClass('text').addClass('weidu_txt');
		me.siblings().removeClass('active').find('.weidu_meage').hide();
		me.siblings().find('.num').removeClass('weidu_txt').addClass('text');
		$("#message_list").empty();
		post_data.type = me.data('type');
		post_data.page = 1;
		get_message_list();
	});

	function create_message(data_item){
		var message_dom = $("<div class=\"important\"></div>");
		message_dom.attr({'id':data_item.id,'type':post_data.type});
		var em_dom = $("<em></em>");
		var h3_dom = $("<h3></h3>");
		var title_dom = $("<span>"+data_item.title+"</span>");
		if(data_item.read_status == 0){
			h3_dom.addClass("none_wdu");
			em_dom.addClass("weidu");
		}
		title_dom.append(em_dom);
		h3_dom.append(title_dom);
		var date_dom = $("<p></p>");
		if(post_data.type == 1){
			date_dom.text(data_item.send_time);
		}else{
			date_dom.text(data_item.last_modify_time);
		}
		var contact_dom = $("<div class=\"one_line\">" + data_item.contact + "</div>");
		message_dom.append(h3_dom).append(date_dom).append(contact_dom);
		message_dom.click(jump_hander);
		return message_dom;
	}

	function jump_hander(){
		var message_id = $(this).attr('id');
		var type = $(this).attr('type');
		window.location.href = "/new/message/info?type=" + type + "&id=" + message_id;
	}

    function get_message_list(){
    	$.post('/new/message/messagelist',post_data,function(data){
            var res = $.parseJSON(data);
            if(res.rsp_code == 0000){
            	var message_list = res.rsp_data.message_list;
            	var length = message_list.length;
	            if(length){
	                for(i = 0,j=message_list.length;i<j;i++){
	                    var message_dom = create_message(message_list[i]);
	                    $("#message_list").append(message_dom);
	                }
	                if(length < post_data.limit){
	                    flag = false;
	                }else{
	                	post_data.page++;
	                }
	            }else{
	                flag = false;
	            }
            }else{
            	flag = false;
            }
        }); 
    }

    get_message_list();

    $(window).scroll(
    	function(){
	        var scrollTop = $(this).scrollTop();
	        var scrollHeight = $(document).height();
	        var windowHeight = $(this).height();
	        if (scrollTop + windowHeight == scrollHeight) {
	            if(flag){
	               get_message_list();
	            }
	    	}
    });