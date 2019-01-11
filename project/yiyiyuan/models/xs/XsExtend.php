<?php

namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "{{%extend}}".
 *
 * @property string $id
 * @property string $basic_id
 * @property string $company_name
 * @property string $company_industry
 * @property string $company_position
 * @property string $company_phone
 * @property string $company_address
 * @property string $school_name
 * @property string $school_time
 * @property string $edu
 * @property string $create_time
 */
class XsExtend extends \app\models\xs\XsBaseNewModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%extend}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['basic_id'], 'integer'],
            [['basic_id', 'create_time'], 'required'],
            [['create_time'], 'safe'],
            [['company_name', 'company_address'], 'string', 'max' => 100],
            [['company_industry'], 'string', 'max' => 255],
            [['company_position', 'company_phone', 'school_time', 'edu'], 'string', 'max' => 20],
            [['school_name'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'basic_id' => '请求表id',
            'company_name' => '公司名称',
            'company_industry' => '所属行业',
            'company_position' => '公司职位',
            'company_phone' => '公司电话',
            'company_address' => '公司地址',
            'school_name' => '学校名称',
            'school_time' => '入学时间',
            'edu' => '学历',
            'create_time' => '创建时间',
        ];
    }

    public function saveData($data){
        $time = date("Y-m-d H:i:s");
		$postData = [
            'basic_id'          =>   $data['basic_id'],
            'company_name'      =>   $data['company_name'],
            'company_industry'  =>   $data['company_industry'],
            'company_position'  =>   $data['company_position'],
            'company_phone'     =>   $data['company_phone'],
            'company_address'   =>   $data['company_address'],
            'school_name'       =>   $data['school_name'],
            'school_time'       =>   $data['school_time'],
            'edu'               =>   $data['edu'],
			'create_time' => $time,
		];

		$error = $this->chkAttributes($postData);
		if ($error) {
			return false;
		}

		return $this->save();
    }
}
