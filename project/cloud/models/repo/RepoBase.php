<?php
/**
 * 所有数据分析仓库的数据库表均需要继承此类
 */
namespace app\models\repo;

class RepoBase extends \app\models\BaseModel {
    public static function getDb() {
        return \Yii::$app->db_analysis_repertory;
    }
}
