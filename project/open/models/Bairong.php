<?php

namespace app\models;
use app\common\Func;
use Yii;
use app\common\Logger;
/**
 * This is the model class for table "{{%bairong}}".
 *
 * @property integer $id
 * @property string $request_id
 * @property integer $aid
 * @property string $name
 * @property string $idcard
 * @property string $phone
 * @property string $rsp_code
 * @property string $rsp_status_text
 * @property integer $status
 * @property string $url
 * @property string $ip
 * @property string $create_time
 * @property string $modify_time
 */
class Bairong extends \app\models\BaseModel {
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return '{{dc_bairong}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['aid', 'name', 'idcard', 'phone', 'card', 'apis','create_time', 'modify_time'], 'required'],
			[['aid', 'status'], 'integer'],
			[['create_time', 'modify_time'], 'safe'],
			[['name', 'rsp_code'], 'string', 'max' => 30],
			[['idcard', 'phone'], 'string', 'max' => 20],
			[['card'], 'string', 'max' => 64],
			[['rsp_status_text','apis'], 'string', 'max' => 255],
			[['url'], 'string', 'max' => 200],
			[['ip','swift_number'], 'string', 'max' => 100],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => '主键',
			'aid' => '应用id',
			'name' => '姓名',
			'idcard' => '身份证',
			'phone' => '手机号',
			'rsp_code' => '响应:状态码',
			'rsp_status_text' => '响应:原因',
			'status' => '状态:0:初始; 2:成功; 11:失败',
			'url' => '保存路径',
			'ip' => 'ip',
			'swift_number' => '百融的序列号',
			'create_time' => '创建时间',
			'modify_time' => '最后修改时间',
		];
	}
	/**
	 * 根据手机号批量获取
	 * @param [] $phones 手机号
	 * @return []
	 */
	public function getByPhones($phones,$apis) {
		if (!$phones) {
			return null;
		}
		$where = [
			'AND',
			['phone' => (string)$phones],
			['>','create_time', date('Y-m-d H:i:s', strtotime('-3 month') )],
			['apis' => $apis]
		];
		Logger::dayLog('br', 'where', $where);
		$data = static::find()->where($where)->limit(1000)->all();
		return $data;
	}


	/**
	 * 批量保存数据
	 * @param [] $map
	 * @return []
	 */
	public function saveDatas($post_map) {
		$br_models = [];
		foreach ($post_map as $phone => $data) {
			$m = new Bairong;
			$result = $m->saveData($data);
			if ($result) {
				$br_models[$phone] = $m;
			}
		}
		return $br_models;
	}
	/**
	 * 保存数据
	 * @param [] $data
	 * @return []
	 */
	public function saveData($data) {
		if (!is_array($data) || empty($data)) {
			return false;
		}
		//保存数据
		$time = date("Y-m-d H:i:s");
		$sdata = [
			'aid' => $data['aid'],
			'name' => $data['name'],
			'idcard' => $data['idcard'],
			'phone' => $data['phone'],
			'card' => isset($data['card']) ? $data['card'] : '',
			'apis' => $data['apis'],
			'rsp_code' => '',
			'rsp_status_text' => '',
			'status' => 0,
			'url' => '',
			'ip' => isset($data['ip']) ? $data['ip'] : '',
			'swift_number' => '',
			'create_time' => $time,
			'modify_time' => $time,
		];
		$errors = $this->chkAttributes($sdata);
		if ($errors) {
			return false;
		}

		return $this->save();
	}
	/**
	 * 保存响应结果
	 * @param  [] $res
	 * @return bool
	 */
	public function saveRspStatus(&$res) {

		if (isset($res['code']) && ($res['code'] == '00' || $res['code'] == '600000')) {
			$id = $this->id;
			$content = json_encode($res, JSON_UNESCAPED_UNICODE);
			$this->url = $this->saveJson($id, $content);
			$this->swift_number = isset($res['swift_number']) ? $res['swift_number'] : '';
			$this->status = 2;
		} else {
			$this->status = 11;
		}
		$this->rsp_code = isset($res['code']) ? $res['code'] : '';
		$this->modify_time = date('Y-m-d H:i:s');
		return $this->save();
	}
	public function saveFailStatus() {
            $this->status = 11;
		$this->rsp_code = '_NOTFOUND';
		$this->rsp_status_text = '无响结果';
		$this->modify_time = date('Y-m-d H:i:s');
		$this->save();
	}
	/**
	 * 按月分组
	 */
	public function saveJson($id, $content) {
		$path = '/ofiles/bairong/' . date('Ym/d/') . $id . '.json';
		$filePath = Yii::$app->basePath . '/web' . $path;
		Func::makedir(dirname($filePath));
		file_put_contents($filePath, $content);
		return $path;
	}
}
