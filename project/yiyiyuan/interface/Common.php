<?php
if (!class_exists('Http')) {
    include '../common/Http.php';
}
if (!class_exists('Logger')) {
    include '../common/Logger.php';
}

class Common {

    /**
     * 验签
     * @param array $array_notify
     */
    public static function verificationSign($array_notify) {
        unset($array_notify['sign']);

        $paramkey = array_keys($array_notify);
        sort($paramkey);
        $signstr = '';
        foreach ($paramkey as $key => $val) {
            $signstr .= $array_notify[$val];
        }

        $key = Yii::$app->params['xianhua_key'];

        //签名
        $sign = md5($signstr . $key);
        return $sign;
    }

    //判断是否属手机
    public static function is_mobile() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = Array("240x320", "acer", "acoon", "acs-", "abacho", "ahong", "airness", "alcatel", "amoi", "android", "anywhereyougo.com", "applewebkit/525", "applewebkit/532", "asus", "audio", "au-mic", "avantogo", "becker", "benq", "bilbo", "bird", "blackberry", "blazer", "bleu", "cdm-", "compal", "coolpad", "danger", "dbtel", "dopod", "elaine", "eric", "etouch", "fly ", "fly_", "fly-", "go.web", "goodaccess", "gradiente", "grundig", "haier", "hedy", "hitachi", "htc", "huawei", "hutchison", "inno", "ipad", "ipaq", "ipod", "jbrowser", "kddi", "kgt", "kwc", "lenovo", "lg ", "lg2", "lg3", "lg4", "lg5", "lg7", "lg8", "lg9", "lg-", "lge-", "lge9", "longcos", "maemo", "mercator", "meridian", "micromax", "midp", "mini", "mitsu", "mmm", "mmp", "mobi", "mot-", "moto", "nec-", "netfront", "newgen", "nexian", "nf-browser", "nintendo", "nitro", "nokia", "nook", "novarra", "obigo", "palm", "panasonic", "pantech", "philips", "phone", "pg-", "playstation", "pocket", "pt-", "qc-", "qtek", "rover", "sagem", "sama", "samu", "sanyo", "samsung", "sch-", "scooter", "sec-", "sendo", "sgh-", "sharp", "siemens", "sie-", "softbank", "sony", "spice", "sprint", "spv", "symbian", "tablet", "talkabout", "tcl-", "teleca", "telit", "tianyu", "tim-", "toshiba", "tsm", "up.browser", "utec", "utstar", "verykool", "virgin", "vk-", "voda", "voxtel", "vx", "wap", "wellco", "wig browser", "wii", "windows ce", "wireless", "xda", "xde", "zte");
        $is_mobile = false;
        foreach ($mobile_agents as $device) {
            if (stristr($user_agent, $device)) {
                $is_mobile = true;
                break;
            }
        }
        return $is_mobile;
    }

    //兼容低版本PHP array_column  根据 key  获取 列数据
    public static function i_array_column($input, $columnKey, $indexKey = null) {
        if (!function_exists('array_column')) {
            $columnKeyIsNumber = (is_numeric($columnKey)) ? true : false;
            $indexKeyIsNull = (is_null($indexKey)) ? true : false;
            $indexKeyIsNumber = (is_numeric($indexKey)) ? true : false;
            $result = array();
            foreach ((array) $input as $key => $row) {
                if ($columnKeyIsNumber) {
                    $tmp = array_slice($row, $columnKey, 1);
                    $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
                } else {
                    $tmp = isset($row[$columnKey]) ? $row[$columnKey] : null;
                }
                if (!$indexKeyIsNull) {
                    if ($indexKeyIsNumber) {
                        $key = array_slice($row, $indexKey, 1);
                        $key = (is_array($key) && !empty($key)) ? current($key) : null;
                        $key = is_null($key) ? 0 : $key;
                    } else {
                        $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                    }
                }
                $result[$key] = $tmp;
            }
            return $result;
        } else {
            return array_column($input, $columnKey, $indexKey);
        }
    }

    public static function Uploadfun($up_info, $to_path, $typelist = array(), $file_size = 2000000) {
        if ($up_info['error'] > 0) {//1.判断文件上传是否错误
            switch ($up_info['error']) {
                case 1:
                    $err_info = "上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值";
                    break;
                case 2:
                    $err_info = "上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值";
                    break;
                case 3:
                    $err_info = "文件只有部分被上传";
                    break;
                case 4:
                    $err_info = null; //"没有文件被上传";
                    break;
                case 6:
                    $err_info = "找不到临时文件夹";
                    break;
                case 7:
                    $err_info = "文件写入失败";
                    break;
                default:
                    $err_info = "未知的上传错误";
                    break;
            }
            return $err_info;
            die();
        }

        if (count($typelist) > 0) {//2.判断上传文件类型是否合法
            if (!in_array($up_info['type'], $typelist)) {
                $err_info = '文件类型不合法！' . $up_info['type'];
                return $err_info;
                die();
            }
        }

        if ($up_info['size'] > $file_size) {//4.判断上传文件大小是否超出允许值
            return $err_info = '文件大小超过' . $file_size;
            die();
        }

        if (!is_dir($to_path)) {
            mkdir($to_path, 0777, true);  //注意权限问题
        }

        $fileinfo = pathinfo($up_info['name']); //5.上传文件重命名
        $exten_name = isset($fileinfo['extension']) ? $fileinfo['extension'] : "jpg";

        do {
            $main_name = date('YmHis' . '--' . rand(100, 999));
            $new_name = $main_name . '.' . $exten_name;
        } while (file_exists($to_path . '/' . $new_name));

        if (is_uploaded_file($up_info['tmp_name'])) {//6.判断是否是上传的文件，并移动文件
            if (move_uploaded_file($up_info['tmp_name'], $to_path . '/' . $new_name)) {
//  			$err_info= date("Y-m-d").'/'.$new_name;
//  			$url = Yii::$app->params['back_url'].'?r=upload';
//  			$file_content = base64_encode(file_get_contents($to_path.'/'.$new_name));
//  			$data = 'file_name='.$new_name.'&file_content='.rawurlencode($file_content).'&file_path='.$to_path;
//  			$ret = \Http::interface_post($url, $data);
                \Logger::errorLog(print_r($ret, true), 'uploadimage');
                return $err_info;
            } else {
                return $err_info = '上传文件移动失败！';
                die();
            }
        } else {
            return $err_info = '这个文件不是上传文件！';
            die();
        }
    }

}
