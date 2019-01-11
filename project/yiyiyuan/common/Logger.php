<?php
namespace app\common;
class Logger
{
    public static function createdir($dir)
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

	 public static function log( $filepath, $line )
	{
		self::createdir(dirname($filepath));
		$line = date("Y-m-d H:i:s").'=>'.$line;
		$file_handle = fopen( $filepath , "a" );
		fwrite( $file_handle , $line );
		fclose($file_handle);
	}

	public static function errorLog( $content , $type='log', $filename='weixin' ){
		$filepath = \Yii::$app->basePath.'/log/'.$filename.'/'.date('Y').'/'.date('m').'/'.date('d').'/'.$type.'.txt';
		self::log( $filepath  , $content  );
	}

    /**
     * 纪录错误日志
     * 按月分组
     */
    private static function saveLog( $categore , $content ){
        $filepath = \Yii::$app->basePath. "/log/{$categore}/" . date('Ym/d') . '.txt';
        self::log( $filepath , $content  );
    }

    /**
     * 日志记法
     * 0: file
     * 1... 内容自动以\t分隔, 数组自动var_export($c,true)转换成串
     */
    public static function dayLog(){
        //1 获取第一个参数作为文件名
        $params = func_get_args();
        $filePath = $params[0];
        if( !$filePath ){
            return false;
        }
        unset($params[0]);
        if(empty($params)){
            return false;
        }

        //2 将参数重组
        $ps = [];
        foreach($params as $param){
            if( is_array($param) || is_object($param) ){
                $param = var_export($param, true);
            }
            $ps[] = $param;
        }

        $content = implode("\t:\t", $ps);
        static::saveLog($filePath, $content);
        return true;
    }
}
