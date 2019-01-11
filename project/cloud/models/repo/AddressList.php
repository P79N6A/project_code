<?php

namespace app\models\repo;

/**
 * This is the model class for table "address_list".
 */
class AddressList extends RepoBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'address_list';
    }

    public function getByUserPhone($user_phone, $fields='*') {
        $data = static::find()
            ->select($fields)
            ->distinct()
            ->limit(5000)
            ->where(['user_phone' => $user_phone])
            ->all();
        return $data;
    }

    public function getByPhones($phones, $fields='*') {
        $data = static::find()
            ->select($fields)
            ->where(['in','user_phone',$phones])
            ->limit(100000000)
            ->all();
        return $data;
    }
}
