<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yx_activity".
 *
 * @property string $id
 * @property integer $type
 * @property integer $status
 * @property string $banner_url
 * @property string $rule_url
 * @property string $title_url
 * @property string $alert_url
 * @property string $prize_url
 * @property string $button
 * @property string $start_date
 * @property string $end_date
 * @property integer $nums_rule_ious
 * @property integer $nums_rule_buy
 * @property integer $nums_rule_online
 * @property integer $admin_depart
 * @property string $admin_user
 * @property integer $admin_id
 * @property integer $admin_status
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class Activitynew extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_activitynew';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type','title','index_input','banner_input','start_input','elastic_layer_rule', 'admin_user', 'admin_id', 'create_time', 'last_modify_time'], 'required'],
            [['type','statistics_user','statistics_pv', 'status', 'index_input', 'banner_input', 'start_input', 'admin_depart', 'admin_id', 'admin_status', 'version'], 'integer'],
            [['start_date', 'end_date', 'create_time', 'last_modify_time'], 'safe'],
            [['banner_url','start_url', 'rule_url', 'title_url', 'alert_url', 'prize_url', 'button'], 'string', 'max' => 64],
            [['admin_user'], 'string', 'max' => 32]
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
            'index_input' => 'Index Input',
            'banner_input' => 'Banner Input',
            'start_input' => 'Start Input',
            'elastic_layer_rule' => 'Elastic Layer Rule',
            'statistics_user'=>'Statistics User',
            'statistics_pv'=>'Statistics Pv',
            'type' => 'Type',
            'status' => 'Status',
            'banner_url' => 'Banner Url',
            'start_url' => 'Start Url',
            'rule_url' => 'Rule Url',
            'title_url' => 'Title Url',
            'alert_url' => 'Alert Url',
            'prize_url' => 'Prize Url',
            'button' => 'Button',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'admin_depart' => 'Admin Depart',
            'admin_user' => 'Admin User',
            'admin_id' => 'Admin ID',
            'admin_status' => 'Admin Status',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function getCondition(){
        return $this->hasOne(ActivityCondition::className(), ['activity_id' => 'id']);
    }

    public function getPrize()
    {
        return $this->hasMany(Prize::className(), ['activity_id' => 'id']);
    }

    public function getPrizeList(){
        return $this->hasMany(PrizeList::className(),['activity_id' => 'id']);
    }

    public static function getTitle($type){
        $data = array();
        switch($type){
            case 1:
                break;
            case 2:
                $data['cn'] = "水果机";
                $data['en'] = "fruit_machine";
                $data['img'] = "/newdev/images/lottery/fruit_machine/11.png";
                break;
            case 3:
                $data['cn'] = "大转盘";
                $data['en'] = "turntable";
                $data['img'] = "/newdev/images/lottery/turntable/img3.png";
                break;
            case 4:
                $data['cn'] = "刮刮乐";
                $data['en'] = "scrape";
                $data['img'] = "/newdev/images/lottery/scrape/layer.png";
                break;
            case 5:
                $data['cn'] = "砸金蛋";
                $data['en'] = "egg";
                $data['img'] = "/newdev/images/lottery/egg/egg3.png";
                break;
            default:
                break;
        }
        return $data;
    }

    /**
     * 乐观所版本号
     * @return string
     */
    public function optimisticLock()
    {
        return "version";
    }

    public function getActivity(){
        $now = date('Y-m-d H:i:s',time());
        $where = [
            'AND',
            ['status' => 1],
            ['admin_status' => 1],
            ['<=','start_date',$now],
            ['>','end_date',$now],
        ];
        return self::find()->where($where)->all();
    }
    
    public function save_address($condition) {
        if(!$condition || !is_array($condition)){
            return false;
        }
        $condition['admin_depart'] =1;//申请部门默认1
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $condition['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return $error;
        }
        return $this->save();
    }

}
