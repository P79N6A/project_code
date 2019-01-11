<?php
class Mobile
{
	public static function getmobile($num=''){
		$array_code = array(
				'20'=>array(
				    '0' => array(
				    		'id'=> 0 ,
				    		'name'=>'王投资',
				    		'mobile'=>'13000000000'
				    	
				    ),
				    '1' => array(
				    		'id'=> -1 ,
				    		'name'=>'赵投资',
				    		'mobile'=>'13000000001'
				    	
				    ),
				    '2' => array(
				    		'id'=> -2 ,
				    		'name'=>'吴投资',
				    		'mobile'=>'13000000002'
				    	
				    ),
				    '3' => array(
				    		'id'=> -3 ,
				    		'name'=>'李投资',
				    		'mobile'=>'13000000003'
				    	
				    ),
				    '4' => array(
				    		'id'=> -4 ,
				    		'name'=>'周投资',
				    		'mobile'=>'13000000004'
				    	
				    ),
				    '5' => array(
				    		'id'=> -5 ,
				    		'name'=>'候投资',
				    		'mobile'=>'13000000005'
				    	
				    ),
				    '6' => array(
				    		'id'=> -6 ,
				    		'name'=>'郑投资',
				    		'mobile'=>'13000000006'
				    	
				    ),
				    '7' => array(
				    		'id'=> -7 ,
				    		'name'=>'王投资',
				    		'mobile'=>'13000000007'
				    	
				    ),
				    '8' => array(
				    		'id'=> -8 ,
				    		'name'=>'陆投资',
				    		'mobile'=>'13000000008'
				    	
				    ),
				    '9' => array(
				    		'id'=> -9 ,
				    		'name'=>'冯投资',
				    		'mobile'=>'13000000009'
				    	
				    ),
				    '10' => array(
				    		'id'=> -10 ,
				    		'name'=>'陈投资',
				    		'mobile'=>'13000000010'
				    	
				    ),
				    '11' => array(
				    		'id'=> -11 ,
				    		'name'=>'蒋投资',
				    		'mobile'=>'13000000011'
				    	
				    ),
				    '12' => array(
				    		'id'=> -12 ,
				    		'name'=>'沈投资',
				    		'mobile'=>'13000000012'
				    	
				    ),
				    '13' => array(
				    		'id'=> -13 ,
				    		'name'=>'韩投资',
				    		'mobile'=>'13000000013'
				    	
				    ),
				    '14' => array(
				    		'id'=> -14 ,
				    		'name'=>'杨投资',
				    		'mobile'=>'13000000014'
				    	
				    ),
				    '15' => array(
				    		'id'=> -15 ,
				    		'name'=>'朱投资',
				    		'mobile'=>'13000000015'
				    	
				    ),
				    '16' => array(
				    		'id'=> -16 ,
				    		'name'=>'许投资',
				    		'mobile'=>'13000000016'
				    	
				    ),
				    '17' => array(
				    		'id'=> -17 ,
				    		'name'=>'何投资',
				    		'mobile'=>'13000000017'
				    	
				    ),
				    '18' => array(
				    		'id'=> -18 ,
				    		'name'=>'曹投资',
				    		'mobile'=>'13000000018'
				    	
				    ),
				    '19' => array(
				    		'id'=> -19 ,
				    		'name'=>'陶投资',
				    		'mobile'=>'13000000019'
				    	
				    ),
				    '20' => array(
				    		'id'=> -20 ,
				    		'name'=>'喻投资',
				    		'mobile'=>'13000000020'
				    	
				    )
			),
			'10' => array(
					'0' => array(
							'id'=> 0 ,
							'name'=>'程投资',
							'mobile'=>'13000000000'
					
					),
					'1' => array(
							'id'=> -1 ,
							'name'=>'韩投资',
							'mobile'=>'13000000001'
					
					),
					'2' => array(
							'id'=> -2 ,
							'name'=>'孙投资',
							'mobile'=>'13000000002'
					
					),
					'3' => array(
							'id'=> -3 ,
							'name'=>'王投资',
							'mobile'=>'13000000003'
					
					),
					'4' => array(
							'id'=> -4 ,
							'name'=>'周投资',
							'mobile'=>'13000000004'
					
					),
					'5' => array(
							'id'=> -5 ,
							'name'=>'张投资',
							'mobile'=>'13000000005'
					
					),
					'6' => array(
							'id'=> -6 ,
							'name'=>'郑投资',
							'mobile'=>'13000000006'
					
					),
					'7' => array(
							'id'=> -7 ,
							'name'=>'李投资',
							'mobile'=>'13000000007'
					
					),
					'8' => array(
							'id'=> -8 ,
							'name'=>'王投资',
							'mobile'=>'13000000008'
					
					),
					'9' => array(
							'id'=> -9 ,
							'name'=>'冯投资',
							'mobile'=>'13000000009'
					
					)
			),
			'5' => array(
					'0' => array(
							'id'=> 0 ,
							'name'=>'吴投资',
							'mobile'=>'13000000000'
					
					),
					'1' => array(
							'id'=> -1 ,
							'name'=>'张投资',
							'mobile'=>'13000000001'
					
					),
					'2' => array(
							'id'=> -2 ,
							'name'=>'李投资',
							'mobile'=>'13000000002'
					
					),
					'3' => array(
							'id'=> -3 ,
							'name'=>'陈投资',
							'mobile'=>'13000000003'
					
					),
					'4' => array(
							'id'=> -4 ,
							'name'=>'周投资',
							'mobile'=>'13000000004'
					
					)
			),
			'3' => array(
					'0' => array(
							'id'=> 0 ,
							'name'=>'周投资',
							'mobile'=>'13000000000'
					
					),
					'1' => array(
							'id'=> -1 ,
							'name'=>'王投资',
							'mobile'=>'13000000001'
					
					),
					'2' => array(
							'id'=> -2 ,
							'name'=>'高投资',
							'mobile'=>'13000000002'
					
					)
			)
		);
		if($num == ''){
			return $array_code;
		}else{
			return $array_code[$num];
		}
	}
	
	public static function getshare($num){
		$array_code = array(
			'0' => '100',
			'1' => '1500',
			'2' => '300',
			'3' => '2000',
			'4' => '500',
			'5' => '1000',
			'6' => '700',
			'7' => '800',
			'8' => '1000',
			'9' => '1500',
			'10' => '2000',
			'11' => '3000',
			'12' => '5000',
			'13' => '6000',
			'14' => '8000',
			'15' => '10000',
		);
		return $array_code[$num];	
	}
	
	public static function getusername($num){
		$array_code = array(
			'0' => array(
					'id'=> 0 ,
					'name'=>'周投资',
					'mobile'=>'13000000000'
				 
			),	
			'-1' => array(
					'id'=> -1 ,
					'name'=>'王投资',
					'mobile'=>'13000000001'
			),
			'-2' => array(
					'id'=> -2 ,
					'name'=>'李投资',
					'mobile'=>'13000000002'
			 
			),
			'-3' => array(
					'id'=> -3 ,
					'name'=>'高投资',
					'mobile'=>'13000000003'
			 
			),
			'-4' => array(
					'id'=> -4 ,
					'name'=>'陈投资',
					'mobile'=>'13000000004'
			 
			),
			'-5' => array(
					'id'=> -5 ,
					'name'=>'吴投资',
					'mobile'=>'13000000005'
			 
			),
			'-6' => array(
					'id'=> -6 ,
					'name'=>'程投资',
					'mobile'=>'13000000006'
			 
			),
			'-7' => array(
					'id'=> -7 ,
					'name'=>'马投资',
					'mobile'=>'13000000007'
			 
			),
			'-8' => array(
					'id'=> -8 ,
					'name'=>'韩投资',
					'mobile'=>'13000000008'
			 
			),
			'-9' => array(
					'id'=> -9 ,
					'name'=>'罗投资',
					'mobile'=>'13000000009'
			 
			),
			'-10' => array(
					'id'=> -10 ,
					'name'=>'陈投资',
					'mobile'=>'13000000010'
			 
			),
			'-11' => array(
					'id'=> -11 ,
					'name'=>'蒋投资',
					'mobile'=>'13000000011'
			 
			),
			'-12' => array(
					'id'=> -12 ,
					'name'=>'沈投资',
					'mobile'=>'13000000012'
			 
			),
			'-13' => array(
					'id'=> -13 ,
					'name'=>'喻投资',
					'mobile'=>'13000000013'
			 
			),
			'-14' => array(
					'id'=> -14 ,
					'name'=>'杨投资',
					'mobile'=>'13000000014'
			 
			),
			'-15' => array(
					'id'=> -15 ,
					'name'=>'朱投资',
					'mobile'=>'13000000015'
			 
			),
			'-16' => array(
					'id'=> -16 ,
					'name'=>'许投资',
					'mobile'=>'13000000016'
			 
			),
			'-17' => array(
					'id'=> -17 ,
					'name'=>'何投资',
					'mobile'=>'13000000017'
			 
			),
			'-18' => array(
					'id'=> -18 ,
					'name'=>'曹投资',
					'mobile'=>'13000000018'
			 
			),
			'-19' => array(
					'id'=> -19 ,
					'name'=>'陶投资',
					'mobile'=>'13000000019'
			 
			),
			'-20' => array(
					'id'=> -20 ,
					'name'=>'黄投资',
					'mobile'=>'13000000020'
			 
			)
		);
		return $array_code[$num];
	}
}