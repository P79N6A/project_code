<?php

namespace app\commonapi;

use app\common\ApiClientCrypt;
use app\models\news\Favorite_contacts;
use app\models\own\Address_list;
use yii\helpers\ArrayHelper;

class JxldownSysloan {

	private $res_code; // 0表示无错误
	public $res_data;

	public function __construct($phone, $website = '', $number = 1) {
		$url			 = "juxinli/querynumber";
		$openApi		 = new ApiClientCrypt;
		$res			 = $openApi->sent($url, [
			'phone'		 => $phone,
			'website'	 => $website,
			'number'	 => $number,
		]);
		$res			 = $openApi->parseResponse($res);
		Logger::errorLog(print_r($res, true), 'jxinLi', 'jxinLi');
		$this->res_code	 = $res['res_code'];
		$this->res_data	 = $res['res_data'];
	}

	public function getRes() {
		return $this->res_data;
	}

	/**
	 * 下载聚信立报告和详情
	 * @return [type] [description]
	 */
	public function report() {
		if ($this->res_code) {
			return null;
		}
		$url = $this->res_data['f_stat'];
//        $url = str_replace("10.139.36.194", "182.92.80.211:8091", $url);//TODO 上线前删除
//        $url = str_replace("open.xianhuahua.com", "182.92.80.211:8091", $url);//TODO 上线前删除
		return $this->curlGet($url);
	}

	public function detail() {
		if ($this->res_code) {
			return null;
		}
		$url = $this->res_data['f_detail'];
//        $url = str_replace("10.139.36.194", "182.92.80.211:8091", $url);//TODO 上线前删除
//        $url = str_replace("open.xianhuahua.com", "182.92.80.211:8091", $url);//TODO 上线前删除
		return $this->curlGet($url);
	}

	//上树运营商报告（source = 4）
	public function all($userId = 0) {
		if ($this->res_code) {
			return null;
		}
		$json	 = $this->report();
		$jsons	 = json_decode($json['bizContent'], true);
		if (!$jsons) {
			Logger::dayLog('juxinlierror', '1', $json, $jsons);
			return null;
		}
		$jsonss = json_decode($jsons['dataReport'], true);
		if (!$jsonss) {
			Logger::dayLog('juxinlierror', '2', $jsons, $jsonss);
			return null;
		}
		$reportRetain	 = array_keys($this->listReportRetain());
		$listCallRanking = array_keys($this->listCallRanking());
		$reportArr		 = array();
		$callRankingArr	 = array();
		$phoneLabelArr	 = array();
		$topArr			 = array();
		foreach ($jsonss as $item) {
			//运营商报告
			if (in_array($item['labelName'], $reportRetain)) {
				$item['type']	 = $this->listReportRetain()[$item['labelName']]['type'];
				$item['sort']	 = $this->listReportRetain()[$item['labelName']]['sort'];
				$reportArr[]	 = $item;
			}
			//省市通话
			if (in_array($item['labelName'], $listCallRanking)) {
				$rank										 = $this->listCallRanking()[$item['labelName']]['rank'];
				$callRankingArr[$rank][$item['labelName']]	 = (isset($item['value']) && $item['value']) ? $item['value'] : ((isset($item['value']) && $item['value'] == 0) ? 0 : '未知');
				;
			}
			//通话类型分布
			if ($item['labelName'] == 'phone_label_detail') {
				$phoneLabelArr = $item;
			}
		}

		//通话详单
		$topDetailArr = $this->listTopDetail();

		//联系人统计
		$favoriteArr = $this->getFavorite($userId, $topDetailArr);

		//top50通话人统计
		$topArr = $this->listTop($userId, $topDetailArr);

		$this->sortArrByField($reportArr, 'sort');
		$resture['JSON_INFO']['report']			 = $reportArr;
		$resture['JSON_INFO']['callRanking']	 = $callRankingArr;
		$resture['JSON_INFO']['phoneLabel']		 = $phoneLabelArr;
		$resture['JSON_INFO']['top']			 = $topArr;
		$resture['JSON_INFO']['favorite']		 = $favoriteArr;
		$resture['JSON_INFO']['naturalPerson']	 = $jsons['naturalPerson'];
		return $resture;
	}

	//引流平台
	public function drainageAll($userId = 0) {
		if ($this->res_code) {
			return null;
		}
		$drainageAll = $this->report();

		//通话详单
		$topDetailArr = $this->listTopDetail();

		//联系人统计
		$favoriteArr = $this->getFavorite($userId, $topDetailArr);

		//top50通话人统计或自带通话详单分析
		$topArr	 = '';
		$info	 = $this->listTopOrDrainage($userId, $topDetailArr, $drainageAll);
		if ($info['status'] == 1) {
			$topArr = $info['data'];
		} else {
			$drainageAll = $info['data'];
		}
		if (isset($drainageAll['from']) && $drainageAll['from'] != 'jlm') {
			$from = 'report_' . $drainageAll['from'];
		} else {
			$from = 'report_br';
		}
		$resture['JSON_INFO']['report']		 = $drainageAll;
		$resture['JSON_INFO']['from']		 = $from;
		$resture['JSON_INFO']['top']		 = $topArr;
		$resture['JSON_INFO']['favorite']	 = $favoriteArr;
		return $resture;
	}

	//获取top50或处理报告自带详单
	private function listTopOrDrainage($userId, $topDetailArr, $drainageAll) {
		if (isset($drainageAll['from']) && in_array($drainageAll['from'], ['rong360'])) {
			$phoneArr = [];
			switch ($drainageAll['from']) {
				case 'rong360':
					if (isset($drainageAll['biz_data']['call_log']) && !empty($drainageAll['biz_data']['call_log'])) {
						$phoneArr							 = ArrayHelper::getColumn($drainageAll['biz_data']['call_log'], 'phone');
						$drainageAll['biz_data']['call_log'] = $this->listAddress($userId, $phoneArr, $drainageAll['biz_data']['call_log']);
					}
					break;
			}
			return ['status' => 2, 'data' => $drainageAll];
		} else {
			$topArr = $this->listTop($userId, $topDetailArr);
			return ['status' => 1, 'data' => $topArr];
		}
	}

	//匹配通讯录姓名
	private function listAddress($userId, $topPhoneArr, $topArr) {
		$addressArr = [];
		if ($userId > 0 && !empty($topPhoneArr) && is_array($topPhoneArr)) {
			$oAddressModel	 = new Address_list();
			$addressArr		 = $oAddressModel->getAddressList($userId, $topPhoneArr);
		}
		if (!empty($addressArr)) {
			foreach ($topArr as &$item) {
				$item['name'] = '';
				foreach ($addressArr as $value) {
					if ($item['phone'] == $value->phone) {
						$item['name'] = $value->name;
					}
				}
			}
		}
		return $topArr;
	}

	private function curlGet($url) {
		$json = Http::getCurl($url);
		if (!$json) {
			return null;
		}
		return json_decode($json, true);
	}

	private function object2array(&$object) {
		$object = json_decode(json_encode($object), true);
		return $object;
	}

	/**
	 * 运营商报告显示字段映射
	 * type 0默认 1加背景色 2字体加红加粗
	 * sort 排序
	 * @return array
	 */
	private function listReportRetain() {
		return [
			'extend_certifed_status'			 => ['desc' => '是否实名制', 'type' => 1, 'sort' => 1],
			'idCardNoMatchFlag'					 => ['desc' => '身份证号是否与运营商数据匹配', 'type' => 1, 'sort' => 2],
			'nameMatchFlag'						 => ['desc' => '姓名是否与运营商数据匹配', 'type' => 1, 'sort' => 3],
			'extend_join_dt'					 => ['desc' => '手机入网时长', 'type' => 0, 'sort' => 4],
			'bill_amt'							 => ['desc' => '账单金额', 'type' => 0, 'sort' => 5],
			'rsm_tel_sta_vc_high_num'			 => ['desc' => '高频通话人数', 'type' => 1, 'sort' => 8],
			'pn_active_cnt_near_six_month'		 => ['desc' => '近6个月手机活跃月份数', 'type' => 1, 'sort' => 10],
			'pn_shutdown_more_three_day_cnt'	 => ['desc' => '关机三天(含)以上的次数', 'type' => 1, 'sort' => 21],
			'pn_shutdown_ratio_near_three_day'	 => ['desc' => '最近3天关机比率', 'type' => 1, 'sort' => 22],
			'pn_silence_day_cnt'				 => ['desc' => '手机静默使用情况', 'type' => 1, 'sort' => 23],
			'rsm_tel_sta_vc_1_tot_cnt'			 => ['desc' => '近1月内通话次数', 'type' => 0, 'sort' => 29],
			'rsm_tel_sta_vc_1_tot_num'			 => ['desc' => '近1月内通话号码数', 'type' => 0, 'sort' => 30],
			'pn_silence_day_cnt_m1'				 => ['desc' => '近一月内手机静默使用情况', 'type' => 0, 'sort' => 41],
			'rsm_tel_sta_vc_3_tot_cnt'			 => ['desc' => '近3月内通话次数', 'type' => 0, 'sort' => 42],
			'rsm_tel_sta_vc_3_tot_num'			 => ['desc' => '近3月内通话号码数', 'type' => 0, 'sort' => 43],
			'pn_silence_day_cnt_m3'				 => ['desc' => '三月内手机静默使用情况', 'type' => 0, 'sort' => 44],
			'rsm_tel_sta_vc_avg_dur'			 => ['desc' => '平均呼叫时长', 'type' => 1, 'sort' => 24],
			'rsm_tel_sta_vc_avg_in_dur'			 => ['desc' => '平均呼入时长', 'type' => 1, 'sort' => 25],
			'rsm_tel_sta_vc_avg_out_dur'		 => ['desc' => '平均呼出时长', 'type' => 1, 'sort' => 26],
			'rsm_tel_sta_vc_6_long_in_cnt'		 => ['desc' => '近6个月1分钟以上呼入次数', 'type' => 0, 'sort' => 45],
			'rsm_tel_sta_vc_6_long_out_cnt'		 => ['desc' => '近6个月1分钟以上呼出次数', 'type' => 0, 'sort' => 46],
			'rsm_tel_sta_vc_6_out_num'			 => ['desc' => '近6个月呼出号码数', 'type' => 0, 'sort' => 47],
			'rsm_tel_sta_vc_6_tot_cnt'			 => ['desc' => '近6个月呼叫次数', 'type' => 0, 'sort' => 48],
			'rsm_tel_sta_vc_6_tot_num'			 => ['desc' => '近6个月通话号码数', 'type' => 0, 'sort' => 49],
			'rsm_tel_sta_vc_both_cnt_per'		 => ['desc' => '双向通话次数比例', 'type' => 1, 'sort' => 27],
			'rsm_tel_sta_vc_both_num_per'		 => ['desc' => '双向通话人数比例', 'type' => 1, 'sort' => 28],
			'tel_in_0_7_cnt_ratio'				 => ['desc' => '时段1（0-7）通话次数比例', 'type' => 1, 'sort' => 50],
			'tel_in_7_11_cnt_ratio'				 => ['desc' => '时段2（7-11）通话次数比例', 'type' => 1, 'sort' => 61],
			'tel_in_11_14_cnt_ratio'			 => ['desc' => '时段3（11-14）通话次数比例', 'type' => 1, 'sort' => 62],
			'tel_in_14_19_cnt_ratio'			 => ['desc' => '时段4（14-19）通话次数比例', 'type' => 1, 'sort' => 63],
			'tel_in_19_24_cnt_ratio'			 => ['desc' => '时段5（19-24）通话次数比例', 'type' => 1, 'sort' => 64],
			'tel_in_22_7_clock_ratio'			 => ['desc' => '夜间（22点-7点）通话时长比', 'type' => 2, 'sort' => 65],
			'tel_in_22_7_cnt_ratio'				 => ['desc' => '夜间（22点-7点）通话次数占比', 'type' => 2, 'sort' => 66],
			'rsm_tel_sta_vc_r1_cnt_per'			 => ['desc' => '时段1（0-8）通话次数比例', 'type' => 1, 'sort' => 67],
			'rsm_tel_sta_vc_r2_cnt_per'			 => ['desc' => '时段2（8-18）通话次数比例', 'type' => 1, 'sort' => 68],
			'rsm_tel_sta_vc_r3_cnt_per'			 => ['desc' => '时段3（18-24）通话次数比例', 'type' => 1, 'sort' => 69],
			'rsm_sta_con_avg_amt'				 => ['desc' => '月均消费金额', 'type' => 0, 'sort' => 6],
			'rsm_net_flow_total'				 => ['desc' => '流量使用总数量', 'type' => 0, 'sort' => 7],
			'rsm_vc_city_cnt'					 => ['desc' => '通话地数量', 'type' => 1, 'sort' => 9],
			'score'								 => ['desc' => '个人芝麻分', 'type' => 0, 'sort' => 70],
			'rain_risk_reason'					 => ['desc' => '手机号风险原因(数字越大风险越高)', 'type' => 0, 'sort' => 81],
			'rain_score'						 => ['desc' => '手机号风险评分', 'type' => 0, 'sort' => 82],
			'digital_identity'					 => ['desc' => 'JS版设备指纹', 'type' => 0, 'sort' => 83],
			'consume_fund_index'				 => ['desc' => '人脉圈消费资产指数', 'type' => 0, 'sort' => 84],
			'indentity_risk_index'				 => ['desc' => '人脉圈风险指数', 'type' => 0, 'sort' => 85],
			'social_stability_index'			 => ['desc' => '人脉圈社交稳定性指数', 'type' => 0, 'sort' => 86],
		];
	}

	/**
	 * 通话城市排名
	 * rank 排名 1第一 2第二 3第三
	 * @return array
	 */
	private function listCallRanking() {
		return [
			'rsm_tel_sta_vc_1_prov'		 => ['desc' => '通话次数第一的省份', 'rank' => 1],
			'rsm_tel_sta_vc_1_city'		 => ['desc' => '通话次数第一的城市', 'rank' => 1],
			'rsm_tel_sta_vc_1_high_num'	 => ['desc' => '通话次数第一的城市的高频通话人数', 'rank' => 1],
			'rsm_tel_sta_vc_1_num'		 => ['desc' => '通话次数第一的城市的通话人数', 'rank' => 1],
			'rsm_tel_sta_vc_1_cnt'		 => ['desc' => '通话次数第一的城市的通话次数', 'rank' => 1],
			'rsm_tel_sta_vc_2_prov'		 => ['desc' => '通话次数第二的省份', 'rank' => 2],
			'rsm_tel_sta_vc_2_city'		 => ['desc' => '通话次数第二的城市', 'rank' => 2],
			'rsm_tel_sta_vc_2_high_num'	 => ['desc' => '通话次数第二的城市的高频通话人数', 'rank' => 2],
			'rsm_tel_sta_vc_2_num'		 => ['desc' => '通话次数第二的城市的通话人数', 'rank' => 2],
			'rsm_tel_sta_vc_2_cnt'		 => ['desc' => '通话次数第二的城市的通话次数', 'rank' => 2],
			'rsm_tel_sta_vc_3_prov'		 => ['desc' => '通话次数第三的省份', 'rank' => 3],
			'rsm_tel_sta_vc_3_city'		 => ['desc' => '通话次数第三的城市', 'rank' => 3],
			'rsm_tel_sta_vc_3_high_num'	 => ['desc' => '通话次数第三的城市的高频通话人数', 'rank' => 3],
			'rsm_tel_sta_vc_3_num'		 => ['desc' => '通话次数第三的城市的通话人数', 'rank' => 3],
			'rsm_tel_sta_vc_3_cnt'		 => ['desc' => '通话次数第三的城市的通话次数', 'rank' => 3],
		];
	}

	//top通话详单处理
	private function listTopDetail() {
		$detailArr = $this->detail();
		if (!$detailArr) {
			return null;
		}
		$Calling = ['主叫', '4G高清语音主叫', 'VOLTE主叫', 'DIAL'];
		$called	 = ['被叫', '4G高清语音被叫', 'VOLTE被叫', 'DIALED'];
		$topArr	 = [];
		if (isset($detailArr['raw_data']['members']['transactions']['0']['calls']) && !empty($detailArr['raw_data']['members']['transactions']['0']['calls'])) {
			foreach ($detailArr['raw_data']['members']['transactions']['0']['calls'] as $item) {
				if (array_key_exists($item['other_cell_phone'], $topArr)) {
					$topArr[$item['other_cell_phone']]['tel_cnt'] ++;
					$topArr[$item['other_cell_phone']]['tel_clock'] += $item['use_time'];
					if (in_array($item['init_type'], $Calling)) {
						$topArr[$item['other_cell_phone']]['call_clock'] += $item['use_time'];
						$topArr[$item['other_cell_phone']]['call_cnt'] ++;
					} elseif (in_array($item['init_type'], $called)) {
						$topArr[$item['other_cell_phone']]['called_clock'] += $item['use_time'];
						$topArr[$item['other_cell_phone']]['called_cnt'] ++;
					}
					if (empty($topArr[$item['other_cell_phone']]['earliest'])) {
						$topArr[$item['other_cell_phone']]['earliest'] = $item['start_time'];
					} else {
						if ($item['start_time'] < $topArr[$item['other_cell_phone']]['earliest']) {
							$topArr[$item['other_cell_phone']]['earliest'] = $item['start_time'];
						}
					}
					if (empty($topArr[$item['other_cell_phone']]['latest'])) {
						$topArr[$item['other_cell_phone']]['latest'] = $item['start_time'];
					} else {
						if ($item['start_time'] > $topArr[$item['other_cell_phone']]['latest']) {
							$topArr[$item['other_cell_phone']]['latest'] = $item['start_time'];
						}
					}
				} else {
					$topArr[$item['other_cell_phone']]['phone_number']	 = $item['other_cell_phone'];
					$topArr[$item['other_cell_phone']]['name']			 = '';
					if (in_array($item['init_type'], $Calling)) {
						$topArr[$item['other_cell_phone']]['call_clock']	 = $item['use_time'];
						$topArr[$item['other_cell_phone']]['called_clock']	 = 0;
						$topArr[$item['other_cell_phone']]['call_cnt']		 = 1;
						$topArr[$item['other_cell_phone']]['called_cnt']	 = 0;
					} elseif (in_array($item['init_type'], $called)) {
						$topArr[$item['other_cell_phone']]['call_clock']	 = 0;
						$topArr[$item['other_cell_phone']]['called_clock']	 = $item['use_time'];
						$topArr[$item['other_cell_phone']]['call_cnt']		 = 0;
						$topArr[$item['other_cell_phone']]['called_cnt']	 = 1;
					} else {
						$topArr[$item['other_cell_phone']]['call_clock']	 = 0;
						$topArr[$item['other_cell_phone']]['called_clock']	 = 0;
						$topArr[$item['other_cell_phone']]['call_cnt']		 = 0;
						$topArr[$item['other_cell_phone']]['called_cnt']	 = 1;
					}
					$topArr[$item['other_cell_phone']]['tel_cnt']	 = 1;
					$topArr[$item['other_cell_phone']]['tel_clock']	 = $item['use_time'];
					$topArr[$item['other_cell_phone']]['earliest']	 = $item['start_time'];
					$topArr[$item['other_cell_phone']]['latest']	 = $item['start_time'];
				}
			}
		}
		return $topArr;
	}

	//处理top
	private function listTop($userId, $topDetailArr) {
		$top50['desc'] = 'top50通话人统计';
		if (empty($topDetailArr)) {
			$top50['value'] = null;
			return $top50;
		}
		uasort($topDetailArr, function ($x, $y) {
			return ($x['tel_cnt'] < $y['tel_cnt']);
		});
		$topArr = array_slice($topDetailArr, 0, 50, true);
		if ($topArr && is_array($topArr)) {
			$topPhoneArr = array_keys($topArr);
			$addressArr	 = [];
			if ($userId > 0 && !empty($topPhoneArr) && is_array($topPhoneArr)) {
				$oAddressModel	 = new Address_list();
				$addressArr		 = $oAddressModel->getAddressList($userId, $topPhoneArr);
			}
			foreach ($topArr as &$item) {
				$item['call_clock']		 = isset($item['call_clock']) ? $this->changeTimeType($item['call_clock']) : 0;
				$item['called_clock']	 = isset($item['called_clock']) ? $this->changeTimeType($item['called_clock']) : 0;
				$item['tel_clock']		 = isset($item['tel_clock']) ? $this->changeTimeType($item['tel_clock']) : 0;
				$item['name']			 = '';
				if ($addressArr) {
					foreach ($addressArr as $value) {
						if ($item['phone_number'] == $value->phone) {
							$item['name'] = $value->name;
						}
					}
				}
			}
		}
		$top50['value'] = $topArr;
		return $top50;
	}

	//时间由秒转分钟
	private function changeTimeType($seconds) {
		$minute	 = floor($seconds / 60);
		$second	 = floor(($seconds - 60 * $minute) % 60);
		return $minute . ':' . $second;
	}

	//获取手机号码归属地
	private function getMobileHome($mobile) {
		$mobilehome = Http::mobileHome($mobile, 'txt');
		if (empty($mobilehome)) {
			$mobilehome = '未知';
		} else {
			$mobilehome = $mobilehome['province'];
		}
		return $mobilehome;
	}

	//二维数组根据指定字段排序
	private function sortArrByField(&$array, $field, $desc = false) {
		$fieldArr = array();
		foreach ($array as $k => $v) {
			$fieldArr[$k] = $v[$field];
		}
		$sort = $desc == false ? SORT_ASC : SORT_DESC;
		array_multisort($fieldArr, $sort, $array);
	}

	//过滤重复号码
	private function filterRepetition($array) {
		$count	 = count($array);
		$tmpArr	 = array();
		for ($i = 0; $i < $count; $i++) {
			for ($j = $i + 1; $j < $count; $j++) {
				$key	 = strlen($array[$i]['phone_number']) > 11 ? substr($array[$i]['phone_number'], -11) : $array[$i]['phone_number'];
				$str2	 = strlen($array[$j]['phone_number']) > 11 ? substr($array[$j]['phone_number'], -11) : $array[$j]['phone_number'];
				if (array_key_exists($key, $tmpArr)) {
					if ($key == $str2) {
						$tmpArr[$key]['call_clock']		 += $array[$j]['call_clock'];
						$tmpArr[$key]['call_cnt']		 += $array[$j]['call_cnt'];
						$tmpArr[$key]['called_clock']	 += $array[$j]['called_clock'];
						$tmpArr[$key]['called_cnt']		 += $array[$j]['called_cnt'];
						$tmpArr[$key]['tel_clock']		 += $array[$j]['tel_clock'];
						$tmpArr[$key]['tel_cnt']		 += $array[$j]['tel_cnt'];
					}
				} else {
					$tmpArr[$key] = $array[$i];
				}
			}
		}
		return $tmpArr;
	}

	//联系人统计
	private function getFavorite($userId, $topDetailArr = array()) {
		$favorite = Favorite_contacts::find()->where(['user_id' => $userId])->asArray()->one();
		if ($favorite) {
			if ($favorite['mobile']) {
				$favorite['contacts_mobile_home'] = $this->getMobileHome($favorite['mobile']);
			}
			if ($favorite['phone']) {
				$favorite['relatives_mobile_home'] = $this->getMobileHome($favorite['phone']);
			}
			$favoriteArr = $this->getFavoriteDetails($favorite, $topDetailArr);
			return $favoriteArr;
		}
		return null;
	}

	//联系人统计通话详情
	private function getFavoriteDetails($favoriteArr, $topArr) {
		$favoriteArr['relatives_earliest']		 = '';
		$favoriteArr['relatives_latest']		 = '';
		$favoriteArr['relatives_call_count']	 = 0;
		$favoriteArr['relatives_call_length']	 = 0;
		$favoriteArr['contacts_earliest']		 = '';
		$favoriteArr['contacts_latest']			 = '';
		$favoriteArr['contacts_call_count']		 = 0;
		$favoriteArr['contacts_call_length']	 = 0;
		if (!empty($favoriteArr['phone']) && !empty($topArr[$favoriteArr['phone']])) {
			$favoriteArr['relatives_earliest']		 = $topArr[$favoriteArr['phone']]['earliest'];
			$favoriteArr['relatives_latest']		 = $topArr[$favoriteArr['phone']]['latest'];
			$favoriteArr['relatives_call_count']	 = $topArr[$favoriteArr['phone']]['tel_cnt'];
			$favoriteArr['relatives_call_length']	 = $this->changeTimeType($topArr[$favoriteArr['phone']]['tel_clock']);
		}
		if (!empty($favoriteArr['mobile']) && !empty($topArr[$favoriteArr['mobile']])) {
			$favoriteArr['contacts_earliest']	 = $topArr[$favoriteArr['mobile']]['earliest'];
			$favoriteArr['contacts_latest']		 = $topArr[$favoriteArr['mobile']]['latest'];
			$favoriteArr['contacts_call_count']	 = $topArr[$favoriteArr['mobile']]['tel_cnt'];
			$favoriteArr['contacts_call_length'] = $this->changeTimeType($topArr[$favoriteArr['mobile']]['tel_clock']);
		}
		return $favoriteArr;
	}

}

?>