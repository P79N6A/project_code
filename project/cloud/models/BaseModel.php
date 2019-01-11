<?php

namespace app\models;

use Yii;

class BaseModel extends \yii\db\ActiveRecord {

    /**
     * 定义出错数据
     */
    public $errinfo;

    /**
     * 事务处理
     */
    protected $transaction;
    private static $_models = array();   // class name => model

    /**
     * 静态方法，方便子类静态使用。
     * 需要子类实现
     * @param string
     */

    public static function model($className) {
        if (isset(self::$_models[$className])) {
            return self::$_models[$className];
        } else {
            $model = self::$_models[$className] = new $className(null);
            $model->attachBehaviors($model->behaviors());
            return $model;
        }
    }

    /**
     * 添加数据
     * @param $data
     */
    public function add($data) {
        //1 检测数据是否有效
        if (!is_array($data) || empty($data)) {
            return false;
        }

        //2  设置当前类为可添加的, 并检测是否有错误发生
        $this->setIsNewRecord(true);
        if ($errors = $this->chkAttributes($data)) {
            return $this->returnError(false, $errors);
        }

        //3 保存数据并返回结果
        if (!$this->save()) {
            return $this->returnError(false, $this->errors);
        }
        return true;
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

    /**
     * 根据主键查询
     */
    public function getById($id) {
        return static::findOne($id);
    }

    /**
     * 根据主键查询
     */
    public function getByIds($ids) {
        if (!is_array($ids)) {
            return null;
        }
        return static::findAll($ids);
    }

    /**
     * 批量插入操作
     * 此功能要求各行的字段相同
     * @param $value 二维数据
     * @return number 插入的条数
     */
    public static function insertBatch($values) {
        if (!is_array($values) || !is_array($values[0])) {
            return false;
        }
        $columns = array_keys($values[0]);
        $vs = [];
        foreach ($values as $v) {
            $temp = [];
            foreach ($columns as $name) {
                $temp[] = $v[$name];
            }
            $vs[] = $temp;
        }
        
        $db = static::getDb();
        $command = $db->createCommand()->batchInsert(static::tableName(), $columns, $vs);
        return $command->execute();
    }

    /**
     * 返回结果，同时纪录错误原因
     * @param $result 0 false null 等等错误信息。与正确返回的结果类型保持一致,例如若最终返回一个数字，可以是0. 若返回正确结果应该是个对象，那么这里可以是null
     * @param $errinfo 一般来说是个字符串，代表错误原因
     */
    protected function returnError($result, $errinfo) {
        $this->errinfo = $errinfo;
        return $result;
    }

    /**
     * 检测是不是不为空的数组
     * @param $data
     * @return bool
     */
    protected function valid_array($data) {
        return is_array($data) && !empty($data);
    }

    /*
     * 事务处理封装
     */

    protected function beginTransaction() {
        $this->transaction = Yii::$app->db->beginTransaction();
    }

    protected function endTransaction($ok) {
        if ($ok) {
            $this->transaction->commit();
        } else {
            $this->transaction->rollBack();
        }
    }

}
