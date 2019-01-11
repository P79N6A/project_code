<?php

namespace app\models;

use Yii;
use app\common\Func;
/**
 * 身份限制请求日志,同一身份证每日限验5次
 * 
 * @property integer $id
 * @property integer $aid
 * @property string $name
 * @property string $idcard
 * @property string $callbackurl
 * @property string $trade_no
 * @property string $partner_trade_no
 * @property string $url
 * @property integer $status
 * @property string $create_time
 */
class IdcardLog extends BaseModel
{
	// 支付状态
	const STATUS_INIT = 0;// 初始
	const STATUS_ING = 1;// 处理中
	const STATUS_OK = 2; // 成功
	const STATUS_FAIL = 11;// 失败
	
     /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%idcard_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'name', 'idcard', 'partner_trade_no', 'create_time'], 'required'],
            [['aid', 'status'], 'integer'],
            [['create_time'], 'safe'],
            [['name', 'idcard'], 'string', 'max' => 20],
            [['callbackurl', 'url'], 'string', 'max' => 100],
            [['trade_no', 'partner_trade_no'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => 'APPID',
            'name' => '真实姓名',
            'idcard' => '身份证号',
            'callbackurl' => '回调地址',
            'trade_no' => '服务器交易号',
            'partner_trade_no' => '客户交易号',
            'url' => 'json格式',
            'status' => '状态:0:初始; 1:采集中; 2:验证成功; 11:验证失败',
            'create_time' => '创建时间',
        ];
    }
    /** 
    * 每日同一身份证三次查询 
    * @param string $idcard 
    * @return bool 
    */ 
   public function chkQueryNum($idcard){ 
       if(!$idcard){ 
           return false; 
       } 
       $today = date('Y-m-d'); 
       $total = self::find()-> where(['idcard'=>$idcard])  
                                     -> andWhere(['>=','create_time',$today]) 
                                     -> count(); 
        
       // 每日 限定为3次 
		if( YII_ENV_DEV ){ 
		    $limit = 5; 
		}else{ 
		    $limit = 5; 
		} 
        
       return $total < $limit; 
   } 
	/**
	 * 保存到数据库中
	 */
	public function savaData($logData){
		if( !is_array($logData) ){
			return false;
		}
		$logData['create_time'] = date("Y-m-d H:i:s");
		$error = $this->chkAttributes($logData);
		if($error){
			return $this->returnError(false, implode("|", $error));
		}
		return $this->save();
	}
	/**
	 * 纪录错误日志
	 * 按月分组
	 */
	public function saveJson( $idcard , $content ){
		$path = '/ofiles/idcard/' . date('Ym/d/') . $idcard . '.json';
		$filePath = Yii::$app->basePath . '/web'  . $path;
		Func::makedir(dirname($filePath));
		file_put_contents($filePath, $content);
		return $path;
	}
	/**
	 * 根据编号获取纪录
	 * @param $partner_trade_no
	 * @return object
	 */
	public static function getByNo($partner_trade_no){
		if(!$partner_trade_no){
			return null;
		}
		return static::find() -> where(["partner_trade_no"=>$partner_trade_no]) ->one();
	}
	/**
	 * 获取同姓名,身份证的信息
	 * @param array $postData
	 * @return object ar
	 */
	public function getIdcard($name, $idcard){
		if( !$name || !$idcard ){
			return null;
		}
		
		$where = ['name'=>$name, 'idcard'=>$idcard];
		return static::find() -> where($where) -> one();
	}
}
