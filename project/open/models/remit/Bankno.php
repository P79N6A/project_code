<?php

namespace app\models\remit;

use Yii;
use app\modules\api\common\remit\RemitApi;
/**
 * This is the model class for table "rt_bank".
 *
 * @property integer $id
 * @property string $bankname
 * @property string $branch_no
 * @property string $branch_name
 * @property string $create_time
 */
class Bankno extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rt_bankno';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['branch_name', 'branch_no', 'create_time'], 'required'],
            [['create_time'], 'safe'],
            [['branch_no'], 'string', 'max' => 100],
            [['branch_name'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'branch_name' => '联行名称;例如总行,**支行',
            'branch_no' => '联行编号:与联行名称对应',
            'create_time' => '请求开始时间',
        ];
    }
	/**
	 * 根据名称获取联号
	 * @param $name
	 * @return 
	 */
	public function getNoByName($branch_name){
		//1 分支名称为空时直接返回
		if( !$branch_name ){
			return '';
		}
		
		//2 查询数据
		$branch_no = static::find() ->select('branch_no') -> where(['branch_name'=>$branch_name]) -> scalar();
		if($branch_no){
			return $branch_no;
		}
		$branch_no = static::find() ->select('branch_no') -> where(['like','branch_name',$branch_name]) -> scalar();
		if($branch_no){
			return $branch_no;
		}
		
		return '';
		
		//3 重新导入再次查询
		$nums = $this->importBanks();
		if( !$nums ){
			return '';
		}
		
		//4 重试一次查询
		$branch_no = static::find() ->select('branch_no') -> where(['branch_name'=>$branch_name]) -> scalar();
		return $branch_no;
	}
	/**
	 * 获取银行数据
	 */
	public function importBanks(){
		//1 从接口中获取数据
		$env = YII_ENV_DEV ? 'dev' : 'prod';
		$oRemit = new RemitApi($env);
		$res = $oRemit -> bankno();
		if($res['status'] != 200){
			return 0;
		}
	
		//2 批量导入
		$maxid = static::find() -> select(["max(id) as id"]) -> scalar();
		$nums = $this ->xmlTodb($res['data']);
		if(!$nums){
			return 0;
		}

		//3 删除历史数据
		if($maxid){
			static::deleteAll(['<=','id',$maxid]);
		}
		return $nums;
	}
	/**
	 * 批量导入 xml的数组格式导入到db中
	 */
	public function xmlTodb($xmlArr){
		$data = \yii\helpers\ArrayHelper::getValue($xmlArr, 'list.row');
		$dayTime = date('Y-m-d H:i:s');
		$newData = [];
		foreach($data as $v){
			$newData[] = [
				'branch_name' => $v['branchName'],
				'branch_no' => $v['branchNo'],
				'create_time' => $dayTime,
			];
		}

		$nums = $this ->insertBatch($newData);
		return $nums;
	}
}
