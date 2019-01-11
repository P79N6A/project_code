<?php 
use yii\widgets\ActiveForm;
use app\models\dev\Province;
use yii\helpers\ArrayHelper;
?>
<div class="container">
     <div class="content">
              <?php $form = ActiveForm::begin([
    			'id' => "bind-form",
    			'enableAjaxValidation' => false,
    			'options' => ['enctype' => 'multipart/form-data'],]); 
   			 ?>
            <p class="pleft mb20 n26">用户姓名：<label class="red"><?php echo $userinfo->realname;?></label></p>
			<p class="mb20">
			<?= $form->field($model, 'card',['labelOptions'=>['style'=>'display:none']])->textInput(['maxlength' => 19,'placeholder'=>'卡号','id'=>'no']) ?>
			</p>
                <p class="p_ipt mb20">
                    <span id="bank"><?php echo $userbank['bank_name'];?></span>
                </p>
                <p class=" mb20">
                    <?php
					    $city=Province::find()->where(['pid' => 0])->all();
					    $listData=ArrayHelper::map($city,'id','name');
					    echo $form->field($model, 'province',['labelOptions'=>['style'=>'display:none']])->dropDownList(
							   $listData,['prompt' => '省']);
  					  ?>
  				</p>
				<?php
					   $city=Province::find()->where(['pid' => $userbank['province']])->all();
					   $listData=ArrayHelper::map($city,'id','name');
                 	   echo $form->field($model, 'city',['labelOptions'=>['style'=>'display:none']])->dropDownList($listData, ['prompt' => '市']) ?>
                <p class=" p_iptmb20">
                
                	<?= $form->field($model, 'sub_bank',['labelOptions'=>['style'=>'display:none']])->textInput(['maxlength' => 200,'placeholder'=>'支行','id'=>'sub_bank']) ?>
                </p>
                  <input type="hidden" id="type" name="User_bank[type]"   value="<?php echo $userbank['type'];?>"/>
                  <input type="hidden" id="bank_abbr" name="User_bank[bank_abbr]"   value="<?php echo $userbank['bank_abbr'];?>"/>
                  <input type="hidden" id="bank_name" name="User_bank[bank_name]"   value="<?php echo $userbank['bank_name'];?>"/>
                  <input type="hidden" id="is_true" name="is_true"   value="1"/>
                  <input type="hidden" id="user_bank_id" name="user_bank_id"   value="<?php echo $userbank['id'];?>"/>
                  <button type="button" id="bindbutton"  class="btn" style="width:100%;" >确定</button>

         <?php ActiveForm::end(); ?>
         
       </div>
</div>
<script src="/js/dev/bind.js?v=20150401"></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  wx.config({
	debug: false,
	appId: '<?php echo $jsinfo['appid'];?>',
	timestamp: <?php echo $jsinfo['timestamp'];?>,
	nonceStr: '<?php echo $jsinfo['nonceStr'];?>',
	signature: '<?php echo $jsinfo['signature'];?>',
	jsApiList: [
		'hideOptionMenu'
	  ]
  });
  
  wx.ready(function(){
	  wx.hideOptionMenu();
	});
</script> 