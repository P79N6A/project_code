<?php

namespace app\models\tidb;

use Yii;

use app\common\Logger;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "label_list".
 *
 * @property string $id
 * @property string $phone
 * @property string $source
 * @property string $tag_type
 * @property string $modify_time
 * @property string $create_time
 * @property string $other_info
 */
class TiPhoneTagList extends TiBaseModel
{

    const PINGAN_SOURCE = 18; //凭安source码
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'phone_tag_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'source', 'tag_type', 'modify_time', 'create_time'], 'required'],
            [['source'], 'integer'],
            [['modify_time', 'create_time'], 'safe'],
            [['other_info'], 'string'],
            [['phone'], 'string', 'max' => 20],
            [['tag_type'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => '用户手机号',
            'source' => '标签来源：1 聚信立；2 数据魔盒； 3 上数',
            'tag_type' => '电话标签',
            'modify_time' => '最近修改时间',
            'create_time' => '添加时间',
            'other_info' => '手机号其他信息',
        ];
    }

    public function getExistTag($phone_list)
    {
        if (empty($phone_list)) {
            return false;
        }
        $where = ['in','phone',$phone_list];
        $res = static::find()->select('phone,tag_type')->where($where)->asArray()->all();
        return $res;
    }

    public function saveData($data)
    {
        $time = date("Y-m-d H:i:s"); 
        $data['create_time'] = $time;
        $data['modify_time'] = $time;
        $error = $this->chkAttributes($data); 
        if ($error) { 
            Logger::dayLog("anti/TiPhoneTagList","save failed", $data, $error);
            return false;
        }
        return $this->save();
    }

    public function getPhoneTag($phone)
    {
        if (empty($phone)) {
            return false;
        }
        $where = ['phone' => $phone];
        $res = static::find()->where($where)->one();
        return $res;
    }

    public function validatePhone($phone){
        // 验证联系电话
        $isMob='/^1[2-9][0-9]\d{8}$/';
         
        $isTel='/^0\d{2,3}-?\d{7,8}$/';

        if(!preg_match($isMob,$phone) && !preg_match($isTel,$phone)){
            return false;
        }
        return true;
    }
}
