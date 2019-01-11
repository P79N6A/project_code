<?php

namespace app\modules\dev\controllers;

use app\commands\SubController;
use app\models\dev\Attention;
use app\models\dev\Gprs;
use app\models\dev\Statistics;
use app\commonapi\Http;
use app\commonapi\Logger;
use Yii;

if (!class_exists('WXBizMsgCrypt')) {
    require '../weixincommon/wxBizMsgCrypt.php';
}

class DefaultController extends SubController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        if (!isset($_GET['echostr'])) {
            $this->responseMsg();
        } else {
            $this->valid();
        }
    }

    public function actionError() {
        return $this->render('error');
    }

    //验证签名
    public function valid() {
        $echoStr = $_GET["echostr"];
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $tmpArr = array(Yii::$app->params['TOKEN'], $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            echo $echoStr;
            exit;
        }
    }

    //响应消息
    public function responseMsg() {
        $timestamp = Yii::$app->request->get('timestamp', '');
        $nonce = Yii::$app->request->get('nonce', '');
        $msg_signature = Yii::$app->request->get('msg_signature', '');
        if (empty($timestamp) || empty($nonce) || empty($msg_signature)) {
            echo "";
            exit;
        }
        $encrypt_type = (isset($_GET['encrypt_type']) && ($_GET['encrypt_type'] == 'aes')) ? "aes" : "raw";
        $postStr = file_get_contents('php://input');
        if (!empty($postStr)) {
            //解密
            if ($encrypt_type == 'aes') {
                $pc = new \WXBizMsgCrypt(Yii::$app->params['TOKEN'], Yii::$app->params['EncodingAESKey'], Yii::$app->params['AppID']);
                //$this->logger(" D \r\n".$postStr);
                Logger::errorLog(print_r($postStr, true), 'postStr');
                $decryptMsg = "";  //解密后的明文
                $errCode = $pc->DecryptMsg($msg_signature, $timestamp, $nonce, $postStr, $decryptMsg);
                $postStr = $decryptMsg;
            }
            //$this->logger(" R \r\n".$postStr);
            Logger::errorLog(print_r($postStr, true), 'de_postStr');
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
            $openid = $postObj->FromUserName;
            $date = date('Y-m-d H:i:s');
            $ticket = isset($postObj->Ticket) ? 'qrd' : 'find';
            $result = '';

            //消息类型分离
            if ($RX_TYPE == 'event') {
                if ($postObj->Event == 'subscribe') {
                    //判断是通过搜索公众号关注还是扫码关注
                    if ($ticket == 'qrd') {
                        //扫码关注
                        $eventkey = trim($postObj->EventKey);
                        $streventkey = explode("_", $eventkey);
                        $sql = "insert into " . Attention::tableName() . "(qr_id,openid,create_time) value('$streventkey[1]','$openid','$date')";
                        $ret = Yii::$app->db->createCommand($sql)->execute();
                    } else {
                        //搜索关注
                        $sql = "insert into " . Attention::tableName() . "(qr_id,openid,create_time) value('0','$openid','$date')";
                        $ret = Yii::$app->db->createCommand($sql)->execute();
                    }
                    //关注公众号
                    $result = $this->receiveEvent($postObj);
                    //先查询是否还有可以发放的优惠券，如果有，则判断是否领取
//             		$coupon = Coupon::find()->select(array('id','exchange_code'))->where(['type'=>1])->andWhere("openid is NULL")->orderBy('id')->one();
//             		if(!empty($coupon))
//             		{
//             			//判断该用户是否领取，如果未领取则发放，否则不发放
//             			$couponbyuserinfo = Coupon::find()->where(['openid'=>$openid])->andWhere(['type'=>1])->one();
//             			if(empty($couponbyuserinfo))
//             			{
//             				//发放优惠券
//             				$nowtime = date('Y-m-d H:i:s');
//             				$sql = "update ".Coupon::tableName()." set openid='$openid',last_modify_time='$nowtime' where id=".$coupon['id'];
//             				$ret = Yii::$app->db->createCommand($sql)->execute();
//             				\Logger::errorLog('sendsms', 'sendtemplatetouer33');
//             				//优惠券发放成功，向用户推送模板消息
//             				$template_id = '7I1U4DDW6j88_Kwmem4IGGMgEAkfyd4kUVuIzYH0-Q8';
//             				$exchage_code = $coupon['exchange_code'];
//             				$data = '{
// 										           "touser":"'.$openid.'",
// 										           "template_id":"'.$template_id.'",
// 										           "topcolor":"#FF0000",
// 										           "data":{
// 										                   "first": {
// 										                       "value":"恭喜您领到一张陪你看电影的电影抵扣券",
// 										                       "color":"#173177"
// 										                   },
// 										                   "keyword1":{
// 										                       "value":"陪你看电影抵扣券",
// 										                       "color":"#173177"
// 										                   },
// 										                   "keyword2": {
// 										                       "value":"'.$exchage_code.'",
// 										                       "color":"#173177"
// 										                   },
// 										                   "keyword3": {
// 										                       "value":"领取后三个月",
// 										                       "color":"#173177"
// 										                   },
// 										                   "remark":{
// 										                       "value":"请下载陪你看电影APP进行兑换！（不限场次及电影，不可叠加使用）\n点击下方“信用投资”开始体验！",
// 										                       "color":"#173177"
// 										                   }
// 										           }
// 										      }';
//             				$resulttemplate = $this->sendTemplatetouser($data);
//             				\Logger::errorLog(print_r($resulttemplate, true), 'sendtemplatetouserbygewala');
//             			}
//             		}
                } else if ($postObj->Event == 'TEMPLATESENDJOBFINISH') {
                    //判断消息模板事件
                } else if ($postObj->Event == 'unsubscribe') {
                    //取消关注公众号
                    $time = date('Y-m-d H:i:s');
                    $sql = "update " . Attention::tableName() . " set type=2,cancle_time='$time' where openid = '$openid' and type =1";
                    $ret = Yii::$app->db->createCommand($sql)->execute();
                    echo "";
                    exit;
                } else if ($postObj->Event == 'SCAN') {
                    $result = $this->receiveEvent($postObj);
                    //用户已关注
                } else if ($postObj->Event == 'CLICK') {
                    //用户点击菜单推送
                    $result = $this->receiveEvent($postObj);
                } else if ($postObj->Event == 'LOCATION') {
                    //地理位置
                    $stropenid = $postObj->FromUserName; //发送方帐号
                    $latitude = $postObj->Latitude;
                    $longtitude = $postObj->Longitude;
                    $accuracy = $postObj->Precision;
                    Logger::errorLog(print_r($postObj, true), 'gprs_test');

                    // redis 将同一openid的gps缓存一天
                    $gps_key = "GPS_WX_" . $stropenid;
                    $redis = Yii::$app->redis;
                    if ($redis->get($gps_key)) {
                        echo '';
                        exit;
                    }
                    $redis->setex($gps_key, 86400, 1);
                    //end redis
                    //添加地理位置信息
                    $sql = "insert into " . Gprs::tableName() . "(openid,latitude,longtitude,accuracy,create_time) value('$stropenid','$latitude','$longtitude','$accuracy','$date')";
                    $ret = Yii::$app->db->createCommand($sql)->execute();
                    echo "";
                    exit;
                }
            } else if ($RX_TYPE == 'text') {
                $result = $this->receiveText($postObj);
            } else if ($RX_TYPE == 'image') {
                $result = $this->receiveImage($postObj);
            } else if ($RX_TYPE == 'voice') {
                $result = $this->receiveVoice($postObj);
            }
//             switch ($RX_TYPE)
//             {
//                 case "event":
//                     $result = $this->receiveEvent($postObj);
//                     break;
//                 case "text":
//                     $result = $this->receiveText($postObj);
//                     break;
//             }
            // $this->logger(" R \r\n".$result);
            Logger::errorLog(print_r($result, true), 'result');
            //加密
            if ($encrypt_type == 'aes') {
                $encryptMsg = ''; //加密后的密文
                $errCode = $pc->encryptMsg($result, $timestamp, $nonce, $encryptMsg);
                $result = $encryptMsg;
                // $this->logger(" E \r\n".$result);
                Logger::errorLog(print_r($result, true), 'en_result');
            }
            echo $result;
            exit;
        } else {
            echo "";
            exit;
        }
    }

    //接收事件消息
    private function receiveEvent($object) {
        $content = "";
        switch ($object->Event) {
            case "subscribe":
//                $text = "微信公众号首关语“欢迎关注先花一亿元，新用户发起借款30分钟到账，点击左下角“我要借钱”急速发起借款，都来先花花，一起有钱花。\n\n请根据您的问题回复相应数字，我们会在第一时间为您解答\n";
//                $text .="回复“1”借款审核需要多少长时间？\n回复“2”视频认证与自拍照认证有什么区别？\n回复“3”怎样还款？\n回复“4”借款被驳回之后怎么办？\n回复“5”怎样提升借款额度？\n回复“6”借款发生逾期之后怎么办？\n";
//                $text .="回复“7”手机号认证是什么？\n回复“8”如何参与优惠卷活动？\n\n 其他问题请直接咨询";
//                //$text = "小主，你是来赚钱的吗？\n是！\n请点击<a href = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxebb286d89943a38b&redirect_uri=http://yyy.xianhuahua.com/dev/invest&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect'>“点我赚钱”</a>马上注册\n\n 担保卡投资，期限短，流动强；收益高；\n还可享受双倍收益，最高年收益21.76%";
//                $this->subscribe($object, $text);
                $content = array();
                //$content[] = array("Title" => "30秒学会如何借款", "Description" => "新手必看，借款攻略", "PicUrl" => "http://yyy.xianhuahua.com/images/banner/new.png", "Url" => "http://t.cn/RcY3qyv");
                $pic_url = Yii::$app->params['app_url'] . '/images/banner/new_4.jpg';
                $content[] = array("Title" => "30秒学会如何借款", "Description" => "新手必看，借款攻略", "PicUrl" => $pic_url, "Url" => "http://mp.weixin.qq.com/s/JT6pCz2xbbjY3wkDo-pN9w");
//                $content[] = array("Title" => "哪里获得邀请码？", "Description" => "", "PicUrl" => "http://mp.yaoyuefu.com/images/dev/yqm.png", "Url" => "http://url.cn/dBW5Ru");
                //$content[] = array("Title"=>"世界那么大，我想去看看活动说明！ ", "Description"=>"", "PicUrl"=>"http://mp.yaoyuefu.com/images/dev/disantiao.png", "Url" =>"http://t.cn/RAj1cBB");
                break;
            case "SCAN":
                //$content = "欢迎到回到先花一亿元";
                break;
            case "CLICK":
                $info = $_SERVER;
                $model = new Statistics();
                $type = 6;
                $model->from = 'weixin';
                $model->remoteip = isset($info['HTTP_REMOTEIP']) ? $info['HTTP_REMOTEIP'] : 0;
                $model->user_agent = isset($info['HTTP_USER_AGENT']) ? $info['HTTP_USER_AGENT'] : 0;
                $model->create_time = date('Y-m-d H:i:s');
                $model->type = $type;

                $model->save();
                $content = array();
                $content[] = array("Title" => "常见问题", "Description" => "", "PicUrl" => "http://mp.yaoyuefu.com/images/dev/qa.png", "Url" => "http://t.cn/RAPu3QK");
                $content[] = array("Title" => "我想借钱", "Description" => "", "PicUrl" => "http://mp.yaoyuefu.com/images/dev/jq.png", "Url" => "http://t.cn/RAPurfR");
                $content[] = array("Title" => "我想投资", "Description" => "", "PicUrl" => "http://mp.yaoyuefu.com/images/dev/tz.png", "Url" => "http://t.cn/RAPugxZ");
                $content[] = array("Title" => "担保卡", "Description" => "", "PicUrl" => "http://mp.yaoyuefu.com/images/2140076529959284843.jpg", "Url" => "http://t.cn/RLiyied");
                $content[] = array("Title" => "担保卡投资", "Description" => "", "PicUrl" => "http://mp.yaoyuefu.com/images/2140076529959284843.png", "Url" => "http://www.urlpp.com/w5z");
                break;
        }

        $result = $this->transmitNews($object, $content);
        return $result;
    }

    //接收图片消息
    private function receiveImage($object) {
        $content = array("MediaId" => $object->MediaId);
        //	$result = $this->transmitImage($object, $content);
        $result = $this->duokefu($object, $content); //转接多客服
        return $result;
    }

    //接收语音消息
    private function receiveVoice($object) {
        $mark = '小主，抱歉~收不到语音的哦，请打字咨询哦';
        if (isset($object->Recognition) && !empty($object->Recognition)) {
            // $content = "你刚才说的是：" . $object->Recognition;
            $result = $this->transmitText($object, $mark);
            // $result = $this->duokefu($object, $content); //转接多客服
        } else {
            $content = array("MediaId" => $object->MediaId);
            // $result = $this->duokefu($object, $content); //转接多客服
            $result = $this->transmitText($object, $mark);
        }

        return $result;
    }

    /**
     * 判断关键词是否在输入的语句中
     */
    private function strstring($keyword, $reject = array()) {
        $mark = 0;
        foreach ($reject as $val) {
            if (strstr($keyword, $val)) {
                $mark = 1;
                break;
            }
        }
        return $mark;
    }

    /**
     * 判断语句是不是单独的数字
     */
    private function strnum($str) {
        $rule = '/^\d$/';
        $result = preg_match($rule, $str);
        return $result;
    }

    // public function actionTest(){
    //     print_r($this->strnum(12));
    // }

    /**
     * 判断语句是不是六位八位数字
     */
    private function strnums($str) {
        $rule = '/^\d{6}$/';
        $rule1 = '/^\d{8}$/';
        $result = preg_match($rule, $str);
        $result = preg_match($rule1, $str) || $result;
        return $result;
    }

    //接收文本消息
    private function receiveText($object) {
        $res = 0;
        //工作时间9：00-21：00
//        $now = date('Y-m-d H:i:s');
//        $starttime = date('Y-m-d 09:00:00');
//        if ($now > '2017-01-26 18:00:00' && $now < '2017-02-03 18:00:00') {
//            $endtime = date('Y-m-d 18:00:00');
//            $content = '您好，春节期间客服工作时间为：9:00-18:00，请您在工作期间内进行咨询，花二哥祝您新春快乐。';
//        } else {
//            $endtime = date('Y-m-d 21:00:00');
//            $content = '您好，人工客服时间：9:00-21:00 之间，可查看常见问题：http://www.xianhuahua.com/index/help 解决，若未解决，可在工作时间段内进行咨询。';
//        }
//        if ($now < $starttime || $now > $endtime) {
//            $res = 1;
//            $result = $this->transmitText($object, $content);
//        }
//        $time1_start = date('Y-m-d 12:00:00');
//        $time1_end = date('Y-m-d 13:30:00');
//        if ($now > $time1_start && $now < $time1_end) {
//            $res = 1;
//            $result = $this->transmitText($object, "你好，花二哥就餐，会在13:30之后准时接入哦。");
//        }
//        if ($now <= '2017-01-26 18:00:00' || $now >= '2017-02-03 18:00:00') {
//            $time2_start = date('Y-m-d 18:00:00');
//            $time2_end = date('Y-m-d 19:00:00');
//            if ($now > $time2_start && $now < $time2_end) {
//                $res = 1;
//                $result = $this->transmitText($object, "你好，花二哥就餐，会在19:00之后准时接入哦。");
//            }
//        }
//        $keyword = trim($object->Content);
//        $keys = array(
//            '0' => array('在', '在不在', '有人', '客户'),
//            '1' => array('如何借款', '我要借款', '怎么借款', '借钱给我', '借款流程', '借款攻略', '我要借钱', '怎么借钱', '借款给我', '借钱攻略', '攻略', '如何借钱'),
//            '2' => array('提额', '提高额度', '借款额度', '可借额度'),
//            '3' => array('审核时间多长', '审核多久', '审核多长', '多长时间审核', '为什么我的还在审核', '还在审核', '款多久到', '几点审核', '晚上审核吗', '审核是几点', '审核能快点', '加急审核', '加快审核', '快点审核', '快审核', '审核自拍照', '审核审核', '自拍照审核', '自拍照还在审核', '照片还在审核', '为什么还在审核', '快审核啊', '审核好慢', '审核加急', '能不能审核', '为啥还在审核', '审核好久了'),
//            '4' => array('借款驳回', '被驳回'),
//            '5' => array('自拍照认证', '自拍认证'),
//            '6' => array('手机无法认证'),
//            '7' => array('收不到短信', '收不到验证码'),
//            '8' => array('自拍照审核驳回', '视频无法认证'),
//            '9' => array('担保借款', '担保卡', '担保卡借款'),
//            '10' => array('冻结', '一把锁', '被锁了'),
//            '11' => array('补充资料填写'),
//            '12' => array('补充资料修改'),
//            '13' => array('银行卡绑卡失败', '绑卡失败', '绑不上卡'),
//            '14' => array('手机号更换'),
//            '15' => array('好友认证'),
//            '16' => array('赚钱妖怪'),
//            '17' => array('协商还款', '滞纳金太高', '逾期费用太高'),
//            '18' => array('注销'),
////            '19' => array('请尝试担保卡借款'),
//            '20' => array('请一周后再次尝试发起借款'),
//            '21' => array('借款电话审核未通过，请填写正确的联系人电话或工作电话后再次提交申请'),
//            '22' => array('请填写真实的个人资料'),
////            '23' => array('视频认证', '视频认证多次', '认证次数过多', '让联系客服', '视频', '联系客服'),
////            '24' => array('客服电话', '你们电话'),
////            '25' => array('不能申请', '不让申请', '二哥不受理', '二哥不给', '不能借款', '不给借款', '不让借款', '发不起借款', '不能发起', '不受理', '提示一周', '一周再发', '一周以后', '为什么一周', '为什么不能'),
////            '26' => array('自拍照不过', '照片不过', '照片驳回', '自拍照驳回', '怎么拍照', '自拍照'),
////            '27' => array('担保卡'),
////            '28' => array('收益'),
////            '29' => array('邀请码', '激情码'),
////            '30' => array('手机无法认证', '认证手机过不去', '手机认证失败', '服务密码错误', '运营商认证失败'),
////            '31' => array('自拍照怎么拍', '如何自拍', '怎么拍照'),
//        );
//        $array_mark = array(
//            '0' => array(0, '你好，有什么可以帮到你。'),
//            '1' => array(0, '登录先花一亿元，选择-我-右上角我的资料-完善借款必填项，完成后，点击发起借款：首次借款额度最高500-1000。'),
//            '2' => array(0, '首次借款额度最高500-1000，保持良好的还款记录，系统会自动提高。'),
//            '3' => array(0, '成功发起借款后，会在24小时之内完成审核，请耐心等待。'),
//            '4' => array(0, '1：确认账户栏内的资料里借款必填项（除学籍）都已经完善。2：确认首次借款额度最高500-1000 。3：确认所填资料信息都是真实可靠。'),
//            '5' => array(0, "两种认证方式：\na：微信公众号关注：先花一亿元， 登录先花一亿元，选择-我-右上角我的资料内自拍照，按照模特姿势进行上传，会在1-2个工作日完成审核。\nb : 下载先花一亿元app，登录app，选择-账户-我的资料-视频，按照提示进行认证，时时完成审核。"),
//            '6' => array(0, ' 手机号无法成功认证，若是密码错误，可以联系运营商。若是提示的无法采集，网络错误，建议更换时间段在尝试。'),
//            '7' => array(0, '你好，首先保证手机可以正常接收短信（没有超过当日受限），然后提供下手机号，在半个小时之后重新获取就可以了。'),
//            '8' => array(0, '多次驳回无法提交后，说明不符合一亿元的借款条件，所以无法完善，建议使用担保借款'),
//            '9' => array(0, '担保借款：推荐信用卡，微信登录一亿元账户栏-担保卡里面进行购买担保卡，1元=1点，购买成功到有担保点后，就可以发起-担保借款，担保借款最低200起，发起担保借款后，会在当天到账，最晚是在24小时之内，除了银行只收1%的通道费，不收取其它任何费用，担保借款成功后按时还款信用卡账单即可。'),
//            '10' => array(0, '系统检测到，您的个人信息不符合规则或在其它合作机构有不良记录，所以我们暂时无法为您解冻，抱歉~小主可以使用我们的新功能担保借款哦。'),
//            '11' => array(0, '在微信一亿元上，账户，最下方的补充资料，上传自己的身份证正、反面,以及本人与身份证的自拍合照（身份证挡住右眼拍照）保持全部信息清晰可见。'),
//            '12' => array(0, '暂时不可以哦，不过等我们的电话信审人员联系你的时候会引导你的哦，发起借款之后耐心等待就可以了。'),
//            '13' => array(0, '各别银行卡不支持，建议绑卡失败后可以更换其他银行的卡进行尝试。'),
//            '14' => array(0, '非常抱歉，现在暂时不支持更换，支持更换的时候会统一通知的哦。'),
//            '15' => array(0, '三个好友认证是可以提高借款审核通过率哦。'),
//            '16' => array(0, '小主点击推广赚钱，进入到赚钱妖怪，通过微信分享给自己的朋友，朋友注册成功之后，自拍照审核通过，小主可以获取五块钱哦，发起借款成功后，还会有额外收益哦。'),
//            '17' => array(0, '小主你好，逾期收到短信通知之后，可以直接拨打收到的短信上的电话：010-83488802，或者是直接添加QQ：1073006214沟通哦。'),
//            '18' => array(0, '非常抱歉小主，为防止欺诈用户在清除数据后再次注册，所以账号目前还无法注销，但一亿元正在努力成长中，若小主您有注销需求，等以后增加了注销功能后，我们会主动联系您哦. '),
////            '19' => array(0, '你好，你暂时不符合好友借款的要求，建议你可以尝试下我们的另一款借款产品，微信关注先花一亿元，登录我的账户，借款页面选择担保借款（推荐有信用卡的用户使用哦）。'),
//            '20' => array(0, '你好，系统监测到你近期发起借款太过频繁，建议你在一周之后重新发起借款哦。'),
//            '21' => array(0, '你好，保证你所填写的所有信息都是真实可靠，同时确保所填联系人电话都是可以正常通话哦，以上信息完善真实后，可以在一周之后再次发起借款。'),
//            '22' => array(0, '你好，你所填写的借款信息必须真实可靠，系统会自动识别其虚假信息，建议你完善自己的真实信息，在在一周之后再次发起借款。'),
////            '23' => array(0, '小主，app视频无法认证可以登陆微信端一亿元，  左下角点击进入我要借款，点击账户页面 ，点击右上角我的资料 ，选择自拍照认证既可。'),
////            '24' => array(0, '小主暂时未开通电话客服，你具体遇到什么问题，详细说明。'),
////            '25' => array(0, '小主你好：由于您近期操作过于频繁，或系统检测到您在多个平台发起借款，建议您一周后再来尝试哦，给您带来不便，请您谅解！'),
////            '26' => array(0, '小主你好，必须严格按照模特姿势拍照上传，系统会自动审核。'),
////            '27' => array(0, "担保卡：使用信用卡（或其他类型卡）在我的担保卡里面进行购买担保卡，1元=1点，最低100起，购买成功就可以发起担保借款，最低200，银行只收1%的通道费，担保借款可以不用还款，按时还款信用卡就可以哦。\n\n担保借款最晚会在24小时之内到账（周六、日，节假日到账会有所延迟）"),
////            '28' => array(0, '你好，由于第三方接口维护升级，暂时无法进行提现，维护时间参考产品提现时提示通知，给你带来不便请你谅解。'),
////            '29' => array(0, '小主你好，邀请码可以不用填写，直接跳过进行注册。'),
////            '30' => array(0, '小主你好，运营商无法成功验证，系统检测出你不符合借款要求，抱歉。'),
////            '31' => array(0, '小主你好，自拍照必须严格按照模特姿势拍照上传，若还是无法通过就是不符合借款条件。'),
//        );
//        foreach ($keys as $key => $val) {
//            $mark = $this->strstring($keyword, $val);
//            if ($mark == 1) {
//                $array_mark[$key][0] = 1;
//                break;
//            }
//        }
//        foreach ($array_mark as $key => $val) {
//            if ($val[0] == 1) {
//                $res = 1;
//                $result = $this->transmitText($object, $val[1]);
//                break;
//            }
//        }
//        //判断关键字是不是单独的数字
//        $result_1 = $this->strnum($keyword);
//        if ($result_1 == 1) {
//            $res = 0;
//            $continue = '';
//            switch ($keyword) {
//                case 1:$continue = "资料已经完善后发起借款，借款审核会在24小时之内完成。";
//                    break;
//                case 2:$continue = "两种认证方式都可以\n\nAPP端视频认证：实时通过审核，若是认证次数过多后无法再次认证，可以使用微信端进行自拍照认证\n\n微信端自拍照认证：按照模特姿势上传自己的自拍照，会在24小时之内完成审核\n\n若是app端跟微信端都无法完成认证，那么就是不符合一亿元的借款条件，无法使用";
//                    break;
//                case 3:$continue = "线上、线下两种还款方式\n\n线上还款：登陆微信或app，在借款页面直接点击我要还款，进行操作就可以。\n\n线下还款：在微信公众号上登陆一亿元，在借款页面，点击还款选择右下角线下还款，通过支付宝或是手机网银转账到对公账户，然后截图还款凭证，在还款页面添加上传即可。\n\n若以上还款都无法正常进行（或其他还款问题），请回复“9”咨询人工客服";
//                    break;
//                case 4:$continue = "登陆账户，在我的资料里面，查看借款记录，打开当前被驳回的借款，查看借款理由。将借款理由输入到先花一亿元微信公众号即可。";
//                    break;
//                case 5:$continue = "新用户首次借款额度均为1000元，使用后最高可借 1万元\n保持良好的还款记录大于三次。\n邀请超过10个以上的好友在先花一亿元上对你进行认证。\n系统会在每个月进行一次借款评估，符合要求就会提额哦。";
//                    break;
//                case 6:$continue = "逾期收到短信通知后，（周一到周六早上10:00-18:00）拨打电话：010-83488802，或添加QQ：1073006214进行沟通";
//                    break;
//                case 7:$continue = "手机号无法成功认证，若是密码错误，可以联系运营商。若是提示的无法采集，网络错误，建议更换时间段在尝试。";
//                    break;
//                case 8:$continue = "苹果手机---参与方式：\n第一步：完成五星好评\n第二步：打开App Store,点击”Apple ID“\n第三步：点击”查看Apple ID“\n第四步：点击“评分与评论”并将评论截图\n第五步：将评论截图及手机号码发送至先花一亿元微信公众号\n\n安卓手机---参与方式：\n第一步：完成5星好评\n第二步：找到“我的好评”并截图\n第三步：将评论截图及手机号码发送至先花一亿元微信公众号";
//                    break;
//            }
//            if (!empty($continue)) {
//                $result = $this->transmitText($object, $continue);
//                $res = 1;
//            }
//        }
//        //判断关键字是不是六位或八位纯数字
//        $result_2 = $this->strnums($keyword);
//        if ($result_2 == 1) {
//            $res = 1;
//            $result = $this->transmitText($object, '小主你好：获取到邀请码之后，进入先花一亿元微信，点击打开一亿元我要借款，输入邀请码进行注册就可以发起借款。');
//        }

        if ($res == 0) {
            $content = trim($object->Content);
            $result = $this->duokefu($object, $content); //转接多客服
        }
        return $result;
        /* $keyword = trim($object->Content);
          if (strstr($keyword, "文本")){
          $content = "这是个文本消息";
          }else if (strstr($keyword, "单图文")){
          $content = array();
          $content[] = array("Title"=>"单图文标题",  "Description"=>"单图文内容", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
          }else if (strstr($keyword, "图文") || strstr($keyword, "多图文")){
          $content = array();
          $content[] = array("Title"=>"多图文1标题", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
          $content[] = array("Title"=>"多图文2标题", "Description"=>"", "PicUrl"=>"http://d.hiphotos.bdimg.com/wisegame/pic/item/f3529822720e0cf3ac9f1ada0846f21fbe09aaa3.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
          $content[] = array("Title"=>"多图文3标题", "Description"=>"", "PicUrl"=>"http://g.hiphotos.bdimg.com/wisegame/pic/item/18cb0a46f21fbe090d338acc6a600c338644adfd.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
          }else if (strstr($keyword, "音乐")){
          $content = array();
          $content = array("Title"=>"最炫民族风", "Description"=>"歌手：凤凰传奇", "MusicUrl"=>"http://121.199.4.61/music/zxmzf.mp3", "HQMusicUrl"=>"http://121.199.4.61/music/zxmzf.mp3");
          }else{
          $content = date("Y-m-d H:i:s",time())."\n".$object->Content;
          }
          if(is_array($content)){
          if (isset($content[0])){
          $result = $this->transmitNews($object, $content);
          }else if (isset($content['MusicUrl'])){
          $result = $this->transmitMusic($object, $content);
          }
          }else{
          $result = $this->transmitText($object, $content);
          }
         */
        //else{
        //$content = trim($object->Content);
        //$result = $this->duokefu($object, $content);//转接多客服   
        //}
    }

    //转到多客服
    private function duokefu($object, $content) {
        $xmlTpl = " <xml>
     <ToUserName><![CDATA[%s]]></ToUserName>
     <FromUserName><![CDATA[%s]]></FromUserName>
     <CreateTime>%s</CreateTime>
     <MsgType><![CDATA[transfer_customer_service]]></MsgType>
 </xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复文本消息
    private function transmitText($object, $content) {
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    //回复图文消息
    private function transmitNews($object, $newsArray) {
        if (!is_array($newsArray)) {
            return;
        }
        $itemTpl = "        <item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
        </item>
";
        $item_str = "";
        foreach ($newsArray as $item) {
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[news]]></MsgType>
    <ArticleCount>%s</ArticleCount>
    <Articles>
$item_str    </Articles>
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }

    //回复图片消息
    private function transmitImage($object, $imageArray) {
        $itemTpl = "<Image>
        <MediaId><![CDATA[%s]]></MediaId>
    </Image>";

        $item_str = sprintf($itemTpl, $imageArray['MediaId']);

        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[image]]></MsgType>
    $item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($imageArray));

        return $result;
    }

    //回复音乐消息
    private function transmitMusic($object, $musicArray) {
        $itemTpl = "<Music>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <MusicUrl><![CDATA[%s]]></MusicUrl>
        <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
    </Music>";

        $item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);

        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[music]]></MsgType>
    $item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //关注推送客服消息
    private function subscribe($object, $content) {
        $access_token = $this->getAccessToken();
        //$openid= 'oW_ojuBBUqKJVMKFI2GisrwIEJ7I';
        //$content = "欢迎关注先花一亿元";
        $openid = $object->FromUserName;
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $this->getAccessToken();
        $data = '{
			    "touser":"' . $openid . '",
			    "msgtype":"text",
			    "text":
			    {
			         "content":"' . $content . '"
			    }
				}';

        $result = Http::dataPost($data, $url);
        Logger::errorLog(print_r($result, true), 'subscribe');
    }

}
