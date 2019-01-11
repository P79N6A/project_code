<?php
namespace app\models\service;

use Yii;

class service
{

    public function beforeaction()
    {
        return true;
    }

    /**
     * 返回get的数据
     * @param null $name
     * @param null $defaultValue
     * @return mixed
     */
    protected function get($name = null, $defaultValue = null)
    {
        $v = Yii::$app->request->get($name, $defaultValue);
        $v = $v ? $this->new_trim($v) : $v;
        return $v;
    }

    /**
     * 返回post的数据
     * @param null $name
     * @param null $defaultValue
     * @return mixed
     */
    protected function post($name = null, $defaultValue = null)
    {
        $v = Yii::$app->request->post($name, $defaultValue);
        $v = $this->new_trim($v);
        return $v;
    }

    /**
     * 去除空格
     * @param $string
     * @return array|string
     */
    public function new_trim($string)
    {
        if (!is_array($string))
            return trim($string);
        foreach ($string as $key => $val) {
            $string[$key] = self::new_trim($val);
        }
        return $string;
    }
}