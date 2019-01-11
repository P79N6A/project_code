<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');
session_start();
include_once( 'config.php' );
include_once( 'weibooauth.php' );



$o = new WeiboOAuth( WB_AKEY , WB_SKEY , $_SESSION['keys']['oauth_token'] , $_SESSION['keys']['oauth_token_secret']  );

$last_key = $o->getAccessToken(  $_REQUEST['oauth_verifier'] ) ;

$_SESSION['last_key'] = $last_key;

Utility::Redirect( WEB_ROOT . '/thirdpart/sinaweibo/auth.php' );
