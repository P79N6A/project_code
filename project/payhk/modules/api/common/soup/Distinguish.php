<?php
/**
 * 用于识别存在云端的静态身份证图片上的文字信息。
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/16
 * Time: 16:13
 */
namespace app\modules\api\common\soup;

use app\common\Logger;
use app\models\SoupOcrIdcard;
use yii\helpers\ArrayHelper;

class Distinguish
{
    const PIC_URL = "https://v2-auth-api.visioncloudapi.com/ocr/idcard";

    public function inspectImage($params)
    {
        //1.获取图片id
        $image_id = ArrayHelper::getValue($params, 'image_id');
        if (empty($image_id)){
            return $this->returnMessage("3001");
        }
        //2.生成签名
        $oAutograph = new Autograph();
        $auth = $oAutograph->make();
        //2.请求第三方
        $post_data = array ('image_id'=>$image_id);
        $result = $this->sendHttp($auth, $post_data);
        $code = ArrayHelper::getValue($result, 'code');
        if ($code == 1000) {
            //3.保存数据
            $oSoupOcrIdcard = new SoupOcrIdcard();
            $save_bool = $this->saveData($oSoupOcrIdcard, $image_id, $result);
            if ($save_bool){
                return $this->successMessage($result);
            }
            return $this->returnMessage("3002");

        }
        return $this->returnMessage($code);

    }

    private function successMessage($result)
    {
        $info = ArrayHelper::getValue($result, 'info');
        $validity = ArrayHelper::getValue($result, 'validity');
        return [
            'code'  => 0000,
            'msg'   => [
                'side'                  => ArrayHelper::getValue($result, 'side'),
                'info_name'             => ArrayHelper::getValue($info, 'name'),
                'info_gender'           => ArrayHelper::getValue($info, 'gender'),
                'info_nation'           => ArrayHelper::getValue($info, 'nation'),
                'info_year'             => ArrayHelper::getValue($info, 'year'),
                'info_month'            => ArrayHelper::getValue($info, 'month'),
                'info_day'              => ArrayHelper::getValue($info, 'day'),
                'info_address'          => ArrayHelper::getValue($info, 'address'),
                'info_number'           => ArrayHelper::getValue($info, 'number'),
                'validity_name'         => ArrayHelper::getValue($validity, 'name'),
                'validity_gender'       => ArrayHelper::getValue($validity, 'gender'),
                'validity_address'      => ArrayHelper::getValue($validity, 'address'),
                'validity_number'       => ArrayHelper::getValue($validity, 'number'),
                'validity_birthday'     => ArrayHelper::getValue($validity, 'birthday'),
                'type'                  => ArrayHelper::getValue($result, 'type'),
                'request_id'            => ArrayHelper::getValue($result, 'request_id'),
            ],
        ];
    }

    private function saveData(SoupOcrIdcard $oSoupOcrIdcard, $image_id, $params)
    {
        if (empty($params) || empty($image_id)){
            return false;
        }
        $info = ArrayHelper::getValue($params, 'info', '');
        $save_data = [
                'image_id'              => $image_id, //图片id
                'request_id'            => ArrayHelper::getValue($params, 'request_id'), //本次请求的id
                'code'                  => (string)ArrayHelper::getValue($params, 'code'), //响应状态
                'message'               => ArrayHelper::getValue($params, 'message'), //响应信息
                'side'                  => ArrayHelper::getValue($params, 'side'), //front 代表身份证正面，back 代表身份证反面
                'name'                  => ArrayHelper::getValue($info, 'name'), //姓名
                'number'                => ArrayHelper::getValue($info, 'number'), //身份证号
                'info'                  => json_encode($info), //身份证文字信息
                'validity'              => json_encode(ArrayHelper::getValue($params, 'validity')), //各项信息有效性
                'type'                  => ArrayHelper::getValue($params, 'type'), //身份证来源类型：normal 正常拍摄，photocopy 复印件， ps PS， reversion 屏幕翻拍， other 其他， unknown 未知（识别失败',

        ];
        return $oSoupOcrIdcard -> saveData($save_data);
    }

    private function sendHttp($auth, $post_data)
    {
        $ch = curl_init();
        //请将AUTHORIZATION替换为根据API_KEY和API_SECRET得到的签名认证串
        $Authorization = $auth;
        #echo $Authorization;
        $header[] = 'Authorization: '.$Authorization;
        Logger::dayLog("soup/distinguish", "请求参数：", json_encode($post_data));
        Logger::dayLog("soup/distinguish", "签名认证：", json_encode($header));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, self::PIC_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        //打开SSL验证时，需要安装openssl库。也可以选择关闭，关闭会有风险。
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        Logger::dayLog("soup/distinguish", "返回值：", $output);
        $output_array = json_decode($output,true);
        curl_close($ch);
        return $output_array;
    }

    /**
     * 返回错误信息
     * @param $code
     * @return array
     */
    private function returnMessage($code)
    {
        $errorCode = $this->errorCode();
        return ['code'=>$code, 'msg'=>ArrayHelper::getValue($errorCode, $code, '未定义错误')];
    }

    /**
     * 错误码
     * @return array
     */
    private function errorCode()
    {
        return [
            //系统响应码 code 说明
            '1100'			=> '账号密码不匹配',
            '1200'			=> '输入参数无效',
            '1101'			=> '账号过期',
            '1103'			=> '该接口没有权限',
            '1002'			=> '使用频率超过限制',
            '2000'			=> '资源没找到',
            '4001'			=> '身份证服务失败',
            //http状态码
            '400'			=> 'BAD_REQUEST',
            '404'			=> 'NOT_FOUND',
            '411'			=> 'LENGTH_REQUIRED',
            '413'			=> 'PAYLOAD_TOO_LARGE',
            '500'			=> 'INTERNAL_ERROR',
            //自定义
            '3001'          => '图片id不能为空',
            '3002'          => '信息保存失败',
        ];
    }
}