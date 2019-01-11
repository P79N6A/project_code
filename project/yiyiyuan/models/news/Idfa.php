<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_idfa".
 *
 * @property string $id
 * @property string $idfa
 */
class Idfa extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_idfa';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            [['idfa'], 'string', 'max' => 45] 
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idfa' => 'Idfa',
        ];
    }
}
