<?php
include_once( 'config.php' );
include_once( 'txwboauth.php' );


$o = new WeiboOAuth( WB_AKEY , WB_SKEY , $_SESSION['keys']['oauth_token'] , $_SESSION['keys']['oauth_token_secret']  );
$last_key = $o->getAccessToken(  $_REQUEST['oauth_verifier'] ) ;//获取ACCESSTOKEN
$_SESSION['last_key'] = $last_key;

Utility::Redirect( WEB_ROOT . '/thirdpart/qq/auth.php' );
