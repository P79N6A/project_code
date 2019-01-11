<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "yi_user_images".
 *
 * @property string $id
 * @property string $user_id
 * @property string $title
 * @property string $img
 * @property integer $status
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class UserImages extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_images';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['img', 'status'], 'required'],
            [['id', 'user_id', 'status', 'version'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['title'], 'string', 'max' => 64],
            [['img'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', '会员id'),
            'title' => Yii::t('app', '标题'),
            'img' => Yii::t('app', '链接地址'),
            'status' => Yii::t('app', '状态'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_modify_time' => Yii::t('app', '修改时间'),
            'version' => Yii::t('app', 'Version'),
        ];
    }
	/**
	 * 获取某会员的图片
	 */
	public function getImagesByUserId($user_id){
		$user_id = intval($user_id);
		if( !$user_id ){
			return FALSE;
		}
		
		return static::find() -> where(['user_id'=>$user_id])->orderBy('id ASC')->all();
	}
	/**
	 * 批量保存图片
	 */
	public function saveImages($images){
		//1 参数验证
		/*$user_id = intval($user_id);
		if( !$user_id ){
			return FALSE;
		}*/
		if( !is_array($images) || empty($images) ){
			return FALSE;
		}
		
		//2 保存数据
		$transaction = Yii::$app->db->beginTransaction();
		$ret = true;
		$nowTime = date("Y-m-d H:i:s");
		foreach($images as $r ){
			$id = $r['id'];
			if( $id ){
				$upModel = static::findOne($id);
				$upModel -> img = $r['img'];
				$upModel -> last_modify_time = $nowTime;
				$result = $upModel -> save();
			}else{
				$data = [
					'title'  => '',
					'user_id'=> $r['user_id'],
					'status' => 1,
					'img' => $r['img'],
					'create_time' => $nowTime,
					'last_modify_time' => $nowTime,
				];
				$o = new self();
				$o ->attributes = $data;
				$result = $o -> save();
			}
			
			if( !$result ){
				break;
			}
		}
		
		//3 返回结果
		if( $result ){
			$transaction -> commit();
			return true;
		}else{
			$transaction -> rollBack();
			return FALSE;
		}
	}
}
