<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_channel_source".
 *
 * @property string $id
 * @property integer $type
 * @property string $pid
 * @property integer $level
 * @property integer $purpose
 * @property integer $status
 * @property string $create_time
 * @property string $channel_code
 * @property string $channel_name
 * @property string $last_modify_time
 * @property integer $back_img
 * @property string $url
 * @property integer $version
 * @property string $s_type
 * @property string $s_id
 */
class Channel_source extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_channel_source';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['type', 'pid', 'level', 'purpose', 'status', 'back_img', 'version', 's_type', 's_id'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['channel_code'], 'string', 'max' => 10],
            [['channel_name'], 'string', 'max' => 50],
            [['url', 'short_url'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'pid' => 'Pid',
            'level' => 'Level',
            'purpose' => 'Purpose',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'channel_code' => 'Channel Code',
            'channel_name' => 'Channel Name',
            'last_modify_time' => 'Last Modify Time',
            'back_img' => 'Back Img',
            'url' => 'Url',
            'short_url' => 'Short Url',
            'version' => 'Version',
            's_type' => 'S Type',
            's_id' => 'S ID',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    /**
     * 
     * @param type $type 1:页面访问 2:注册按钮点击
     * @param type $date
     * @return type
     */
    public function getPvuv($type = 1, $date = '') {
        $id = $type == 1 ? $this->s_type : $this->s_id;
        if (!$id) {
            return ['pv' => 0, 'uv' => 0];
        }
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        $end_date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
        $pv = Statistics::find()->where(['type' => $id])->andFilterWhere(['BETWEEN', 'create_time', $date, $end_date])->count();
        $uv = Statistics::find()->select('remoteip')->distinct()->where(['type' => $id])->andFilterWhere(['BETWEEN', 'create_time', $date, $end_date])->count();
        return ['pv' => $pv, 'uv' => $uv];
    }

    /**
     * 获取一级渠道
     * @param type $name
     * @return type
     */
    public function getFirstLevel($name = '') {
        $channel = self::find()->where(['level' => 1, 'status' => 1]);
        if (!empty($name)) {
            $channel = $channel->andFilterWhere(['LIKE', 'channel_name', $name]);
        }
        return $channel->AsArray()->all();
    }

    /**
     * 获取一级渠道
     * @param type $name
     * @return type
     */
    public function getAllChannel($page = 0, $limit = 5, $name = '') {
        $channel = self::find()->where(['status' => 1]);
        if (!empty($name)) {
            $channel = $channel->andFilterWhere(['LIKE', 'channel_name', $name]);
        }
        return $channel->offset($page * $limit)->limit($limit)->all();
    }

    /**
     * 获取一级渠道
     * @param type $name
     * @return type
     */
    public function addUrl($url) {
        if (empty($url)) {
            return FALSE;
        }
        try {
            $this->url = $url;
            $this->last_modify_time = date('Y-m-d H:i:s');
            return $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    /**
     * 
     * @param type $type 链接形式 1:H5 2：直接下载 3：下载页面
     * @param type $pid 父级id
     * @param type $level  等级 1：一级渠道 2：二级却道
     * @param type $purpose 链接用途 1:投放 2：渠道推广
     * @param type $channel_name    渠道名称
     * @param type $back_img    H5背景图
     * @return boolean
     */
    public function addChannel($type, $pid, $level, $purpose, $channel_name, $back_img) {
        $mp = [
            '1' => 'a',
            '2' => 'b',
            '3' => 'c',
            '4' => 'd',
        ];
        $data['type'] = $type;
        $data['pid'] = $pid;
        $data['level'] = $level;
        $data['purpose'] = $purpose;
        $data['channel_code'] = (string) $this->getMaxcode();
        $data['channel_name'] = $channel_name;
        if ($type == 1) {
            $data['back_img'] = $back_img;
            $statisModel = new Statistics_type();
            $s_t_id = $statisModel->addChannelType($data['channel_code'], $channel_name);
            $str = $mp[$back_img] . "_" . $data['channel_code'];
            if (!empty($s_t_id)) {
                $str .= '&type=' . $s_t_id['s_type'] . '&regtype=' . $s_t_id['s_id'];
                $data['s_type'] = $s_t_id['s_type'];
                $data['s_id'] = $s_t_id['s_id'];
            }
            $data['url'] = Yii::$app->params['app_url'] . '/new/traffic/regtraffic?from=' . $str;
        } else if ($type == 3) {
            $statisModel = new Statistics_type();
            $s_t_id = $statisModel->addDownChannelType($data['channel_code'], $channel_name);
            $str = $data['channel_code'];
            if (!empty($s_t_id)) {
                $str .= '&down_type=' . $s_t_id['s_id'];
            }
            $data['url'] = Yii::$app->params['app_url'] . '/new/ds/downnew?type=' . $str;
        }
        $data['status'] = 1;
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return FALSE;
        }
        return $this->save();
    }

    public function getMaxcode() {
        $channel = self::find()->max('channel_code');
        $code = !empty($channel) ? $channel + 1 : 5001;
        while ($code) {
            $is_channel = Statistics_type::find()->where(['come_from' => $code])->one();
            if (empty($is_channel)) {
                break;
            } else {
                $code++;
            }
        }
        return $code;
    }

    public function ModifyChannel($channel_name, $level, $p_id, $purpose, $back_img) {
        try {
            $this->channel_name = $channel_name;
            $this->level = $level;
            $this->pid = $p_id;
            $this->purpose = $purpose;
            $this->back_img = $back_img;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
        return $result;
    }

    /**
     * 渠道上架
     * @return boolean
     */
    public function onloand() {
        try {
            $this->status = 1;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
        return $result;
    }

    /**
     * 渠道下架
     * @return boolean
     */
    public function down() {
        try {
            $this->status = 2;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
        return $result;
    }

    /**
     * 添加短链
     * @param type $shorturl
     * @return boolean
     */
    public function addShortUrl($shorturl) {
        try {
            $this->short_url = $shorturl;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $ex) {
            return false;
        }
        return $result;
    }

}
