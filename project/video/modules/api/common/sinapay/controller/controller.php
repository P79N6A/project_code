<?php
    /**
     * Created by PhpStorm.
     * User: linwei
     * Date: 16/4/11
     * Time: 16:46
     * demo只是提供一个接口对接编写的思路，具体接口对接商户技术以自身项目的实际情况来进行接口代码的编写。
     */
    @include_once(dirname ( __File__ ) . "/controller_sina.php");
    $sina = new controller_sina();
    switch ($_POST['service']) {
        //创建激活会员
        case 'create_activate_member':
            $result = $sina->create_activate_member($_POST);
            print_r($result);
            exit;
            break;
        //设置实名信息
        case 'set_real_name':
            $result = $sina->set_real_name($_POST);
            print_r($result);
            exit;
            break;
        //绑定认证信息
        case 'binding_verify':
            $result = $sina->binding_verify($_POST);
            print_r($result);
            exit;
            break;
        //解绑认证信息
        case 'unbinding_verify':
            $result = $sina->unbinding_verify($_POST);
            print_r($result);
            exit;
            break;
        //查询认证信息
        case 'query_verify':
            $result = $sina->query_verify($_POST);
            print_r($result);
            exit;
            break;
        //绑定银行卡
        case 'binding_bank_card':
            $result = $sina->binding_bank_card($_POST);
            echo json_encode($result);
            exit;
            break;
        //绑定银行卡推进
        case 'binding_bank_card_advance':
            $result = $sina->binding_bank_card_advance($_POST);
            print_r($result);
            exit;
            break;
        //解绑银行卡
        case 'unbinding_bank_card':
            $result = $sina->unbinding_bank_card($_POST);
            print_r($result);
            exit;
            break;
        //查询我的银行卡
        case 'query_bank_card':
            $result = $sina->query_bank_card($_POST);
            print_r($result);
            exit;
            break;
        //查询余额/基金份额
        case 'query_balance':
            $result = $sina->query_balance($_POST);
            print_r($result);
            exit;
            break;
        //查询收支明细接口
        case 'query_account_details':
            $result = $sina->query_account_details($_POST);
            print_r($result);
            exit;
            break;
        //冻结余额
        case 'balance_freeze':
            $result = $sina->balance_freeze($_POST);
            print_r($result);
            exit;
            break;
        //解冻余额
        case 'balance_unfreeze':
            $result = $sina->balance_unfreeze($_POST);
            print_r($result);
            exit;
            break;
        //请求企业会员资质审核
        case 'audit_member_infos':
            $result = $sina->audit_member_infos($_POST);
            print_r($result);
            exit;
            break;
        //查询企业会员信息
        case 'query_member_infos':
            $result = $sina->query_member_infos($_POST);
            print_r($result);
            exit;
            break;
        //查询企业会员审核结果
        case 'query_audit_result':
            $result = $sina->query_audit_result($_POST);
            print_r($result);
            exit;
            break;
        //sina页面展示用户信息
        case 'show_member_infos_sina':
            $result = $sina->show_member_infos_sina($_POST);
            echo json_encode($result);
            exit;
            break;
        //查询冻结解冻结果
        case 'query_ctrl_result':
            $result = $sina->query_ctrl_result($_POST);
            print_r($result);
            exit;
            break;
        //经办人信息
        case 'smt_fund_agent_buy':
            $result = $sina->smt_fund_agent_buy($_POST);
            print_r($result);
            exit;
            break;
        //查询中间账户余额
        case 'query_middle_account':
            $result = $sina->query_middle_account($_POST);
            print_r($result);
            exit;
            break;
        //解绑银行卡推进接口
        case 'unbinding_bank_card_advance':
            $result = $sina->unbinding_bank_card_advance($_POST);
            print_r($result);
            exit;
            break;
        //修改，设置，找回支付密码
        case 'set_pay_password':
            $result = $sina->all_pay_password($_POST);
            print_r($result);
            exit;
            break;
        case 'set_pay_password':
            $result = $sina->all_pay_password($_POST);
            print_r($result);
            exit;
            break;
        case 'modify_pay_password':
            $result = $sina->all_pay_password($_POST);
            print_r($result);
            exit;
            break;
        case 'find_pay_password':
            $result = $sina->all_pay_password($_POST);
            print_r($result);
            exit;
            break;
        //查询是否设置了支付密码
        case 'query_is_set_pay_password':
            $result = $sina->query_is_set_pay_password($_POST);
            print_r($result);
            exit;
            break;
        //修改认证手机号，找回认证手机号
        case 'modify_verify_mobile':
            $result = $sina->all_verify_mobile($_POST);
            print_r($result);
            exit;
            break;
        //找回手机号
        case 'find_verify_mobile':
            $result = $sina->all_verify_mobile($_POST);
            print_r($result);
            exit;
            break;
        //查询经办人信息
        case 'query_fund_agent_buy':
            $result = $sina->query_fund_agent_buy($_POST);
            print_r($result);
            exit;
            break;
        //我的银行卡
        case 'web_binding_bank_card':
            $result = $sina->web_binding_bank_card($_POST);
            print_r($result);
            exit;
            break;
        //创建托管代收接口
        case 'create_hosting_collect_trade':
            $result = $sina->create_hosting_collect_trade($_POST);
            print_r($result);
            exit;
            break;
        //创建托管代付交易
        case 'create_single_hosting_pay_trade':
            $result = $sina->create_single_hosting_pay_trade($_POST);
            print_r($result);
            exit;
            break;
        //创建批量托管代付交易
        case 'create_batch_hosting_pay_trade':
            $result = $sina->create_batch_hosting_pay_trade($_POST);
            print_r($result);
            exit;
            break;
        //托管交易支付
        case 'pay_hosting_trade':
            $result = $sina->pay_hosting_trade($_POST);
            print_r($result);
            exit;
            break;
        //托管交易查询
        case 'query_hosting_trade':
            $result = $sina->query_hosting_trade($_POST);
            print_r($result);
            exit;
            break;
        //托管交易批次查询
        case 'query_hosting_batch_trade':
            $result = $sina->query_hosting_batch_trade($_POST);
            print_r($result);
            exit;
            break;
        //托管退款
        case 'create_hosting_refund':
            $result = $sina->create_hosting_refund($_POST);
            print_r($result);
            exit;
            break;
        //托管退款查询
        case 'query_hosting_refund':
            $result = $sina->query_hosting_refund($_POST);
            print_r($result);
            exit;
            break;
        //托管充值
        case 'create_hosting_deposit':
            $result = $sina->create_hosting_deposit($_POST);
            print_r($result);
            exit;
            break;
        //托管充值查询
        case 'query_hosting_deposit':
            $result = $sina->query_hosting_deposit($_POST);
            print_r($result);
            exit;
            break;
        //托管提现
        case 'create_hosting_withdraw':
            $result = $sina->create_hosting_withdraw($_POST);
            print_r($result);
            exit;
            break;
        //托管提现查询
        case 'query_hosting_withdraw':
            $result = $sina->query_hosting_withdraw($_POST);
            print_r($result);
            exit;
            break;
        //托管转账
        case 'create_hosting_transfer':
            $result = $sina->create_hosting_transfer($_POST);
            print_r($result);
            exit;
            break;
        //支付推进请求
        case 'advance_hosting_pay':
            $result = $sina->advance_hosting_pay($_POST);
            print_r($result);
            exit;
            break;
        //创建单笔代付到提现卡
        case 'create_single_hosting_pay_to_card_trade':
            $result = $sina->create_single_hosting_pay_to_card_trade($_POST);
            print_r($result);
            exit;
            break;
        //批量代付到提现卡
        case 'create_batch_hosting_pay_to_card_trade':
            $result = $sina->create_batch_hosting_pay_to_card_trade($_POST);
            print_r($result);
            exit;
            break;
        //代收完成交易
        case 'finish_pre_auth_trade':
            $result = $sina->finish_pre_auth_trade($_POST);
            print_r($result);
            exit;
            break;
        //代收撤销交易
        case 'cancel_pre_auth_trade':
            $result = $sina->cancel_pre_auth_trade($_POST);
            print_r($result);
            exit;
            break;
        //货币基金收益率查询
        case 'query_fund_yield':
            $result = $sina->query_fund_yield($_POST);
            print_r($result);
            exit;
            break;
        //查询标的信息
        case 'query_bid_info':
            $result = $sina->query_bid_info($_POST);
            print_r($result);
            exit;
            break;
        //标的录入接口
        case 'create_bid_info':
            $result = $sina->create_bid_info($_POST);
            print_r($result);
            exit;
            break;
        //托管交易支付结果查询
        case 'query_pay_result':
            $result=$sina->query_pay_result($_POST);
            print_r($result);
            exit;
            break;
        default:
            print_r($_POST);
            echo "demo服务接口不正确";
            exit;
    }
?>