<?php

namespace app\models\repo;

/**
 * This is the model class for table "reverse_address_list".
 */
class ReverseAddressList extends RepoBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'reverse_address_list';
    }

    public function getByPhones($phones, $fields='*') {
        $data = static::find()
            ->select($fields)
            ->where(['in','phone',$phones])
            ->limit(1000000)
            ->all();
        return $data;
    }
    public function getCountByPhones($phones) {
        $count = static::find()
            ->where(['in','phone',$phones])
            ->count();
        return $count;
    }
}
