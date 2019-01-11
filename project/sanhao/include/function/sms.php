<?php
//发送短信接口
	function send( $mobile , $msg )
	{	
		global $INI;
        $host = 'sms.jxtuan.com' ;
        $port = '80' ;
        $path = '/appinterfaces/sendmsg' ;
        $time = time() ;
        $timestamp = date( 'ymdHi' , $time ) ;
        $post = json_encode(array('mobile'=>$mobile,'port'=>5201,'msg'=>$msg));
		$post = substr( $post , 0 );
		$len = strlen($post);
		//发送
		$fp = @fsockopen( $host , $port, $errno, $errstr, 30 );
		if (!$fp) 
		{
		   //echo "$errstr ($errno)\n";
		   //纪录运行日志失败。请求连接不上 “找不到主机，ERRNO：$errno”
			return false;
		} 
		else 
		{
			$out = "POST $path HTTP/1.1\r\n";
			$out .= "Host: $host\r\n";
			$out .= "Content-type: application/x-www-form-urlencoded\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Content-Length: $len\r\n";
			$out .= "\r\n";
			$out .= $post."\r\n";
		    //echo($out);
		    fwrite( $fp, $out );
		    while ( !feof( $fp ) ) 
			{
		        $receive .= fgets( $fp, 128 );
		    }
		    fclose($fp);
		}
		return getResponse( $receive );
	}
	
	function getResponse($response_str)
    {
		$response = array(
		   	'header' =>array(),
		   	'body'=>array()
		   	);
    	$pos = strpos( $response_str , "\r\n\r\n" );

    	$headerStr = substr( $response_str, 0, $pos );
    	$headerAry = explode( "\r\n", $headerStr );
    	//解析状状态行
    	$status_line = array_shift( $headerAry );
    	$status_ary = explode(" ", $status_line );
    	$response['header']['status_line'] = array(
    		'http_version' => $status_ary[0],
    		'status_code'  => $status_ary[1],
    		'reason_phrase'=> substr( $status_line, strlen( $status_ary[0] ) + strlen( $status_ary[1] ) + 2 )
    	);
    	//实体报头
    	if (is_array( $headerAry ) )
    	{
			foreach ( $headerAry as $line )
			{
		    	$pp = strpos( $line , ":" );
		    	$response['header'][substr( $line, 0, $pp )] = substr( $line, $pp+1 );
			}
    	}
        //实体主体
    	$response['body'] = substr( $response_str, $pos+4 );
    	
    	$http_status_line = $response['header']['status_line'];
		$status = $http_status_line['status_code'];
		$reason_phrase = $http_status_line['reason_phrase'];
		if( $status == '200' ){
			$body = preg_replace_callback(
				        '/(?:(?:\r\n|\n)|^)([0-9A-F]+)(?:\r\n|\n){1,2}(.*?)'.
				        '((?:\r\n|\n)(?:[0-9A-F]+(?:\r\n|\n))|$)/si',
				        create_function(
				            '$matches',
				            'return hexdec($matches[1]) == strlen($matches[2]) ? $matches[2] : $matches[0];'
				        ),
				        $response['body']
				    );
			$xml = json_decode( $body ) ;
			
			return $xml ;
		}
		
		return false;
		
    }
    
    
     /**
	 * 把字符串转换为数组
	 *
	 * @param string $XML
	 * @return array
	 */
	function XMLtoArray($XML)
	{
	   $xml_parser = xml_parser_create();
	   xml_parser_set_option( $xml_parser , XML_OPTION_CASE_FOLDING , 0 );
	   xml_parse_into_struct($xml_parser, $XML, $vals);
	   xml_parser_free($xml_parser);
	   
	   //编码转换
		//array_walk_recursive ( $vals, "myIconv");
		/////////
	   // wyznaczamy tablice z powtarzajacymi sie tagami na tym samym poziomie 
	   $_tmp='';
	   foreach ($vals as $xml_elem)
	   { 
		   $x_tag=$xml_elem['tag'];
		   $x_level=$xml_elem['level'];
		   $x_type=$xml_elem['type'];
		   if ($x_level!=1 && $x_type == 'close')
		   {
			   if (isset($multi_key[$x_tag][$x_level]))
				   $multi_key[$x_tag][$x_level]=1;
			   else
				   $multi_key[$x_tag][$x_level]=0;
		   }
		   if ($x_level!=1 && $x_type == 'complete')
		   {
			   if ($_tmp==$x_tag) 
				   $multi_key[$x_tag][$x_level]=1;
			   $_tmp=$x_tag;
		   }
	   }
	   // jedziemy po tablicy
	   foreach ($vals as $xml_elem)
	   { 
		   $x_tag=$xml_elem['tag'];
		   $x_level=$xml_elem['level'];
		   $x_type=$xml_elem['type'];
		   if ($x_type == 'open') 
			   $level[$x_level] = $x_tag;
		   $start_level = 1;
		   $php_stmt = '$xml_array';
		   if ($x_type=='close' && $x_level!=1) 
			   $multi_key[$x_tag][$x_level]++;
		   while($start_level < $x_level)
		   {
				 $php_stmt .= '[$level['.$start_level.']]';
				 if (isset($multi_key[$level[$start_level]][$start_level]) && $multi_key[$level[$start_level]][$start_level]) 
					 $php_stmt .= '['.($multi_key[$level[$start_level]][$start_level]-1).']';
				 $start_level++;
		   }
		   $add='';
		   if (isset($multi_key[$x_tag][$x_level]) && $multi_key[$x_tag][$x_level] && ($x_type=='open' || $x_type=='complete'))
		   {
			   if (!isset($multi_key2[$x_tag][$x_level]))
				   $multi_key2[$x_tag][$x_level]=0;
			   else
				   $multi_key2[$x_tag][$x_level]++;
				 $add='['.$multi_key2[$x_tag][$x_level].']'; 
		   }
		   if (isset($xml_elem['value']) && trim($xml_elem['value'])!='' && !array_key_exists('attributes',$xml_elem))
		   {
			   if ($x_type == 'open') 
				   $php_stmt_main=$php_stmt.'[$x_type]'.$add.'[\'content\'] = $xml_elem[\'value\'];';
			   else
				   $php_stmt_main=$php_stmt.'[$x_tag]'.$add.' = $xml_elem[\'value\'];';
			   eval($php_stmt_main);
		   }
		   if (array_key_exists('attributes',$xml_elem))
		   {
			   if (isset($xml_elem['value']))
			   {
				   $php_stmt_main=$php_stmt.'[$x_tag]'.$add.'[\'content\'] = $xml_elem[\'value\'];';
				   eval($php_stmt_main);
			   }
			   foreach ($xml_elem['attributes'] as $key=>$value)
			   {
				   $php_stmt_att=$php_stmt.'[$x_tag]'.$add.'[$key] = $value;';
				   eval($php_stmt_att);
			   }
		   }
	   }
		 return $xml_array;
	}