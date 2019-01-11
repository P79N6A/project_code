<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/18
 * Time: 16:37
 */
namespace app\modules\bankauth\common\xinyan;


class XyApi
{
    /**
     * 获取配置文件
     * @param $cfg
     * @return mixed
     * @throws \Exception
     */
    public function getConfig($cfg)
    {
        $configPath = __DIR__ . DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."{$cfg}.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }

    /**
     * 判断是否为空
     * @param $params
     * @param $checkparms
     * @return bool
     */
    public function checkEmpey($params, $checkparms)
    {
        if (empty($params) || empty($checkparms)){
            return false;
        }
        foreach($checkparms as $value){
            if (empty($params[$value])){
                return false;
            }
        }
        return true;
    }

    /**
     * 错误码
     * @return array
     */
    public function errorMsg()
    {
        return [
            'S0001'    => '系统繁忙，请稍后再试',
            'S1000'    => '请求参数有误（具体以响应参数错误为准）',
            'S1001'    => '请求订单不存在或已过期，请重新发起交易',
            'S1002'    => '请求订单已受理，请稍后查询交易结果',
            'S1003'    => '订单不能重复提交',
            'S1004'    => '请求订单创建失败',
            'S1005'    => '请求报文解析失败',
            'S1006'    => '请求报文加密数据处理失败',
            'S1007'    => '请求明文数据与密文数据不一致',
            'S1010'    => '短信验证失败，请重新发起交易',
            'S1011'    => '短信发送次数超限，请重新发起交易',
            'S1012'    => '短信验证码错误',
            'S1013'    => '短信验证次数超限，请重新发起交易',
            'S1014'    => '短信验证码已过期，请重新发起交易',
            'S1015'    => '未找到卡 bin 信息',
            'S1016'    => '暂不支持该银行卡校验',
            'S2000'    => '商户不存在',
            'S2001'    => '商户状态异常',
            'S2002'    => '商户终端信息不存在',
            'S2003'    => '商户终端信息状态异常',
            'S2004'    => '商户暂不支持该产品',
            'S2005'    => '商户暂不支持该功能',
            'S2006'    => '商户余额不足',
        ];
    }

    /**
     * 机构响应码
     */
    public function responseCode()
    {
        return [
            '0001'    => '持卡人身份信息有误',
            '0002'    => '持卡人账号信息和身份信息不匹配',
            '0003'    => '持卡人账户信息有误',
            '0004'    => '该卡已被注销',
            '0005'    => '该卡已冻结，请联系发卡行',
            '0006'    => '该卡已挂失',
            '0007'    => '该卡有风险',
            '0008'    => '交易繁忙，请稍后再试',
            '0009'    => '卡号无效，请确认后输入',
            '0010'    => '卡状态异常，请联系发卡行',
            '0012'    => '请联系银行核实您的卡状态是否正常！',
            '0014'    => '认证不一致（身份证号有误）',
            '0015'    => '认证不一致（手机号有误）',
            '0016'    => '认证不一致（姓名有误）',
            '0017'    => '认证失败',
            '0018'    => '手机号码为空，请重新输入',
            '0019'    => '系统异常，请稍后再试',
            '0020'    => '银行卡已过有效期',
            '0021'    => '银行卡异常',
            '0022'    => '该卡验证错误次数超限，请隔日再试',
            '0023'    => '记录失败',
            '0024'    => '参数不能为空！',
            '0025'    => '引入配置文件失败',
            '0026'    => '重复提交，请稍后重试',
        ];
    }
}