<?php defined('SYSPATH') or die('No direct script access.');

define('PAYMENT_TYPE_ALIPAY', 0);
define('PAYMENT_TYPE_SHENGPAY', 1);
/**
 * 盛付通直连配置，PayChannel只能有一个值，BankCode必须有值，选择的银行
 */
return array
(
	'shengpay' => array(
		'type' => PAYMENT_TYPE_SHENGPAY,//接口类型，盛付通
		'options' => array(
			'service' => 'default',
			'key' => 'abcdefg', //签名串
			'MerchantNo' => '715226', //商户号967157
			'PayChannel' => '03,04,07,14,18',//支付渠道为两位数字，增加多个渠道请用逗号“，”隔开。03,04,12,13,14
			'CurrencyType' => 'RMB', //货币类型
			'SignType' => '2', // 签名类型1:RSA 2:MD5 3:PKI
			'NotifyUrl' => 'http://10.241.73.49/i_md5_prj/Notify.php', //回调地址
			'PostBackUrl' => 'http://10.241.73.49/i_md5_prj/Notify.php', //支付完成跳转地址
			'DefaultChannel' => '04', //默认跳转到的银行
		)
	),
	
	'alipay' => array(
		'type' => PAYMENT_TYPE_ALIPAY,//接口类型，支付宝类型
		'options' => array(
			'service' => 'create_direct_pay_by_user',//接口名称
			'partner' => '2088101568338364',//合作者身份ID，商户号
			'key' => '12345',//安全校验码
			'seller_email' => 'taobao@taobao.com',//卖家支付宝账号
			'error_notify_url' => '',//请求出错时的通知页面路径
			'notify_url' => '',//服务器异步通知页面路径
			'return_url' => '',//支付完成跳转地址
			'sign_type' => 'MD5',//签名方式, DSA、RSA、MD5三个值可选，必须大写。
		)
	),
	
	'directpay' => array(
		'type' => PAYMENT_TYPE_SHENGPAY,//接口类型，盛付通类型
		'options' => array(
			'service' => 'ibankpay',
			'key' => 'abcdefg', //签名串
			'MerchantNo' => '715226', //商户号967157
			'PayChannel' => '04',//支付渠道为两位数字，增加多个渠道请用逗号“，”隔开。03,04,12,13,14
			'CurrencyType' => 'RMB', //货币类型
			
			'SignType' => '2', // 签名类型1:RSA 2:MD5 3:PKI, 仅支持md5
			'NotifyUrl' => 'http://10.241.73.49/i_md5_prj/Notify.php', //回调地址
			'PostBackUrl' => 'http://10.241.73.49/i_md5_prj/Notify.php', //支付完成跳转地址
			'DefaultChannel' => '04',
		)
	),
);