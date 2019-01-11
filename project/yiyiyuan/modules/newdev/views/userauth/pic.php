<?php    
use app\commonapi\ImageHandler;
use yii\helpers\Url;

$uploadurl = ImageHandler::$img_upload;
?>
<div class="container">
          <div class="content text-center">
          	<p class="b30 text-center mb20">持证自拍照</p>
                <!--新持证自拍 START-->
                <form target="_self" id="supplyForm" action="/new/userauth/picsave" method="post">
                    <input  id="_csrf" name="_csrf" type="hidden" value="<?php echo $csrf; ?>">
                    <input name ="orderinfo" type = "hidden" value="<?php echo $orderinfo ?>">
                    <?php //echo $orderinfo; ?>
                    <input type="hidden" name="user_id" value="<?php echo $userinfo->user_id; ?>"/>
                    <?php //echo $userinfo->user_id; ?>
                    <span  class="cardUpload">
                        <?php
                        $path = isset($imgList[0]) ? $imgList[0]['img'] : '';
                        $url = $path ? ImageHandler::getUrl($path) : $imgDefault;
                        ?>
                        <input  type="hidden" id="supply1Url" name="supplyUrl[1]" value="<?= $path ?>"/>
                        <img id="supply1" src="<?= $url ?>" width="100px">
                    </span>
            <p class="cor_b2 text-left n20 mt40"><img src="/images/dev/tip.png" width="3.5%"/> 请严格按照要求进行拍照，否则将不能完成此次借款。</p>
            <p class="red text-center b38 border-red"><?php echo $pictype['title'];?></p>
            <input type="hidden" name="serverid" value="" id="reg_serverid">
            <input type="hidden" name="user_id" value="<?php echo $userinfo['user_id'];?>" />
            <input type="hidden" name="pic_type" id="reg_pic_type" value="<?php echo $pictype['id'];?>"> 
          	<input type="button" id="btok" disabled="disabled" value="提交认证"  class="btn dis" style="width:100%;" >
            <hr style="border-color:#e74747;"/>
            <p class="text-left b30">示例照片:</p>
            <p class="text-center mt20">
            	<img src="<?php echo $pictype['pic'];?>" width="54%"/>
            </p>
            </form>
          </div>
       </div>
<script src="/js/imgupload.js?m=v7" type="text/javascript"></script>
<script src="http://upload.yaoyuefu.com/js/resizeimg/dist/lrz.bundle.js" type="text/javascript"></script>
<script type="text/javascript">

    var showErr = function (id, msg) {
        $("#submit-err").html(msg);
    }
    ImageUpload.prototype.beforeSave=function(){
	var me = this;
	var result = false;
	var id,v;
	
	for( var k in me.ids ){
		id = me.ids[k];
		//alert(id);return false;
		if( document.getElementById(id + "_base64").value ||
			document.getElementById(id + "_file").value ){
			result = true;
		}
	}
	result = true;
	if( !result ){
		me.error("-20000","至少上传一张图片");
	}
	return result;
    }
    ImageUpload.prototype.save = function(){
	var me = this;
	//1 提交前
	var result = me.beforeSave();
	if(!result){ 
		return false; 
	}

	//2 使用 iframe 提交
	var oForm = me.oImageForm.oForm[0];
	if (!window.FileReader) {// 非html5 时
		oForm.enctype="multipart/form-data";//enctype : 
	}
	
	if( me.onupload ){
		 me.onupload();
	}
	iframepost(oForm, me.afterSave);
}
    
    var fnAfter = function (data) {
        // 验证回调结果
        var ok = data && parseInt(data.res_code, 10) === 0;
        if (!ok) {
            showErr(data.res_code, data.res_data);
            return null;
        }

        // 写入到本地表单中
        var urls = data.res_data;
        for (var id in urls) {
            $("#" + id + 'Url').val(urls[id]);
        }

        $("#supplyForm").submit();
    }
    $(function () {
        var oUpload = new ImageUpload({
            "formid": "uploadImgForm",
            'action': "<?= $uploadurl ?>/upload",
            "encrypt": "<?= $encrypt ?>",
            "error": showErr,
            'afterSave': fnAfter,
            'onupload': function () {
                $("#btok").html("正在上传中");
            }
        });
        var i, img, id, url;
            img = 'supply1';
            id = document.getElementById(img + 'Url').value;

            // 只可添加,不可修改
            url = document.getElementById(img + 'Url').value;
            if (!url) {
                oUpload.add(img, url, function (id, rst) {
                    document.getElementById(id).src = rst.base64;

                    //按钮状态
                    $("#btok").removeClass('dis');
                    $("#btok").removeAttr('disabled');
                });
            }

        // 图片上传绑定
        $("#btok").click(function () {
            oUpload.save();
        });

    });

</script>
<!-- end 图片上传 -->
