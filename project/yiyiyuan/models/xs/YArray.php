<?php

namespace app\models\xs;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * 只要用于处理一维数据的
 * 1. 合并, add,get
 * 2. 加前缀, addPrefix
 * 3. 键不存在时, 可返回默认值等.getByKeys 
 */
class YArray
{
    private $data;
    public function __construct(){
        // $this->data = $this->asArray($data); 
        $this->data = [];
    }
    /**
     * 添加一个数组
     */
    public function add($subArr, $prefix=''){
        $subArr = $this->asArray($subArr);
        if( !empty($subArr) ){
            if($prefix!=''){
                $subArr = $this->addPrefix($subArr, $prefix);
            }
            $this->data = array_merge($this->data, $subArr); 
        }
    }
    public function get(){
        return $this->data;
    }
    /**
     * 根据键值获取数据中元素, 若不存在则给予默认值
     */
    public function getByKeys(&$data,$keys,$default){
        $arr = [];
        foreach($keys as $key){
            if(isset($data[$key])){
                $arr[$key] = $data[$key];
            }elseif(!is_null($default)){
                $arr[$key] = $default;
            }
        }
        return $arr;
    } 
    /**
     * 为数据加前缀
     */
    public function addPrefix(&$data, $prefix=""){
        if(empty($data)){
            return null;
        }
        $arr = $this->asArray($data);
        $newArr = [];
        foreach($arr as $key => $value){
            $newKey = $prefix ? $prefix.$key : $key;
            $newArr[$newKey] = $value;
        }
        return $newArr;
    }
    /**
     * 转化成数组
     */
    public function asArray($data){
        if(is_array($data)){
        }elseif(is_object($data)){
            $data = ArrayHelper::toArray($data);
        } else{
            $data = [];
        }  
        return $data; 
    }
}
