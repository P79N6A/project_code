<?php

namespace app\models\news;

use app\commonapi\Apihttp;
use Yii;

/**
 * This is the model class for table "yi_goods_category_list".
 *
 * @property string  $id
 * @property string  $classify_name
 * @property integer $sort_id
 * @property integer $recommend
 * @property string  $create_time
 * @property string  $last_modify_time
 * @property integer $version
 */
class Goods_category_list extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_goods_category_list';
    }

    public function getGoodsList()
    {
        return Goods_list::find()->where(['cid' => $this->id])->limit(5)->all();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['classify_name', 'sort_id'], 'required'],
            [['sort_id','status','category_id','parent_id', 'type','recommend', 'version'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['classify_name','classify_img'], 'string', 'max' => 64],
            [['click_url'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Category Id',
            'parent_id' => 'Parent Id',
            'type' => 'Type',
            'classify_name' => 'Classify Name',
            'classify_img' => 'Classify Img',
            'sort_id' => 'Sort ID',
            'click_url' => 'Click Url',
            'recommend' => 'Recommend',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock()
    {
        return "version";
    }

    /**
     * 获取全部产品分类
     * @param int $limit
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAllCategory($limit = 5)
    {
        $where = [
            'parent_id' => 0,
            'status' => 1
        ];
        return self::find()->where($where)->limit($limit)->orderBy('sort_id asc')->all();
    }

    public function listCategory($parent_id = 0,$status = 1,$type = 1,$recommend = 0,$limit = 5){
        $where = [
            'parent_id' => $parent_id,
            'status' => $status,
            'type' =>$type,
            'recommend' => $recommend
        ];
        return self::find()->where($where)->limit($limit)->orderBy('sort_id asc')->all();
    }

    /**
     * 获取推荐产品分类
     * @param int $limit
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getTjCategory($limit = 2)
    {
        $where = [
            'parent_id' => 0,
            'type' => 1,
            'recommend' => 1
        ];
        return self::find()->where($where)->limit($limit)->orderBy('sort_id asc')->all();
    }

    public function getNameById($id)
    {
        $id = intval($id);
        if (!$id) {
            return '';
        }
        $data = self::findOne($id);
        return $data->classify_name;
    }

    public function updateSave($condition){
        if(!$condition || !is_array($condition)){
            return false;
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return $error;
        }
        return $this->save();
    }

    public function addSave($condition){
        if(!$condition || !is_array($condition)){
            return false;
        }
        $condition['category_id'] = time();
        $condition['parent_id'] = 0;
        $condition['recommend'] = 0;
        $condition['status'] = 2;//下架
        $condition['type'] = 1;//亿元链接
        $condition['sort_id'] = 1;
        $condition['create_time'] = date('Y-m-d H:i:s');
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return $error;
        }
        return $this->save();
    }
    /**
     * 亿元同步商城分类
     * @param $category_id 同步id $type:1 增加或修改 $type=2 删除 $type=3 同步排序
     * @return boolean true:
     */
    public function getSyncCategory($data,$category_id,$type=1) {
        $apiHttp = new Apihttp();
        $data_str=json_encode($data);
        $payResult = $apiHttp->getSendCategory(['data' =>$data_str, 'category_id' => $category_id,'type' => $type]);
        if ($payResult['rsp_code'] == '0000' || empty($payResult)) {
            return true;
        }
        return false;
    }
}
