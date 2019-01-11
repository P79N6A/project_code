<?php

namespace app\models\news;

use app\models\BaseModel;

/**
 * This is the model class for table "yi_guide_notify_list".
 *
 * @property string $id
 * @property string $gid
 * @property integer $notice_status
 * @property integer $result_status
 * @property integer $notice_num
 * @property string $next_notice_time
 * @property string $last_modify_time
 * @property string $create_time
 */
class GuideNotifyList extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_guide_notify_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['gid'], 'required'],
            [['gid', 'notice_status', 'result_status', 'notice_num', 'version'], 'integer'],
            [['next_notice_time', 'last_modify_time', 'create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'gid' => 'Gid',
            'notice_status' => 'Notice Status',
            'result_status' => 'Result Status',
            'notice_num' => 'Notice Num',
            'next_notice_time' => 'Next Notice Time',
            'create_time' => 'Create Time',
        ];
    }

    //乐观所版本号
    public function optimisticLock()
    {
        return "version";
    }

    public function getGuide()
    {
        return $this->hasOne(GuideNotify::className(), ['id' => 'gid']);
    }

    public function add($data)
    {
        $data['next_notice_time'] = date("Y-m-d H:i:s");
        $data['last_modify_time'] = date("Y-m-d H:i:s");
        $data['create_time'] = date("Y-m-d H:i:s");
        $check = $this->chkAttributes($data);

        if (!empty($check)) {
            return false;
        }
        return $this->save();
    }

    public function listNotify($stime, $type = 1, $limit = 200)
    {
        $where = [
            'AND',
            ['>=', GuideNotifyList::tableName() . '.create_time', $stime],
            [GuideNotify::tableName() . '.type' => $type],
            [GuideNotifyList::tableName() . '.notice_status' => [1, 5]],
            ['<=', GuideNotifyList::tableName() . '.next_notice_time', date("Y-m-d H:i:s")],
        ];
        return self::find()->joinWith('guide', true, 'LEFT JOIN')->where($where)->limit($limit)->all();
    }

    public function updateSuccess()
    {
        $this->notice_status = 3;
        $this->notice_num += 1;
        $this->last_modify_time = date("Y-m-d H:i:s");
        $res = $this->save();
        return $res;
    }

    public function updateError()
    {
        $status = 5;
        if ($this->notice_num == 6) {
            $status = 4;
        }
        $this->next_notice_time = $this->acNotifyTime($this->notice_num, $this->last_modify_time);
        $this->notice_status = $status;
        $this->notice_num += 1;
        $this->last_modify_time = date("Y-m-d H:i:s");
        $res = $this->save();
        return $res;
    }

    /**
     * 计算下次查询时间
     * @param int $notify_num 当前次数
     * @param str $notify_time 当前时间
     * @return str 下次查询时间
     */
    private function acNotifyTime($notify_num, $notify_time)
    {
        // 累加的分钟
        $addMinutes = [
            1 => 5,
            2 => 30,
            3 => 89,
            4 => 233,
            5 => 610,
            6 => 1560,
        ];

        // 不在上述时,不改变
        if (!isset($addMinutes[$notify_num])) {
            return date('Y-m-d H:i:s');
        }

        // 累加时间
        $time = ($notify_time == '0000-00-00 00:00:00') ? time() : strtotime($notify_time);
        $t = $time + $addMinutes[$notify_num] * 60;
        return date('Y-m-d H:i:s', $t);
    }
}
