<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(WWW_ROOT.'/weibo/KafkaConfig.php');
require_once(WWW_ROOT.'/weibo/saetv2.ex.class.php');
require_once(WWW_ROOT.'/comm/utils.php');

need_login();
need_checksns();
//接受js文件传递过来的商品信息，商品信息以json格式传递过来
if ( $_POST ) 
{
	$sproduct = $_POST['product'];
	$aproduct = json_decode($sproduct);
	$u['uid'] = $login_user_id;
	$u['pname'] = $aproduct->productname;
	$u['description'] =$aproduct->productdescription;
	$u['price'] = $aproduct->price;
	$u['type'] = 1;
	if(!empty($aproduct->max_number))
	{
		$u['max_number'] = $aproduct->max_number;
	}
	if(!empty($aproduct->old_price))
	{
		$u['old_price'] = $aproduct->old_price;
	}
	if(!empty($aproduct->express_price))
	{
		$u['express_price'] = $aproduct->express_price;
	}
	if(!empty($aproduct->end_time))
	{
		$u['end_time'] = $aproduct->end_time;
	}
	$u['status'] = 1;
	$u['createtime'] = time();
	$u['id'] = DB::Insert('jx_products', $u);
	if($u['id'])
	{
		//获取传递过来的图片信息
		$aImage = $aproduct->image;
		foreach ($aImage as $key=>$value)
		{
			$productimage['pid'] = $u['id'];
			$productimage['image'] = $value->picurl;
			$productimage['type'] = $value->type;
			$productimage['createtime'] = time();
			DB::Insert('jx_products_image', $productimage);
		}
		//获取传递过来的商品属性值
		$apropertype = $aproduct->property;
		$icount = count($apropertype);
		if($icount > 0)
		{
			foreach ($apropertype as $k=>$v)
			{
				$property['pid'] = $u['id'];
				$property['name'] = $v->name;
				$property['content'] = $v->content;
				$property['createtime'] = time();
				DB::Insert('jx_products_property', $property);
			}
		}
		//判断用户是否绑定了新浪微博和QQ，如果绑定了，则向用户的新浪微博发送一条微博信息
		$conditionqq = array( 'uid' => $login_user_id, 'sns_type' => 'qq');
		$userqq = DB::LimitQuery('jx_users_sns', array(
					'condition' => $conditionqq,
					'one' => true,
		));
		
		$conditionweibo = array( 'uid' => $login_user_id, 'sns_type' => 'weibo');
		$userweibo = DB::LimitQuery('jx_users_sns', array(
					'condition' => $conditionweibo,
					'one' => true,
		));
		$u['description']=str_replace("\n","",$u['description']);
		$u['description']=str_replace("\r","",$u['description']);
		$u['description']=str_replace("\r\n","",$u['description']);
		$productweibo = subtostring($u['pname'].','.$u['description'], 50).'我在@三好网 发起了一个链接，查看请点击：';
		$productweibourl = $INI['system']['wwwprefix']."/".$aImage[0]->picurl;
		//想腾讯微博发送一条微博
//		if(!empty($userqq['sns_id']) && !empty($userqq['sns_token']))
//		{
//			$url  = "https://graph.qq.com/t/add_pic_t";
//		    $data = "access_token=".$userqq['sns_token']."&oauth_consumer_key=".$_SESSION["appid"]."&openid=".$userqq['sns_id']."&format=json"."&content=".urlencode($productweibo)."&pic=".urlencode($productweibourl);
//		    $ret = do_post($url, $data); 
//		    $result = json_decode($ret);
//		    //如果$result->ret == 0，则表示发布成功
//		}
		//向新浪微博发送一条微博
		if(!empty($userweibo['sns_id']) && !empty($userweibo['sns_token']))
		{
			$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $userweibo['sns_token'] );
			$status = $c->upload( $productweibo, $productweibourl );
			if(isset($status['id']))
			{
				echo $u['id'];
			}
			else 
			{
				echo $u['id'];
			}
		}
		else 
		{
			echo $u['id'];
		}
	}
	else 
	{
		echo 'fail';
	}
	exit;
}

$pagetitle = '发布商品';
include template('account_productrelease');