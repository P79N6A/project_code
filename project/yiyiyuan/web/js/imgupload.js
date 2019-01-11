document.domain = "yaoyuefu.com";
/**
 * 图片检测程序
 * @param {Object} file
 * @param {Object} fnCall
 */
function ImageValid(file, fnCall) {
	var me = this;
	me.file = file; // 文件表单对象
	me.fnCall = fnCall; // 回调处理
    me.filters = {
        "jpeg": "/9j/4",
        //"gif": "R0lGOD",
        "png": "iVBORw"
    };
    
    // 执行
    if (window.FileReader) {
		me.previewH5();
    } else { // 降级处理
		me.preview();
    }
}
/**
 * 图片扩展名验证程序
 * @param {Object} data
 */
ImageValid.prototype.validateImg = function (data) {
	var me = this;
    var pos = data.indexOf(",") + 1;
    for (var e in me.filters) {
        if (data.indexOf(me.filters[e]) === pos) {
            return e;
        }
    }
    return null;
}
/**
 * html5图片预览程序
 */
ImageValid.prototype.previewH5 = function(){
	var me = this;
	for (var i = 0, f; f = me.file.files[i]; i++) {
        var oFile = new FileReader();
        oFile.onload = function(e) {
            var src = e.target.result;
            if (!me.validateImg(src)) {
                me.fnCall(false,"只能上传图片");
            } else {
                me.fnCall(true,src);
            }
        };
        
        oFile.readAsDataURL(f);
    }
}
/**
 * 不支持h5特性的.
 * 这个应该废弃了
 */
ImageValid.prototype.preview = function(){
	var me = this;
    if (!/\.jpg$|\.png$|\.gif$/i.test(me.file.value)) {
        me.fnCall(false,"只能上传图片");
    } else {
        me.fnCall(true, me.file.value);
    }
}
//****end ****/




/**
 * 生成表单HTML
 * @param {Object} formid
 * @param {Object} encrypt
 * @param {Object} inputs 图片上传表单对应的ID号
 */
function ImageForm(formid,action,encrypt){
	var me = this;
	me.formid = formid;
	me.encrypt=encrypt;
	me.action = action; //@todo
	me.oForm=null; // 整个表单对象
	
	me.tplForm = '<form style="display:none;" id="!{formid}" name="!{formid}" action="!{action}" method="POST">\
								<input type="hidden" name="encrypt" value="!{encrypt}">\
							</form>';
	
	me.tplInput = '	<div id="!{group}_group"><input id="!{group}_url"  name="!{group}[url]" type="hidden"   value="!{url}">\
								<input id="!{group}_base64" name="!{group}[base64]" type="hidden">\
								<input id="!{group}_file" name="!{group}[file]" type="file" style="display:none;"></div>';
	// 创建表单
	me.createForm();
}
/**
 * 创建当前表单对象
 */
ImageForm.prototype.createForm = function(){
	var me = this;
	var formid = me.formid;
	// 初始化代码
	if( document.getElementById(formid) ){
		throw new Error(formid + "已经存在");
	}
	
	// 加入到页面中
	var formHtml = me.formHtml();
	me.oForm = $(formHtml);
	me.oForm.appendTo($("body"));
	return me.oForm;
}
/**
 * 创建一个隐藏域
 * @param {Object} group
 * @param {Object} url
 */
ImageForm.prototype.createInput = function(group, url){
	var me = this;
	var inputHtml = me.inputHtml(group, url);
	var oInput = $(inputHtml);
	me.oForm.append(oInput);
	return oInput;
}

/**
 * 创建整个form表单HTML
 */
ImageForm.prototype.formHtml = function(){
	var me = this;
	var formHtml = '';
	var dict = {
		formid	: me.formid,
		action	: me.action,
		encrypt: me.encrypt,
	};
	
	formHtml = me.render(me.tplForm, dict);
	return formHtml;
}
/*ImageForm.prototype.inputsHtml = function(inputs){
	var me = this;
	var arrHtml = [];
	var inputsHtml = '';
	var len  = inputs.length;
	for(var i=0; i<len; i++){
		arrHtml.push( me.inputHtml(inputs[i]) );
	}
	inputsHtml = arrHtml.join("\n");
	return inputsHtml;
}*/
/**
 * 创建单独表单HTML
 * @param {Object} group
 */
ImageForm.prototype.inputHtml = function(group,url){
	var me = this;
	var inputHtml =  me.render(me.tplInput,{
		"group": group,
		"url" : url,
	});
	return inputHtml;
}
/**
 * 替换字符串 !{}
 * @param obj
 * @returns {String}
 * @example
 * '我是!{str}'.render({str: '测试'});
 */
ImageForm.prototype.render = function (str, obj) {
    var str, reg;
    Object.keys(obj).forEach(function (v) {
        reg = new RegExp('\\!\\{' + v + '\\}', 'g');
        str = str.replace(reg, obj[v]);
    });

    return str;
};
//****end ****/



/**
 * 文件上传对外实现类
 * @param {Object} config{
 *			formid,encrypt,ids
 *			error,
 * 		}
 */
function ImageUpload(config){
	// 参数设定
	var me = this;
	
	// 出现错误时触发
	me.error = config.error ? config.error : me._error;

	if(!config.afterSave){
		throw new Error("afterSave必须指定");
	}
	me.afterSave  = config.afterSave;
	me.onupload = config.onupload;
	
	// 隐藏表单ID
	me.formid = config.formid;
	
	// 创建隐藏表单
	me.oImageForm = new ImageForm(config.formid, config.action, config.encrypt);
	
	me.ids=[];
}
/**
 * 加入一个file表单对象
 * @param {Object} group
 * @param {Object} path
 */
ImageUpload.prototype.add = function(id, path, fnCall, fnLoading){
	var me = this;
	var oGroup = me.oImageForm.createInput(id, path);
    
    // 绑定事件
	me.change(id, fnCall, fnLoading);
	
    // 加入到全局变量中
    me.ids.push(id);
}

/**
 * 
 * @param {Object} oFile
 */
ImageUpload.prototype.setBase64 = function(id, oFile, fnCall){
	var me = this;
	try{
		lrz(oFile,{width: 1024 }).then(function (rst) {
			var src = rst.base64;
			document.getElementById(id+'_base64').value = src;
			fnCall = fnCall || me.priview;
			fnCall(id, rst, oFile);
		});
		return true;
	}catch(e){
		me.error("-10000","本地不支持压缩图片");
		return false;
	}
}
// 触发文件打开事件
ImageUpload.prototype.trigger = function(id){
	$('#'+id+'_file').trigger('click');
}
/**
 * 上传的file表单绑定事件
 */
ImageUpload.prototype.change = function(id, fnCall, fnLoading){
	var me = this;
	// 绑定文件change事件
    var oModel = $("#"+id);
    var oInput   = $('#'+id+'_file');
    
    oInput.change(function(){
    	var oFile = this.files[0];
		if(fnLoading){
			fnLoading(id);
		}else{
			$('#'+id)[0].src = 'http://upload.xianhuahua.com/images/loading.png';
		}
			
    	// 验证并回调
        new ImageValid(this, function(status, src){
	        if(!status){
	            me.error(id, src);
	            return false;
	        }


	        // 设置值
	        me.setBase64(id, oFile, fnCall);
	        return true;
        });
    });
    
    // 触发 file 事件
    oModel.click(function(){
        oInput.trigger('click');
    });
}
/**
 * 上传图片前操作
 */
ImageUpload.prototype.beforeSave=function(){
	var me = this;
	var result = false;
	var id,v;
	for( var k in me.ids ){
		id = me.ids[k];
		if( document.getElementById(id + "_base64").value ||
			document.getElementById(id + "_file").value ){
			result = true;
		}
	}
	if( !result ){
		me.error("-20000","至少上传一张图片");
	}
	return result;
}
/**
 * 提交到服务器
 */
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
/** 默认方法: 对应的图片为id **/
/**
 *  默认:图片预览
 * @param str id 
 * @param object result 压缩后的图片
 * @param object original 原文件
 */
ImageUpload.prototype.priview = function(id, rst, original){
	document.getElementById(id).src = rst.base64;;
}
/**
 * 默认
 * @param {Object} id
 * @param {Object} src
 */
ImageUpload.prototype._error = function(id, src){
	alert(id+':'+src);
	return false;
}
// end 默认方法
//****end ****/



/**
 * iframe 提交操作
 * @param {Object} o
 * @param {Object} callback
 */
function iframeload( o, callback ){
	if( o.attachEvent ){
		o.attachEvent('onload',callback)
	}else{
		o.onload = callback;
	}
}
function iframeclear( frame ){
	try{
		frame.contentWindow.document.write('');         
		frame.contentWindow.close();
	document.body.removeChild(frame);   
	}catch(e){}
}
function iframepost( oForm, callback ){
	var iframeid = 'if' + Math.floor(Math.random( 999 )*100 + 100) + (new Date().getTime() + '').substr(5,8);
	var iframe = document.createElement('iframe');
	 iframe.style.display = 'none';
	 iframe.id = iframeid;
	 iframe.name = iframeid;
	//iframe.src = "about:blank";
	document.body.appendChild( iframe );
	var succeed = function(){
			var name = iframe.contentWindow.name;
			iframeclear( iframe );
			
			// 解析json数据
			var jsonData;
			try{
				if(typeof(JSON) != 'undefined'){
					jsonData = JSON.parse(name);
				}else{
					jsonData = eval( "(" + name + ")");
				}
			}catch(e){}
			if( typeof(jsonData) != 'undefined' &&  typeof(jsonData.res_code) != 'undefined'   ){
				callback(jsonData);
			}
	}
	iframeload( iframe, succeed );
	oForm.setAttribute("target",iframeid);	
	oForm.submit();
}
// end iframe



/**
 *
 * 　　　┏┓　　　┏┓
 * 　　┏┛┻━━━┛┻┓
 * 　　┃　　　　　　　┃
 * 　　┃　　　━　　　┃
 * 　　┃　┳┛　┗┳　┃
 * 　　┃　　　　　　　┃
 * 　　┃　　　┻　　　┃
 * 　　┃　　　　　　　┃
 * 　　┗━┓　　　┏━┛Code is far away from bug with the animal protecting
 * 　　　　┃　　　┃    神兽保佑,代码无bug
 * 　　　　┃　　　┃
 * 　　　　┃　　　┗━━━┓
 * 　　　　┃　　　　　 ┣┓
 * 　　　　┃　　　　 ┏┛
 * 　　　　┗┓┓┏━┳┓┏┛
 * 　　　　　┃┫┫　┃┫┫
 * 　　　　　┗┻┛　┗┻┛
 *
 */