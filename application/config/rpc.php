<?php defined('SYSPATH') or die('No direct script access.');

return array
(
   //后端用户名验证
	'certify' => array(
		'type' => RPC_TYPE_HTTP,//接口类型，http类型
		'server' => array(
			array('host' => '61.172.241.94', 'port' => 8083),
		)
	),
	//后端用户名验证
	'bulletin' => array(
		'type' => RPC_TYPE_HTTP,//接口类型，http类型
		'server' => array(
			array('host' => 'boke.dev.ku6.com', 'port' => 14974),
		)
	),
	'shengpay' => array(
		'type' => RPC_TYPE_HTTP,//接口类型，http类型
		'server' => array(
			array('host' => 'netpay.sdo.com', 'port' => 80),
		)
	),
);
