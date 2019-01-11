<?php

namespace app\models\xs;

use Yii;

/**
 * 基本表
 */
class XsBasic extends \app\models\xs\XsBaseNewModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%basic}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid','gender'],'integer'],
            [['identity_id','event','idcard','phone','name','source', 'create_time'], 'required'],
            [['create_time'],'safe'],
            [['identity_id'],'string','max' => 50],
            [['event','birth','phone','source'], 'string', 'max' => 20],
            [['idcard','province','name','ip'], 'string', 'max' => 30],
            [['device'],'string','max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => '业务应用ID',
            'identity_id' => '业务唯一标识',
            'event' => '事件类型:reg; loan; login;',
            'idcard' => '身份证',
            'birth' => '出生年月',
            'gender' => '性别: 0:女; 1:男',
            'province' => '身份证地区',
            'phone' => '手机号',
            'name' => '姓名',
            'ip' => 'IP地址',
            'device' => '设备号',
            'source' => '来源:weixin,android,ios,web',
            'create_time' => '创建时间',
        ];
    }
    public function saveData($data){
        $time = date("Y-m-d H:i:s");
        $idcard = $this->getValue($data,'idcard');

        $oIC = new XsIdCard;
        $idInfo = $oIC -> get($idcard);
        if (!$idInfo) {
            return false;
        }

        $birth = $this->getValue($idInfo,'birth');
        $gender = $this->getValue($idInfo,'gender');
        $province = $this->getValue($idInfo,'province');

        $postData = [
            'aid'         => $data['aid'],
            'identity_id' => $data['identity_id'],
            'event'       => $data['event'],
            'idcard'      => $idcard,
            'birth'       => $birth,
            'gender'      => $gender,
            'province'    => $province,
            'phone'       => $this->getValue($data,'phone'),
            'name'        => $this->getValue($data,'name'),
            'ip'          => $this->getValue($data,'ip'),
            'device'      => $this->getValue($data,'device'),
            'source'      => $this->getValue($data,'source'),
            'create_time' => $time,
        ];

        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }

        return $this->save();
    }
    /**
     * 废弃获取基本信息
     */
    // public function getById($basic_id){
    //     $data = static::findOne($basic_id);
    //     if(empty($data)){
    //         return Null;
    //     }
    //     return $data;
    //     $arr = [
    //         'birth' => $data['birth'], 
    //         'answer' => $data['answer'], 
    //         'gender' => $data['gender'], 
    //         'province' => $data['province'], 
    //         'phone' => $data['phone'], 
    //     ];
    //     return $data;
    //}
}
