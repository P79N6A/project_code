<?php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;
use app\common\RSALocal;
use app\common\Crypt3Des;
use Yii;

class ImgFileSave extends \app\models\BaseModel
{
	public $project;
	public $type; // 子目录
	public $uid;
	//private $oRsa;
	public function init(){
		parent::init();
		//$this->oRsa = new RSALocal();
	}
    /**
     * @var UploadedFile
     */
    public $imageFile;

    public function rules()
    {
        return [
            //[['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png2'],
        ];
    }
    
    /*public function upload()
    {
        if ($this->validate()) {
            $this->imageFile->saveAs('uploads/' . $this->imageFile->baseName . '.' . $this->imageFile->extension);
            return true;
        } else {
            return false;
        }
    }*/
	
	/**
	 * 加密链接地址
	 */
	public function encryptKey($user_id, $type){
		$jsonStr = json_encode([
			'project'=>'yiyiyuan',
			't'=>time(),
			'type'=>$type,
			'uid'=>$user_id,
		]);
		//$str = $this->oRsa -> encryptByPublic($jsonStr);
		$str = Crypt3Des::encrypt($jsonStr, Yii::$app->params["img_key"]);
		
		return $str;
	}
	/**
	 * 加密链接地址
	 */
	public function decryptKey($encrypt){
		//1 解密操作
		if(!$encrypt){
			return $this->returnError(null, "密钥为空");
		}
		//$jsonStr = $this->oRsa -> decryptByPrivate($encrypt);
		$jsonStr = Crypt3Des::decrypt($encrypt, Yii::$app->params["img_key"]);
		if(!$jsonStr){
			\app\common\Logger::dayLog("decryptKey", "密钥解析失败",$encrypt);
			return $this->returnError(null, "密钥解析失败");
		}
		$data = json_decode($jsonStr,true);
		
		//2 检测数据是否合法
		if(empty($data) || !is_array($data) ){
			\app\common\Logger::dayLog("decryptKey", "密钥不正确",$encrypt,$jsonStr,$data);
			return $this->returnError(null, "密钥不正确");
		}
		if(	!isset($data['t']) || 
			!isset($data['uid']) ||
			!isset($data['project']) ||
			!isset($data['type'])
			){
			\app\common\Logger::dayLog("decryptKey", "密钥内容不全",$data);
			return $this->returnError(null, "密钥内容不全");
		}

		//3 检测超时与否 30分钟
		if( time() - $data['t'] > 1800 ){
			return $this->returnError(null, "密钥失效");
		}
		if(!preg_match("/[a-z]/i", $data['project'])){
			return $this->returnError(null, "project只能是小写字母");
		}
		if(!preg_match("/[a-z]/i", $data['type'])){
			return $this->returnError(null, "type只能是小写字母");
		}
		
		$this->project = $data['project'];
		$this->type = $data['type'];
		$this->uid = $data['uid'];
		
		//4 返回结果
		return true;
	}

	public function getImgPath(){
		return  '/' . $this -> project . '/' . $this -> type;
	}
	/**
	 * @desc 图片上传解密url
	 */
	public function decryptUrl($encrypt){
		//1 解密操作
		if(!$encrypt){
			return $this->returnError(null, "待解密数据为空");
		}
		$jsonStr = Crypt3Des::decrypt($encrypt, "013456GJLNVXZbdhijkmnprz");
		if(!$jsonStr){
			\app\common\Logger::dayLog("decryptUrl", "密钥解析失败",$encrypt);
			return $this->returnError(null, "密钥解析失败");
		}
		$data = json_decode($jsonStr,true);
		
		//2 检测数据是否合法
		if(empty($data) || !is_array($data) ){
			\app\common\Logger::dayLog("decryptUrl", "密钥不正确",$encrypt,$jsonStr,$data);
			return $this->returnError(null, "密钥不正确");
		}		
		//3 返回结果
		return $data;
	}
}