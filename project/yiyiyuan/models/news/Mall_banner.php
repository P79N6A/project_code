<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_mall_banner".
 *
 * @property string $id
 * @property string $title
 * @property integer $admin_id
 * @property integer $type
 * @property integer $category
 * @property integer $ads_position
 * @property integer $product_position
 * @property string $banner_pic_url
 * @property string $pic_url
 * @property string $click_url
 * @property integer $status
 * @property string $create_time
 * @property string $last_modify_time
 * @property string $version
 */
class Mall_banner extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_mall_banner';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'admin_id', 'type', 'last_modify_time'], 'required'],
            [['admin_id', 'type', 'category', 'ads_position', 'product_position', 'status', 'version'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['title'], 'string', 'max' => 128],
            [['desc','banner_pic_url', 'pic_url', 'click_url'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'desc' => 'Desc',
            'admin_id' => 'Admin ID',
            'type' => 'Type',
            'category' => 'Category',
            'ads_position' => 'Ads Position',
            'product_position' => 'Product Position',
            'banner_pic_url' => 'Banner Pic Url',
            'pic_url' => 'Pic Url',
            'click_url' => 'Click Url',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }
    /**
     * 封装规则检查
     */
    public function chkAttributes($postData) {
        $this->attributes = $postData;

        // 当提交无错误时
        if ($this->validate()) {
            return null;
        }

        // 有错误时,只取第一个错误就ok了
        $errors = [];
        foreach ($this->errors as $attribute => $es) {
            $errors[$attribute] = $es[0];
        }
        return $errors;
    }
    /*添加banner
 * */
    public function save_address($condition) {
        if(!$condition || !is_array($condition)){
            return false;
        }
        $condition['create_time'] = date('Y-m-d H:i:s');
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return $error;
        }
        return $this->save();
    }

    /**
     * 更新一条数据
     * @param $condition
     * @return bool
     */
    public function update_list($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return $error;
        }
        return $this->save();
    }
}
