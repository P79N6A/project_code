<?php
namespace app\common;

use Yii;
/**
 * 二维数组处理
 */
class ArrayDas{
    private $leftData;
    private $rightData;
    /**
     * 初始化
     */
    public function __construct(){
        
    }
    public function leftJoin($leftData, $rightData, $onKey){
        return $this->mergeData($leftData,$rightData,"left",$onKey);
    }
    public function rightJoin(&$leftData, &$rightData, $onKey){
        return $this->leftJoin($rightData, $leftData, $onKey);
    }
    public function innerJoin(&$leftData, &$rightData, $onKey){
        return $this->mergeData($leftData,$rightData,"inner",$onKey);
    }
    public function outerJoin(&$leftData, &$rightData, $onKey){
        return $this->mergeData($leftData,$rightData,"outer",$onKey);
    }
    public function mergeData(&$leftData,&$rightData,$join,$onKey){
        // 1 验证数组
        if( !is_array($leftData) || !is_array($rightData) ){
            return null;
        }
        $newarr = [];
        
        //2 根据joinkey处理
        $rMap=[];
        $rKeys = [];
        foreach($rightData as $bv){
            $key = $this->createKey($bv, $onKey);
            $rMap[$key][] = $bv;
            $rKeys[$key] = true;
        }
        
        //4 处理左关联
        foreach($leftData as $av){
            $arr=[];
            $key = $this->createKey($av, $onKey);
            
            // 若b中也存在, 那么合并关联
            $bvs = isset($rMap[$key]) ? $rMap[$key] : null;
            if( is_array($bvs) ){
                foreach($bvs as $bv){
                    $newarr[] = array_merge($av,$bv);
                }
            }else{
                // 对于b表不存时,处理方式不一样.
                if($join=='left'){
                    $newarr[] = $av;
                }elseif($join=='outer'){
                    $newarr[] = $av;
                }elseif($join=='inner'){
                    // inner 必须两者都有
                }
            }
            
            unset($rKeys[$key]);// 删除b中用过的key
        }
        
        //5 outer处理也需要考虑b中的纪录
        if($join == 'outer'){
            foreach($rKeys as $key=>$v){
                $newarr = array_merge($newarr,$rMap[$key]);
            }
        }
                
        //6 返回结果
        return $newarr;
    }
    public function createKey(&$data, $onKey){
        if(is_array($onKey)){
            $ks  = [];
            foreach($onKey as $k){
                $ks[] = $data[$k];
            }
            return implode(';',$ks);
        }else{
            return $data[$onKey];
        }
    }
	
	/**
	 * 重排二维数组的顺序
	 */
	public function formatColumns($values, $columns=null){
		if(!$columns){
			$columns = array_keys($values[0]);
		}
		$vs = [];
		foreach($values as $v){
			$temp=[];
			foreach($columns as $name){
				$temp[$name] = $v[$name];
			}
			$vs[] = $temp;
		}
		return $vs;
	}
	/**
	 * 二维数据缺失值填充
	 */
	public function fillna(&$data, $map){
		if( !is_array($data) || empty( $data ) ){
			return false;
		}
		foreach( $data as &$v ){
			foreach($map as $filed=>$value){
				$v[$filed] = isset($v[$filed]) ? $v[$filed] : $value;
			}
		}
		return true;
	}
}