<?php
namespace app\common;
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

    /**
     * 通过浏览器验证系统类型
     * @param array $array_notify
     */
    public static function checkPlatform() {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (stripos($agent, 'windows') !== false) {
            $platform = 'windows';
        } else if (stripos($agent, 'iPad') !== false) {
            $platform = 'iPad';
        } else if (stripos($agent, 'iPod') !== false) {
            $platform = 'iPod';
        } else if (stripos($agent, 'iPhone') !== false) {
            $platform = 'iPhone';
        } elseif (stripos($agent, 'mac') !== false) {
            $platform = 'mac';
        } elseif (stripos($agent, 'android') !== false) {
            $platform = 'android';
        } elseif (stripos($agent, 'linux') !== false) {
            $platform = 'linux';
        } else if (stripos($agent, 'Nokia') !== false) {
            $platform = 'Nokia';
        } else if (stripos($agent, 'BlackBerry') !== false) {
            $platform = 'BlackBerry';
        } elseif (stripos($agent, 'FreeBSD') !== false) {
            $platform = 'FreeBSD';
        } elseif (stripos($agent, 'OpenBSD') !== false) {
            $platform = 'OpenBSD';
        } elseif (stripos($agent, 'NetBSD') !== false) {
            $platform = 'NetBSD';
        } elseif (stripos($agent, 'OpenSolaris') !== false) {
            $platform = 'OpenSolaris';
        } elseif (stripos($agent, 'SunOS') !== false) {
            $platform = 'SunOS';
        } elseif (stripos($agent, 'OS\/2') !== false) {
            $platform = 'OS\/2';
        } elseif (stripos($agent, 'BeOS') !== false) {
            $platform = 'BeOS';
        } elseif (stripos($agent, 'win') !== false) {
            $platform = 'win';
        }
        return $platform;
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
    
    
    public static function invtecodefrombyqrcode($code) {
    	$array = array('59786', '54761', '54832', '58210', '58211', '58212', '58213', '58214', '58215', '58216','58217','58218', '58219', '58220', '58221', '58222', '58223', '58224', '58225');
    	if (in_array($code, $array)) {
    		return true;
    	} else {
    		return false;
    	}
    }

    //学生用户，配置问题认证
    public static function authstudentquestion($code = null) {
        $array_code = array(
            '2' => '姓名',
            '3' => '家乡',
            '4' => '毕业院校',
            '5' => '入学年份',
            '6' => '年龄'
        );
        if (empty($code))
            return $array_code;
        else
            return $array_code[$code];
    }

    //社会用户，配置问题认证
    public static function authsociologyquestion($code = null) {
        $array_code = array(
            '2' => '姓名',
            '3' => '家乡',
            '4' => '公司',
            '5' => '职位',
            '6' => '年龄'
        );
        if (empty($code))
            return $array_code;
        else
            return $array_code[$code];
    }
    
    //跳转的URL
    public static function redirect_url($code = null) {
    	$array_code = array(
    			'1' => 'http://121.43.72.237/c/dc.html',
    			'2' => 'http://121.43.72.237/c/dc.html',
    			'3' => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx476bb3649401c450&redirect_uri=http://weixin.xianhuahua.com/dev/activity/banker&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect',
    			'4'=>'http://viewer.maka.im/k/WD1E5HIU',
    	);
    	if (empty($code))
    		return $array_code[1];
    	else
    		return $array_code[$code];
    }

    /**
     * 数字金额转换成中文大写金额的函数
     * String Int  $num  要转换的小写数字或小写字符串
     * return 大写字母
     * 小数位为两位
     * */
    public static function get_amount($num) {
        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "分角元拾佰仟万拾佰仟亿";
        $num = round($num, 2);
        $num = $num * 100;
        if (strlen($num) > 10) {
            return "数据太长，没有这么大的钱吧，检查下";
        }
        $i = 0;
        $c = "";
        while (1) {
            if ($i == 0) {
                $n = substr($num, strlen($num) - 1, 1);
            } else {
                $n = $num % 10;
            }
            $p1 = substr($c1, 3 * $n, 3);
            $p2 = substr($c2, 3 * $i, 3);
            if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
                $c = $p1 . $p2 . $c;
            } else {
                $c = $p1 . $c;
            }
            $i = $i + 1;
            $num = $num / 10;
            $num = (int) $num;
            if ($num == 0) {
                break;
            }
        }
        $j = 0;
        $slen = strlen($c);
        while ($j < $slen) {
            $m = substr($c, $j, 6);
            if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
                $left = substr($c, 0, $j);
                $right = substr($c, $j + 3);
                $c = $left . $right;
                $j = $j - 3;
                $slen = $slen - 3;
            }
            $j = $j + 3;
        }

        if (substr($c, strlen($c) - 3, 3) == '零') {
            $c = substr($c, 0, strlen($c) - 3);
        }
        if (empty($c)) {
            return "零元整";
        } else {
            return $c . "整";
        }
    }

    public static function truncate_utf8_string($string, $length, $etc = '...') {
        $result = '';
        $string = html_entity_decode(trim(strip_tags($string)), ENT_QUOTES, 'UTF-8');
        $strlen = strlen($string);
        for ($i = 0; (($i < $strlen) && ($length > 0)); $i++) {
            if ($number = strpos(str_pad(decbin(ord(substr($string, $i, 1))), 8, '0', STR_PAD_LEFT), '0')) {
                if ($length < 1.0) {
                    break;
                }
                $result .= substr($string, $i, $number);
                $length -= 1.0;
                $i += $number - 1;
            } else {
                $result .= substr($string, $i, 1);
                $length -= 0.5;
            }
        }
        $result = htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
        if ($i < $strlen) {
            $result .= $etc;
        }
        return $result;
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
                $err_info = $new_name;
                $url = Yii::$app->params['back_url'] . '?r=upload';
                $file_content = base64_encode(file_get_contents($to_path . '/' . $new_name));
                $data = 'file_name=' . $new_name . '&file_content=' . rawurlencode($file_content) . '&file_path=' . $to_path;
                $ret = \Http::interface_post($url, $data);
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

    /**
     * @abstract 获取指定二维数组指定列的值，并组成字符串格式，以逗号分隔
     * @param $array 数据数组
     * 		  $v 数据里指定的值
     * 		  $sign 是否需要对值加单引号
     *
     * @return 以逗号连接连接在一起的字符串
     * */
    public static function ArrayToString($array, $v = 'id', $sign = false, $sep = ',') {
        $output = "";
        if (is_array($array)) {
            foreach ($array as $value) {
                foreach ($value as $key => $val) {
                    if ($key == $v) {
                        if ($sign) {
                            $output .= "'" . $val . "'" . $sep;
                        } else {
                            $output .= $val . $sep;
                        }
                    }
                }
            }
        }

        if ($output) {
            $output = trim($output, $sep);
        }

        return $output;
    }

    public static function push_template() {
        $template = array(
            array('title'=>'什么钱不钱的？俗！但我就是给你送钱来了嘿~~','desc'=>'→██████请勿私自传播，扩散'),
            array('title'=>'大概不花钱赚iPhone，这是最快的方式了~','desc'=>'→██████请勿私自传播，扩散'),
            array('title'=>'本人就要包养你了，请快点击决定是否接受。要不…heihei...','desc'=>'→██████请勿私自传播，扩散'),
        );
        $length = count($template);
        if ($length > 0) {
            $num = rand(0, $length - 1);
            return $template[$num];
        }else{
            return null;
        }
    }

}
