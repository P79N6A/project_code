<?php

namespace app\models;

use Yii;
class Risk extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xhh_risk';
    }
	/**
     * 添加一条同盾接口返回信息
     */
    public function addRisk($condition) {
        if (empty($condition)) {
            return false;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->create_time = date('Y-m-d H:i:s');
        $this->version = 1;
        $result = $this->save();
        if ($result) {
            return Yii::$app->db->getLastInsertID();
        } else {
            return false;
        }
    }

    /**
     * 获取下载链接
     * @param  str $seq_id     
     * @return []             
     */
    public function getBySeq($seq_id){
        // 查询手机号通讯纪录
        if (!$seq_id) {
            return null;
        }
        $data = static::find()->where(['seq_id' => $seq_id])
            ->limit(1)
            ->asArray()
            ->one();
        if (!$data) {
            return null;
        }

        // 域名获取
        $domain = $this->getDomain($data['create_time']);

        // 获取文件位置
        $data['report_url'] = $domain . $data['url'];
        $data['detail_url'] = str_replace(".json", "_detail.json", $data['report_url']);

        return $data;
    }
    /**
     * 获取域名
     * @param  datetime $create_time 时间格式
     * @return str              域名
     */
    private function getDomain($create_time) {
        $create_time = strtotime($create_time);
        if (time() - $create_time > 86400 * 2) {
            //$domain = "http://124.193.149.180:8100";
            $domain = "http://123.207.141.180";
        } else {
            $domain = "http://open.xianhuahua.com";
        }
        return $domain;
    }
}
