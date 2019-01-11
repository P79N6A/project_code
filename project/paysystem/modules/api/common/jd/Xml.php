<?php
namespace app\modules\api\common\jd;
/**
 * Class Xml
 * 	发送给京东的数据
 * 	格式为XML
 */


class Xml{
	/**
	 * 处理数据
	 * @param $version
	 * @param $merchant
	 * @param $terminal
	 * @param $data
	 * @param $sign
	 * @return mixed
	 */
	public function xml_create($version,$merchant,$terminal,$data,$sign){
		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><chinabank/>');
		$xml->addChild('version',$version);
		$xml->addChild('merchant',$merchant);
		$xml->addChild('terminal',$terminal);
		$xml->addChild('data',$data);
		$xml->addChild('sign',$sign);
		
		/*echo "xml.php====version======".$version."<br>";
		echo "xml.php====merchant======".$merchant."<br>";
		echo "xml.php====terminal======".$terminal."<br>";
		echo "xml.php====data======".$data."<br>";
		echo "xml.php====sign======".$sign."<br>";*/

		return $xml->asXML();
	}

	/**
	 * 京东快捷签约  -- 获取验证码
	 * @param $card_bank
	 * @param $card_type
	 * @param $card_no
	 * @param $card_exp
	 * @param $card_cvv2
	 * @param $card_name
	 * @param $card_idtype
	 * @param $card_idno
	 * @param $card_phone
	 * @param $trade_type
	 * @param $trade_id
	 * @param $trade_amount
	 * @param $trade_currency
	 * @return string
	 */
	public function v_data_xml_create($card_bank,$card_type,$card_no,
								$card_exp,$card_cvv2,$card_name,
								$card_idtype,$card_idno,$card_phone,
								$trade_type,$trade_id,$trade_amount,$trade_currency,$limittime){
		$v_data = '<?xml version="1.0" encoding="UTF-8"?>'.
					'<DATA>'.
						'<CARD>'.
							'<BANK>'.$card_bank.'</BANK>'.
							'<TYPE>'.$card_type.'</TYPE>'.
							'<NO>'.$card_no.'</NO>'.
							'<EXP>'.$card_exp.'</EXP>'.
							'<CVV2>'.$card_cvv2.'</CVV2>'.
							'<NAME>'.$card_name.'</NAME>'.
							'<IDTYPE>'.$card_idtype.'</IDTYPE>'.
							'<IDNO>'.$card_idno.'</IDNO>'.
							'<PHONE>'.$card_phone.'</PHONE>'.
						'</CARD>'.
						'<TRADE>'.
							'<TYPE>'.$trade_type.'</TYPE>'.
							'<ID>'.$trade_id.'</ID>'.
							'<AMOUNT>'.$trade_amount.'</AMOUNT>'.
							'<CURRENCY>'.$trade_currency.'</CURRENCY>'.
							'<LIMITTIME>'.$limittime.'</LIMITTIME>'.
						'</TRADE>'.
					'</DATA>';
		return $v_data;
	}

	/**
	 * 京东快捷支付
	 * @param $card_bank
	 * @param $card_type
	 * @param $card_no
	 * @param $card_exp
	 * @param $card_cvv2
	 * @param $card_name
	 * @param $card_idtype
	 * @param $card_idno
	 * @param $card_phone
	 * @param $trade_type
	 * @param $trade_id
	 * @param $trade_amount
	 * @param $trade_currency
	 * @param $trade_date
	 * @param $trade_time
	 * @param $trade_notice
	 * @param $trade_note
	 * @param $trade_code
	 * @return string
	 */
	function s_data_xml_create($card_bank,$card_type,$card_no,
								$card_exp,$card_cvv2,$card_name,
								$card_idtype,$card_idno,$card_phone,
								$trade_type,$trade_id,$trade_amount,$trade_currency,
								$trade_date,$trade_time,$trade_notice,$trade_note,$trade_code){
		$v_data = '<?xml version="1.0" encoding="UTF-8"?>'.
					'<DATA>'.
						'<CARD>'.
							'<BANK>'.$card_bank.'</BANK>'.
							'<TYPE>'.$card_type.'</TYPE>'.
							'<NO>'.$card_no.'</NO>'.
							'<EXP>'.$card_exp.'</EXP>'.
							'<CVV2>'.$card_cvv2.'</CVV2>'.
							'<NAME>'.$card_name.'</NAME>'.
							'<IDTYPE>'.$card_idtype.'</IDTYPE>'.
							'<IDNO>'.$card_idno.'</IDNO>'.
							'<PHONE>'.$card_phone.'</PHONE>'.
						'</CARD>'.
						'<TRADE>'.
							'<TYPE>'.$trade_type.'</TYPE>'.
							'<ID>'.$trade_id.'</ID>'.
							'<AMOUNT>'.$trade_amount.'</AMOUNT>'.
							'<CURRENCY>'.$trade_currency.'</CURRENCY>'.
							'<DATE>'.$trade_date.'</DATE>'.
							'<TIME>'.$trade_time.'</TIME>'.
							'<NOTICE>'.$trade_notice.'</NOTICE>'.
							'<NOTE>'.$trade_note.'</NOTE>'.
							'<CODE>'.$trade_code.'</CODE>'.
						'</TRADE>'.
					'</DATA>';
		return $v_data;
	}

	/**
	 * 京东快捷支付的退款---------暂时不用
	 * @param $trade_type
	 * @param $trade_id
	 * @param $trade_oid
	 * @param $trade_amount
	 * @param $trade_currency
	 * @param $trade_date
	 * @param $trade_time
	 * @param $trade_notice
	 * @param $trade_note
	 * @return string
	 */
	function r_data_xml_create($trade_type,$trade_id,$trade_oid,$trade_amount,
									$trade_currency,$trade_date,$trade_time,$trade_notice,$trade_note){
		$v_data = '<?xml version="1.0" encoding="UTF-8"?>'.
			'<DATA>'.
				'<TRADE>'.
					'<TYPE>'.$trade_type.'</TYPE>'.
					'<ID>'.$trade_id.'</ID>'.
					'<OID>'.$trade_oid.'</OID>'.
					'<AMOUNT>'.$trade_amount.'</AMOUNT>'.
					'<CURRENCY>'.$trade_currency.'</CURRENCY>'.
					'<DATE>'.$trade_date.'</DATE>'.
					'<TIME>'.$trade_time.'</TIME>'.
					'<NOTICE>'.$trade_notice.'</NOTICE>'.
					'<NOTE>'.$trade_note.'</NOTE>'.
				'</TRADE>'.
			'</DATA>';
		return $v_data;
	}

	/**
	 * 京东订单查询
	 * @param $trade_type
	 * @param $trade_id
	 * @return string
	 */
	function q_data_xml_create($trade_type,$trade_id){
		$v_data = '<?xml version="1.0" encoding="UTF-8"?>'.
			'<DATA>'.
				'<TRADE>'.
					'<TYPE>'.$trade_type.'</TYPE>'.
					'<ID>'.$trade_id.'</ID>'.
				'</TRADE>'.
			'</DATA>';
		return $v_data;
	}

}
?>
