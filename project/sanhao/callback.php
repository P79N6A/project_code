<?php
require_once(dirname(__FILE__) . '/app.php');


$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'weibo';

if($action == "weibo")
{
	require_once(dirname(__FILE__). '/weibo/config.php');
	require_once(dirname(__FILE__). '/weibo/saetv2.ex.class.php');
	
	$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
	if (isset($_REQUEST['code'])) 
	{
		$keys = array();
		$keys['code'] = $_REQUEST['code'];
		$keys['redirect_uri'] = WB_CALLBACK_URL;
		try {
			$token = $o->getAccessToken( 'code', $keys ) ;
		} catch (OAuthException $e) {
		}
	}
	if ($token) 
	{
		$_SESSION['token'] = $token;
		setcookie( 'weibojs_'.$o->client_id, http_build_query($token) );
	}
	get_userinfo();
}
else if($action == "qq")
{
	require_once(dirname(__FILE__).'/comm/config.php');
	if(isset($_REQUEST['state'])) //csrf
	{
	    $token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
	        . "client_id=" . $_SESSION["appid"]. "&redirect_uri=" . urlencode($_SESSION["callback"])
	        . "&client_secret=" . $_SESSION["appkey"]. "&code=" . $_REQUEST["code"];
	
	    $response = file_get_contents($token_url);
	    if (strpos($response, "callback") !== false)
	    {
	        $lpos = strpos($response, "(");
	        $rpos = strrpos($response, ")");
	        $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
	        $msg = json_decode($response);
	        if (isset($msg->error))
	        {
	            echo "<h3>error:</h3>" . $msg->error;
	            echo "<h3>msg  :</h3>" . $msg->error_description;
	            exit;
	        }
	    }
	
	    $params = array();
	    parse_str($response, $params);
	
	    //debug
	    //print_r($params);
	
	    //set access token to session
	    $_SESSION["access_token"] = $params["access_token"];
	
	}
	else 
	{
	    echo("The state does not match. You may be a victim of CSRF.");
	}
	get_openid();
}


