<?php
/**
 * 只纪录正确的身份证信息
 */
namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%idcard}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $idcard
 * @property integer $create_time
 */
class Idcard extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%idcard}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'idcard'], 'required'],
            [['create_time'], 'safe'],
            [['name', 'idcard'], 'string', 'max' => 20],
            [['url','image'], 'string', 'max' => 100],
            [['idcard'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '真实姓名',
            'idcard' => '身份证号',
            'url' => '详细资料json格式',
            'create_time' => '创建时间',
        ];
    }
	public function existsIdcard($idcard){
		if(!$idcard){
			return FALSE;
		}
		return self::find() ->where(['idcard'=>$idcard])->count();
	}
	/**
	 * 根据身份证查询
	 * @param array $postData
	 * @return object ar
	 */
	public static function getByIdcard($idcard){
		if( !$idcard ){
			return null;
		}
		
		return static::find() -> where(['idcard'=>$idcard]) -> one();
	}

	/**
	 * 保存到数据库中
	 */
	public function saveData($name, $idcard, $url, $image=''){
		$data = [
			'name' => $name,
			'idcard' => $idcard,
			'url' => $url,
			'image' => $image,
			'create_time' => date('Y-m-d H:i:s'),
		];
		$error = $this->chkAttributes($data);
		if($error){
			return $this->returnError(false, implode("|", $error));
		}
		return $this->save();
	}
	/**
	 * 纪录错误日志
	 * 按月分组
	 */
	public function saveImage( $idcard , $base64 ){
		//1 相对路径与绝对路径
		$path = '/oimages/idcard/' . date('Ym/d/') . $idcard . '.png';
		$filePath = Yii::$app->basePath . '/web'  . $path;
		
		//2 判断图片是否正确 // 若是含data,则将其去除
		$tmp = [];
		if(preg_match('/data:\s*image\/(\w+);base64,/iu',$base64,$tmp)){
			$base64 = str_replace(' ','+',$base64);
			$base64 = str_replace($tmp[0], '', $base64);
		}
		
		//3 保存图片
		\app\common\Func::makedir(dirname($filePath));
		$img = base64_decode($base64); // 转换成二进制的形式
		if(!file_put_contents($filePath, $img)){
			return '';
		}
		return $path;
	}
	
}
