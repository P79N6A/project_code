<?php
namespace app\modules\promeapi\logic;

use Yii;

class BaseLogic {

    public $info;
    /**
     * @desc 数据输出
     * @param null $result
     * @param string $info
     * @return null
     */
    protected function returnInfo($result, $info)
    {
        $this->info = $info;
        return $result;
    }
}