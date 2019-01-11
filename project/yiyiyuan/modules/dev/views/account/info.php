<div class="container pb100">
<script  src='/dev/st/statisticssave?type=14'></script> 
           <div class="bgff">
           	 <div class="content1 border_bottom">
             	<div class="border_bottom mb20" style="padding-right:6.25%">
                	<p class="mb20">
                    	<img class="face" src="<?php if( $userinfo['userwx']['head']){ echo $userinfo['userwx']['head'];}else{ echo '/images/dev/face.png';}?>" style="vertical-align:middle;"/>
                		<span class="pl b30"><?php echo $userinfo['userwx']['nickname'];?></span>
                        <span class="cor_cd pl"><?php if($userinfo['user_type']=='1'){ echo "大学生";}else{ echo "上班族";} ?></span>
                        <a href="javascript:void(0);" id="get_user_headurl" class="pull-right cor" style="margin-top:4%"><span class="icons refresh"></span>刷新</a>
                    </p>
                </div>
                <div class="border_bottom mb20" style="padding-right:6.25%">
                	<div class="row mb20">
                    	<div class="col-xs-5">
                			<span class="pl">手机号</span>
                        </div>
                        <div class="col-xs-7 text-right cor_cd cor_cd">
                        	<?php echo $userinfo['mobile'];?>
                        </div>
                    </div>
                </div>
                <?php if( $userinfo->user_type == '1') {?>
                	<?php if( $userinfo->school != '' ){?>
                	<div class="border_bottom mb20" style="padding-right:6.25%">
	                	<div class="row mb20">
	                    	<div class="col-xs-5">
	                			<span class="pl">姓名</span>
	                        </div>
	                        <div class="col-xs-7 text-right cor_cd">
	                        	<?php echo $userinfo['realname'];?>
	                        </div>
	                    </div>
	                </div>
	                <div class="border_bottom mb20" style="padding-right:6.25%">
	                	<div class="row mb20">
	                    	<div class="col-xs-5">
	                			<span class="pl">身份证号</span>
	                        </div>
	                        <div class="col-xs-7 text-right cor_cd">
	                        	<?php echo !empty($userinfo['identity']) ? substr($userinfo['identity'],0,6)."********".substr($userinfo['identity'],-4,4) : "" ;?>
	                        </div>
	                    </div>
	                </div>
	                <div class="border_bottom mb20" style="padding-right:6.25%">
						<div class="row mb20">
							<div class="col-xs-5">
								<span class="pl">学校</span>
							</div>
							 <div class="col-xs-7 text-right cor_cd">
								<?php echo !empty($userinfo['school']) ? $userinfo['school'] : "" ;?>
							</div>
						</div>
	                </div>
					<div class="border_bottom mb20" style="padding-right:6.25%">
						<div class="row mb20">
							<div class="col-xs-5">
								<span class="pl">学历</span>
							</div>
							 <div class="col-xs-7 text-right cor_cd">
								<?php if($userinfo['edu']=='1'){?>
								博士
								<?php }else if($userinfo['edu']=='2'){?>
								硕士
								<?php }else if($userinfo['edu']=='3'){?>
								本科
								<?php }else if($userinfo['edu']=='4'){?>
								专科
								<?php }?>
							</div>
						</div>
	                </div>
					<div class="border_bottom mb20" style="padding-right:6.25%">
						<div class="row mb20">
							<div class="col-xs-5">
								<span class="pl">入学年份</span>
							</div>
							 <div class="col-xs-7 text-right cor_cd">
								<?php echo !empty($userinfo['school_time']) ? substr($userinfo['school_time'],0,4) : "" ;?>
							</div>
						</div>
	                </div>
                	<?php }else{?>
                	<div class="border_bottom mb20" style="padding-right:6.25%">
	                	<a href="/dev/reg/two?url=<?php echo urlencode('/dev/account/info');?>" class="cor_4">
	                    	<div class="row mb20">
	                            <div class="col-xs-4">
	                                <span class="pl">身份证号</span>
	                            </div>
								<div class="col-xs-4">
	                                <span class="non">未认证</span>
	                            </div>
	                             <div class="col-xs-4 text-right">
	                                <span class="red">去认证 <img src="/images/dev/gtred.png" width="8.5%"/></span>
	                            </div>
	                        </div>
	                    </a>
	                </div>
                	<?php }?>
                <?php }else{ ?>
                <!-- 身份行业信息 -->
                	<?php if( $userinfo->realname != '' ){?>
                	<div class="border_bottom mb20" style="padding-right:6.25%">
	                	<div class="row mb20">
	                    	<div class="col-xs-5">
	                			<span class="pl">姓名</span>
	                        </div>
	                        <div class="col-xs-7 text-right cor_cd">
	                        	<?php echo $userinfo['realname'];?>
	                        </div>
	                    </div>
	                </div>
	                <div class="border_bottom mb20" style="padding-right:6.25%">
	                	<div class="row mb20">
	                    	<div class="col-xs-5">
	                			<span class="pl">身份证号</span>
	                        </div>
	                        <div class="col-xs-7 text-right cor_cd">
	                        	<?php echo !empty($userinfo['identity']) ? substr($userinfo['identity'],0,6)."********".substr($userinfo['identity'],-4,4) : "" ;?>
	                        </div>
	                    </div>
	                </div>
	                <div class="border_bottom mb20" style="padding-right:6.25%">
						<div class="row mb20">
							<div class="col-xs-5">
								<span class="pl">行业</span>
							</div>
							 <div class="col-xs-7 text-right cor_cd">
								<?php echo $userinfo['industry']!=0 ? $indus[$userinfo['industry']] : '';?>
							</div>
						</div>
	                </div>
					
					<div class="border_bottom mb20" style="padding-right:6.25%">
						<div class="row mb20">
							<div class="col-xs-5">
								<span class="pl">公司</span>
							</div>
							 <div class="col-xs-7 text-right cor_cd">
								<?php echo $userinfo['company'];?>
							</div>
						</div>
	                </div>
					<div class="border_bottom mb20" style="padding-right:6.25%">
						<div class="row mb20">
							<div class="col-xs-5">
								<span class="pl">职位</span>
							</div>
							 <div class="col-xs-7 text-right cor_cd">
								<?php echo $userinfo['position']!='' ? $posi[$userinfo['position']] : '';?>
							</div>
						</div>
	                </div>
                	<?php }else{?>
                	<div class="border_bottom mb20" style="padding-right:6.25%">
	                	<a href="/dev/reg/company?url=<?php echo urlencode('/dev/account/info');?>" class="cor_4">
	                    	<div class="row mb20">
	                            <div class="col-xs-4">
	                                <span class="pl">行业信息</span>
	                            </div>
								<div class="col-xs-4">
	                                <span class="non">未认证</span>
	                            </div>
	                             <div class="col-xs-4 text-right">
	                                <span class="red">去认证 <img src="/images/dev/gtred.png" width="8.5%"/></span>
	                            </div>
	                        </div>
	                    </a>
	                </div>
                	<?php }?>
                	<!-- 学校信息 -->
                	<?php if( $userinfo->school != '' ){?>
	                <div class="border_bottom mb20" style="padding-right:6.25%">
						<div class="row mb20">
							<div class="col-xs-5">
								<span class="pl">学校</span>
							</div>
							 <div class="col-xs-7 text-right cor_cd">
								<?php echo !empty($userinfo['school']) ? $userinfo['school'] : "" ;?>
							</div>
						</div>
	                </div>
					<div class="border_bottom mb20" style="padding-right:6.25%">
						<div class="row mb20">
							<div class="col-xs-5">
								<span class="pl">学历</span>
							</div>
							 <div class="col-xs-7 text-right cor_cd">
								<?php if($userinfo['edu']=='1'){?>
								博士
								<?php }else if($userinfo['edu']=='2'){?>
								硕士
								<?php }else if($userinfo['edu']=='3'){?>
								本科
								<?php }else if($userinfo['edu']=='4'){?>
								专科
								<?php }?>
							</div>
						</div>
	                </div>
					<div class="border_bottom mb20" style="padding-right:6.25%">
						<div class="row mb20">
							<div class="col-xs-5">
								<span class="pl">入学年份</span>
							</div>
							 <div class="col-xs-7 text-right cor_cd">
								<?php echo !empty($userinfo['school_time']) ? substr($userinfo['school_time'],0,4) : "" ;?>
							</div>
						</div>
	                </div>
                	<?php }else{?>
                	<div class="border_bottom mb20" style="padding-right:6.25%">
	                	<a href="/dev/reg/shool?url=<?php echo urlencode('/dev/account/info');?>" class="cor_4">
	                    	<div class="row mb20">
	                            <div class="col-xs-4">
	                                <span class="pl">学籍信息</span>
	                            </div>
								<div class="col-xs-4">
	                                <span class="non">未认证</span>
	                            </div>
	                             <div class="col-xs-4 text-right">
	                                <span class="red">去认证 <img src="/images/dev/gtred.png" width="8.5%"/></span>
	                            </div>
	                        </div>
	                    </a>
	                </div>
                	<?php }?>
                	
                <?php }?>
               <?php if( $userinfo->status == '3'){?>
               <div class="border_bottom mb20" style="padding-right:6.25%">
					<div class="row mb20">
						<div class="col-xs-5">
							<span class="pl">自拍照</span>
						</div>
						 <div class="col-xs-7 text-right cor_cd">
							已通过认证
						 </div>
					</div>
                </div>
               <?php }else{?>
	               <?php if( $userinfo->status == '2'){?>
	                <div class="border_bottom mb20" style="padding-right:6.25%">
						<div class="row mb20">
							<div class="col-xs-5">
								<span class="pl">自拍照</span>
							</div>
							 <div class="col-xs-7 text-right cor_cd">
								审核中
							 </div>
						</div>
	                </div>
	                <?php }else{?>
	                <div class="border_bottom mb20" style="padding-right:6.25%">
	                <a href="/dev/reg/pic?url=<?php echo urlencode('/dev/account/info');?>" class="cor_4">
	                	<div class="row mb20">
	                    	<div class="col-xs-4">
	                			<span class="pl">自拍照</span>
	                        </div>
							<div class="col-xs-4">
	                                <span class="non">未认证</span>
	                            </div>
	                         <div class="col-xs-4 text-right">
	                        	<span class="red">去认证 <img src="/images/dev/gtred.png" width="8.5%"/></span>
	                        </div>
	                    </div>
	                </a>
	                </div>
	                <?php }?>
               <?php }?>               
             </div>
           </div>
          </div>
          
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