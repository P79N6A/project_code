<?php
namespace app\commonapi;
use Yii;

class appLogger
{   
    public static function createdir($dir)
    {
		if(file_exists($dir))return true;
		$dir	= str_replace("\\","/",$dir);
		substr($dir,-1)=="/"?$dir=substr($dir,0,-1):"";
		$dir_arr	= explode("/",$dir);
		$str = '';
		foreach($dir_arr as $k=>$a){
			$str	= $str.$a."/";
			if(!$str)continue;
			if(!file_exists($str))mkdir($str,0755);
		}
		return true;
	}

    /**
     * 记录app日志，string用于日志分析
     * @param $file
     * @param $logArr
     * @return bool
     */
    public static function saveLog($file, $logArr){
       if(!is_array($logArr)){
           return false;
        }
        foreach($logArr as $key => $val){
            $newLogArr[$key] = str_replace([",","\r","\n"],["，","",""],$val);
        }
        $logSrt = implode(",",$newLogArr);

        $rootdir = dirname(Yii::$app->basePath);
        $filepath = $rootdir. '/detaillog/'.$file.'/'.date('Ym').'/'.date('d').'.log';
        self::createdir(dirname($filepath));
        
        file_put_contents($filepath, $logSrt."\n", FILE_APPEND);
    }
}
