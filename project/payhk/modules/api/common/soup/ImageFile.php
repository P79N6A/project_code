<?php
/**
 * 将某url上的图片上传到云端。
 * 图片必须满足如下条件：
 *  1.格式必须为 JPG（JPEG），BMP，PNG，GIF，TIFF 之一
 *  2.宽和高必须大于 8px，小于等于 4000px
 *  3.文件尺寸小于等于 5 MB
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/16
 * Time: 14:04
 */

namespace app\modules\api\common\soup;


use app\common\Logger;
use app\models\SoupPic;
use yii\helpers\ArrayHelper;

class ImageFile
{
    const IMAGE_URL = "https://v2-auth-api.visioncloudapi.com/resource/image/url";

    public function fileUp($params)
    {
        //1.图片地址
        $pic_file_path = ArrayHelper::getValue($params, 'pic_file_path');
        //2.图片类型 1正面  2反面
        $pic_type = ArrayHelper::getValue($params, 'pic_type', 1);
        //3. 判断图片地址是否存在
        if (empty($pic_file_path)){
            return $this->returnMessage("3000");
        }
        //4.查看是否存在记录
        $oSoupPic = new SoupPic();
        $pic_info = $oSoupPic->getData($pic_file_path, $pic_type);
        if (empty($pic_info)){
            //5.记录到数据表中
            $pic_bool = $this->saveData($oSoupPic, $params);
            if (!$pic_bool){
                return $this->returnMessage("3001");
            }
            $pic_info = $oSoupPic;
        }
        //6.生成签名
        $oAutograph = new Autograph();
        $auth = $oAutograph->make();
        //7.请求第三方 上传到商汤云端
        $post_data = array ('data'=>$pic_file_path);
        $result = $this->sendHttp($auth, $post_data);
        //$result = array ( 'code' => 1103, 'message' => 'url no permission', 'request_id' => '89bfa582f4bf4da3988b7d866d9db8de', );
        //更新到表里
        $update_bool = $this->updateData($pic_info, $result);
        if (!$update_bool){
            return $this->returnMessage("3002");
        }
        //判断是否存功
        $code = ArrayHelper::getValue($result, "code", 0);
        if ($code == 1000){
            return $this->successMessage($result);
        }
        return $this->returnMessage($code);

    }

    private function successMessage($result)
    {
        return [
            'code'  => 0000,
            'msg'   => [
                'request_id'        => ArrayHelper::getValue($result, 'request_id'),
                'pic_id'            => ArrayHelper::getValue($result, 'id')
            ],
        ];
    }

    private function sendHttp($auth, $post_data)
    {
        $ch = curl_init();
        //请将AUTHORIZATION替换为根据API_KEY和API_SECRET得到的签名认证串
        $Authorization = $auth;
        //echo $Authorization;
        $header[] = 'Authorization: '.$Authorization;
        Logger::dayLog("soup/image_file", "请求参数：", json_encode($post_data));
        Logger::dayLog("soup/image_file", "签名认证：", json_encode($header));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, self::IMAGE_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        //打开SSL验证时，需要安装openssl库。也可以选择关闭，关闭会有风险。
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        Logger::dayLog("soup/image_file", "返回值：", $output);
        $output_array = json_decode($output,true);
        curl_close($ch);
        return $output_array;
    }

    /**
     * 错误码
     * @return array
     */
    private function errorCode()
    {
        return [
            // 系统响应码 code 说明:
            '1100'      => '账号密码不匹配',
            '1200'      => '输入参数无效',
            '1101'      => '账号过期',
            '1103'      => '该接口没有权限',
            '1002'      => '使用频率超过限制',
            '2001'      => '下载超时',
            '2002'      => '下载出错',
            '2003'      => '图片尺寸不符合要求',
            '2004'      => '图片体积不符合要求',
            '2005'      => '图片类型不符合要求',
            '2006'      => '图片损坏',
            //http状态码：
            '400'       => 'BAD_REQUEST',
            '404'       => 'NOT_FOUND',
            '411'       => 'LENGTH_REQUIRED',
            '413'       => 'PAYLOAD_TOO_LARGE',
            '500'       => 'INTERNAL_ERROR',
            //自定义
            '3000'      => '上传地址不能为空',
            '3001'      => '记录失败',
            '3002'      => '修改失败',
        ];
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
    
    private function saveData(SoupPic $oSoupPic, $params)
    {
        if (empty($params)){
            return false;
        }
        $save_data = [
            'pic_file_path'     => ArrayHelper::getValue($params, 'pic_file_path'),
            'pic_type'          => ArrayHelper::getValue($params, 'pic_type', 1),
        ];
        return $oSoupPic-> saveData($save_data);
    }

    private function updateData($oSoupPic, $params)
    {
        if (empty($params)){
            return false;
        }
        $update_data = [
            'code'          => (string)ArrayHelper::getValue($params, "code"),
            'message'       => ArrayHelper::getValue($params, "message"),
            'request_id'    => ArrayHelper::getValue($params, "request_id"),
            'pic_id'    => ArrayHelper::getValue($params, "id"),
        ];
        return $oSoupPic->updateData($update_data);
    }

}