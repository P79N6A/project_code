<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/25
 * Time: 10:19
 * 通讯录写入到ssdb中，以af_tag_base为源数据查找address_list并记录ssdb
 *
 *  D:\phpStudy\php\php-5.6.27-nts\php.exe  D:\www\cloud\yii addresstossdb runData
 */
namespace app\commands;

use app\common\Logger;
use app\models\anti\AfTagBase;
use app\models\repo\AddressList;
use Yii;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;

class AddresstossdbController extends BaseController{
    private $limit = 100;
    private $log_file = "address_list";

    /**
     * 入口
     * @param null $start_id
     * @param null $end_id
     */
    public function runData($start_id = null, $end_id = null)
    {
        $readFile = $this->_returnFile();
        if (empty($start_id) || empty($end_id)){
            if (is_file($readFile)){
                $file_data = file_get_contents($readFile);
                $start_id = empty($file_data) ? 1 : $file_data;
                $end_id = $start_id + $this->limit - 1;
            }else{
                $start_id = 1;
                $end_id = $start_id + $this->limit - 1;
            }
        }
        $log_write = vsprintf("开始id：%s -- 结束id：%s", [$start_id, $end_id]);
        Logger::dayLog($this->log_file, $log_write);
        $logical_num = $this->_logicalProgram($start_id, $end_id);
        printf("insert %d num\n", $logical_num);
        echo "done!";
        
    }

    /**
     * 逻辑处理
     * @param $start_id
     * @param $end_id
     * @return bool|int
     */
    private function _logicalProgram($start_id, $end_id)
    {
        $oAfTagBase = new AfTagBase();
        //条数
        $user_phone_data = $oAfTagBase->getUserPhoneData($start_id, $end_id);
        if (empty($user_phone_data)){
            $log_write = vsprintf("开始id：%s -- 结束id：%s 暂无数据", [$start_id, $end_id]);
            Logger::dayLog($this->log_file, $log_write);
            return false;
        }
        $success = 0;
        $address_id = 0;
        foreach($user_phone_data as $value){
            $phone = ArrayHelper::getValue($value, 'phone');
            $address_data = $this->_getAddresData($phone);
            $log_write = vsprintf("runing %s--%s ", [$phone, count($address_data)]);
            Logger::dayLog($this->log_file, $log_write);
            if (count($address_data) == 0 || empty($address_data)){
                continue;
            }

            $add_ssdb = $this->_addSsdb($phone, json_encode($address_data));
            if ($add_ssdb){
                $success += 1;
            }
            $address_id = ArrayHelper::getValue($value, "id");
        }

        $log_write = vsprintf("实际执行id %s--%s ", [$start_id, $address_id]);
        Logger::dayLog($this->log_file, $log_write);
        $address_id = $address_id + 1;
        try {
            $file_id = $this->_returnFile();
            if (!is_dir(dirname($file_id))){
                $this->_createdir(dirname($file_id));
            }
            $save_file = file_put_contents($file_id, $address_id);
        }catch(ErrorException $e){
            Logger::dayLog($this->log_file,'记录最后ID: '.$address_id);
            return false;
        }
        return $success;

    }

    /**
     * 获取通讯录信息
     * @param $phone
     * @return bool
     */
    private function _getAddresData($phone)
    {
        if (empty($phone)){
            return false;
        }
        # 获取通讯录手机号
        $oAddressList = new AddressList();
        $address_list = $oAddressList->getByUserPhone($phone);
        if (empty($address_list)){
            return false;
        }
        $list_data = ArrayHelper::getColumn($address_list, "phone");
        return $list_data;
    }

    /**
     * 插入到ssdb里
     * @param $phone
     * @param $json_data
     * @return bool
     */
    private function _addSsdb($phone, $json_data)
    {
        if (empty($phone) || empty($json_data)){
            return false;
        }
        try {
            $address_set = Yii::$app->ssdb_address->set($phone, $json_data);
            if ($address_set) {
                return true;
            }
        }catch (ErrorException $e){
            $log_write = vsprintf("手机号：%s -- address_list：%s", [$phone, $json_data]);
            Logger::dayLog($this->log_file, $log_write);
            return false;
        }

    }

    /**
     * 创建目录
     * @param $dir
     * @return bool
     */
    private function _createdir($dir)
    {
        //if(!is_dir($dir))return false;
        if(file_exists($dir))return true;
        $dir	= str_replace("\\","/",$dir);
        substr($dir,-1)=="/"?$dir=substr($dir,0,-1):"";
        $dir_arr	= explode("/",$dir);
        $str = '';
        foreach($dir_arr as $k=>$a){
            $str	= $str.$a."/";
            if(!$str)continue;
            //echo $str."<br>";
            if(!file_exists($str))mkdir($str,0755);
        }
        return true;
    }

    /**
     * 记录最后执行的ID文件
     * @return string
     */
    private function _returnFile()
    {
        return Yii::$app ->basePath . '/commands/data/aftagbaseId.txt';
    }

}