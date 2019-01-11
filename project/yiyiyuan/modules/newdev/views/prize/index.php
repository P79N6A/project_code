<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title></title>
    <link rel="stylesheet" type="text/css" href="/newdev/css/yyy302/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/newdev/css/yyy302/style302.css"/>
    <script src="/newdev/js/yyy302/jquery-1.10.1.min.js"></script>
</head>
<body>  
    <div class="bdfalse" hidden>
        <img src="/newdev/images/yyy302/none.png"> 
        <p>暂无奖品</p>
    </div> 
    <div class="pinall" >
    </div>
    <div class="nodata" style=" text-align: center;top: 10px;bottom: 10px;line-height:34px;"></div> 
</body>
</html>
<script type="text/javascript">
    var curPage = 1; //当前页码  
var total,pageSize;  
var csrf  = '<?php echo $csrf;?>';
var div_data ='';
var totalPage = '<?php echo $count;?>';
var empty_prize_flag = false;
var img_url = '<?php echo $img_url;?>';
//获取数据  
function getData(page){
    var user_id = '<?php echo $user_id;?>';
    //$("#list").append("<li id='loading'>加载中</li>");
    $(".nodata").hide();
    $.ajax({
        type: 'POST',  
        url: '/new/prize/prizeajax',  
        data: {'_csrf':csrf,'page':page,'user_id':user_id},
        dataType:'json',    
        success:function(json){
            if(json.res_code == 1){
                 $(".pinall").empty();
                total = json.total; //总记录数
                pageSize = json.pageSize; //每页显示条数
                curPage = page; //当前页
                //totalPage = json.totalPage; //总页数
                var list = json.list;
                if(list=='' || list==null || total==0){
                    $(".bdfalse").show();
                    $(".nodata").hide();
                    empty_prize_flag = true;
                }else{
                    $.each(list,function(index,array){ //遍历json数据列
                        div_data += "<div class='pin_cont'><img src="+img_url+array['prize_pic']+"><div class='jpintxt'><h3>奖品："+array['title']+"</h3><p>抽奖时间："+array['create_time']+"</p></div></div>";
                    });
                    $(".pinall").append(div_data);
                    $(".nodata").hide();
                }
            }else{
                $(".bdfalse").show();
                $(".pinall").empty();
                $(".nodata").hide();
            }
        },
        complete:function(){ //生成分页条  
        },  
        error:function(){  
            $(".nodata").html("数据加载失败!"); 
        }  
    });  
}  
</script>

<script type="text/javascript">
 
    var i = 1; 
    getData(1);
    $(document).ready(function() { 
        $(window).scroll(function() {  
            if(totalPage-i>0){  
                //滚动条到达底部加载  
                if ($(document).scrollTop() >= $(document).height() - $(window).height()) {      
                    if(totalPage-i>0){
                        $(".nodata").show();
                        $(".nodata").html("上拉加载更多"); 
                        setTimeout(function() {   
                           getData(i)
                        }, 200);   
                            i++;  
                    }

                }  
                  
            }else{
                
                if(!empty_prize_flag){
                    setTimeout(function(){
                    $(".nodata").show();
                    $(".nodata").html("已经全部加载完毕");  
                    },300);  
                }
                    
            }
        });  
    });  
</script>
<script type="text/javascript">
        wx.config({
            debug: false,
            appId: "<?php echo $jsinfo['appid']; ?>",
            timestamp: "<?php echo $jsinfo['timestamp']; ?>",
            nonceStr: "<?php echo $jsinfo['nonceStr']; ?>",
            signature: "<?php echo $jsinfo['signature']; ?>",
            jsApiList: [
               'hideOptionMenu'
            ]
        });

        wx.ready(function () {
            wx.hideOptionMenu();
        });
</script>
