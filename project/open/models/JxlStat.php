<?php

namespace app\models;

use app\common\Func;
use app\common\RSALocal;
use Yii;

/**
 * This is the model class for table "jxl_stat".
 *
 * @property integer $id
 * @property integer $aid
 * @property integer $requestid
 * @property string $name
 * @property string $phone
 * @property string $website
 * @property string $create_time
 * @property string $url
 */
class JxlStat extends BaseModel {
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'jxl_stat';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['aid', 'requestid', 'is_valid','source'], 'integer'],
			[['phone', 'create_time', 'url'], 'required'],
			[['create_time'], 'safe'],
			[['name', 'website'], 'string', 'max' => 50],
			[['phone', 'idcard'], 'string', 'max' => 20],
			[['url'], 'string', 'max' => 100],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'aid' => '应用id',
			'requestid' => '请求id',
			'name' => '姓名',
			'phone' => '手机号',
			'website' => '网站英文名称',
			'create_time' => '创建时间',
			'url' => '统计JSON存储地址',
			'source' => '来源'
		];
	}
	/**
	 * 保存数据
	 */
	public function saveStat($postData) {
		// 检测数据
		if (!$postData) {
			return $this->returnError(false, '不能为空');
		}
		$time = date('Y-m-d H:i:s');
		$data = [
			'aid' => isset($postData['aid']) ? $postData['aid'] : 0,
			'requestid' => isset($postData['requestid']) ? $postData['requestid'] : 0,
			'name' => isset($postData['name']) ? $postData['name'] : '',
			'idcard' => isset($postData['idcard']) ? $postData['idcard'] : '',
			'phone' => $postData['phone'], // 必填
			'website' => isset($postData['website']) ? $postData['website'] : '',
			'create_time' => $time,
			'url' => $postData['url'], // 必填
			'source' => isset($postData['source']) ? $postData['source'] : 1,
		];

		$error = $this->chkAttributes($data);
		if ($error) {
			return $this->returnError(false, $error);
		}

		return $this->save();
	}
	/**
	 * 1小时是否存在重复纪录
	 */
	public function getHour($phone) {
		$oM = static::find()->where(['phone' => $phone])
			->andWhere(['>=', 'create_time', date('Y-m-d 00:00:00', time() - 3600)])
			->orderBy('create_time DESC')
			->limit(1)
			->one();
		return $oM;
	}
	/**
	 * 三个月的限制
	 */
	public function getHistory($phone, $website) {
		//1 限制时间
		$limitTime = 86400 * 120;
		$t = time() - $limitTime;

		//2 三个月内是否存在数据
		if ($website) {
			$where = ['website' => $website];
		} else {
			$where = ['not in', 'website', ['jingdong']];
		}
		$d = date('Y-m-d 00:00:00', $t);
		$data = static::find()->where(['phone' => $phone])
			->andWhere($where)
			->andWhere(['>=', 'create_time', $d])
			->orderBy('create_time DESC')->limit(1)->one();

		if ($data) {
			return [
				'requestid' => $data['requestid'],
				'phone' => $data['phone'],
			];
		}

		//3 搜索请求表request中10008三个月内是否采集数据
		$data = JxlRequestModel::find()->where(['phone' => $phone])
			->andWhere($where)
			->andWhere(['process_code' => '10008'])
			->andWhere(['>', 'create_time', $t])
			->orderBy('id DESC')
			->limit(1)
			->one();
		if ($data) {
			return [
				'requestid' => $data['id'],
				'phone' => $data['phone'],
			];
		}

		return false;
	}

	/**融360新增
	 * 融三个月的限制
	 */
	public function getHistoryNew($phone,$website = '', $aid = 1) {
		//1 限制时间
		$limitTime = 86400 * 120;
		$t = time() - $limitTime;

		//2 三个月内是否存在数据
		if ($website) {
			$where = ['website' => $website];
		} else {
			$where = ['not in', 'website', ['jingdong']];
		}
		$d = date('Y-m-d 00:00:00', $t);
		$data = static::find()->where(['phone' => $phone])
				->andWhere($where)
				->andWhere(['>=', 'create_time', $d])
				->andWhere(['aid' => $aid])
				->orderBy('create_time DESC')->limit(1)->one();

		if ($data) {
			return [
					'requestid' => $data['requestid'],
					'phone' => $data['phone'],
					'source' => $data['source'],
			];
		}

		return false;
	}
	/**
	 * 获取某次请求的通话纪录
	 * @param $phone 手机号
	 */
	public function getByPhone($phone) {
		// 默认最新一百条
		$data = static::find()->where(['phone' => $phone])
			->orderBy("create_time DESC")
			->limit(1)
			->one();
		return $data;
	}
	/**
	 * 获取某次请求的通话纪录
	 * @param $phone 手机号
	 */
	public function getByRequestid($requestid) {
		// 默认最新一百条
		return static::find()->where(['requestid' => $requestid])
			->orderBy("create_time DESC")
			->limit(1)
			->one();
	}
	/**
	 * 加密链接地址
	 */
	public function encryptUrl($id, $type) {
		$jsonStr = json_encode(['id' => $id, 'type' => $type]);
		$oRsa = new RSALocal();
		$str = $oRsa->encryptByPublic($jsonStr);
		return $str;
	}
	/**
	 * 加密链接地址
	 */
	public function decryptUrl($str) {
		if (!$str) {
			return null;
		}
		$oRsa = new RSALocal();
		$jsonStr = $oRsa->decryptByPrivate($str);
		if (!$jsonStr) {
			return null;
		}
		$data = json_decode($jsonStr, true);
		if (empty($data)) {
			return null;
		}

		// 查询数据库返回
		$id = $data['id'];
		$oStat = static::findOne($id);
		if (!$oStat) {
			return null;
		}

		// 获取url
		$url = $oStat['url'];
		$path = Yii::$app->basePath . '/web' . $url;
		if (!file_exists($path)) {
			return null;
		}
		return ['id' => $id, 'url' => $url, 'path' => $path, 'type' => $data['type']];
	}
	/**
	 * 纪录错误日志
	 * 按月分组
	 */
	public function saveJson($phone, $content) {
		$path = '/ofiles/jxl/' . date('Ym/d/') . $phone . '.json';
		$filePath = Yii::$app->basePath . '/web' . $path;
		Func::makedir(dirname($filePath));
		file_put_contents($filePath, $content);
		return $path;
	}
	/**
	 * 纪录错误日志
	 * 按月分组
	 */
	public function saveDetail($path, &$content) {
		$filePath = Yii::$app->basePath . '/web' . $path;
		Func::makedir(dirname($filePath));
		file_put_contents($filePath, $content);
		return $path;
	}
	/**
	 * 根据phone获取通话的下载地址
	 * @param str $phone
	 * @param str $website
	 * @return []
	 */
	public function getDataByPhone($phone, $website = '') {
		// 查询手机号通讯纪录
		if (!$phone) {
			return null;
		}
		if ($website) {
			$where = ['website' => $website];
		} else {
			$where = ['not in', 'website', ['jingdong']];
		}
		$data = static::find()->where(['phone' => $phone])
			->andWhere($where)
			->orderBy('create_time DESC')
			->limit(1)
			->asArray()
			->one();
		if (!$data) {
			return null;
		}

		// 域名获取
		$domain = $this->getDomain($data['create_time']);

		// 获取文件位置
		$data['f_stat'] = $domain . $data['url'];
		$data['f_detail'] = str_replace(".json", "_detail.json", $data['f_stat']);
		return $data;
	}

	/**
	 * 根据phone获取所有通话的下载地址
	 * @param str $phone
	 * @param str $website
	 * @return []
	 */
	public function getDataByPhoneAll($phone, $website = '',$appId='0') {
		// 查询手机号通讯纪录
		if (!$phone) {
			return null;
		}

		$result = self::find();

		if ($website) {
			$result->andWhere(['website' => $website]);
//			$where = ['website' => $website];
		} else {
			$result->andWhere(['not in', 'website', ['jingdong']]);
//			$where = ['not in', 'website', ['jingdong']];
		}
		if($appId != 0){
			$result->andWhere(['aid' => $appId]);
//			$where[]['aid']=$appId;
		}
		$result->andWhere(['phone' => $phone]);
		$data = $result
				->orderBy('create_time DESC')
				->asArray()
				->all();
//		$data = static::find()->where(['phone' => $phone])
//				->andWhere($where)
//				->orderBy('create_time DESC')
////				->limit(1)
//				->asArray()
//				->all();
		if (!$data) {
			return null;
		}

		// 域名获取
		$res = [];
		foreach($data as $k=>$v){
			// 获取文件位置
			$domain = $this->getDomain($v['create_time']);
			$data[$k]['f_stat'] = $domain . $v['url'];
			$data[$k]['f_detail'] = str_replace(".json", "_detail.json", $data[$k]['f_stat']);
			$res[] = json_encode($data[$k]);
		}
		return $res;
	}
	/**
	 * 获取域名
	 * @param  datetime $create_time 时间格式
	 * @return str              域名
	 */
	private function getDomain($create_time) {
		$create_time = strtotime($create_time);
		if (time() - $create_time > 86400 * 2) {
			//$domain = "http://124.193.149.180:8100";
			//$domain = "http://123.207.141.180";
			$domain = "http://10.139.36.194";
		} else {
			$domain = "http://open.xianhuahua.com";
		}
		return $domain;
	}
}
