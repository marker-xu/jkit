<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Pay extends Controller {
  public function action_index(){
    $arrPayTypes = array(
    	'alipay'=>'支付宝',
    	'shengpay'=>'盛付通',
    	'directpay'=>'盛付通直连'
    );
    
    $this->page->data['type_list'] = $arrPayTypes;
    $this->template->set('type_list', $arrPayTypes);
  }
  
  public function action_topay() {
  	
  	$strPayType = "alipay";
    $arrInput = array(
    	"return_url"  => "http://127.0.0.1",
	   	"body"  => "支付吧",
	   	"show_url"  => "",
    );
//    $strPayType = "directpay";
//    $strPayType = "shengpay";
//    $arrInput = array(
//		"PostBackUrl" => "http://localhost/i_md5_prj/PostBack.php",
//		"BackUrl" => "http://localhost/i_md5_prj/back.php",
//		"ProductDesc" => "this is a product!",
//    'Remark1' => 'remark1',
//  'Remark2' => 'remark2',
//  'BankCode' => 'CMB',
//  'DefaultChannel' => '04',
////    'PayChannel' => '04',
//    );
    Payment::topay($strPayType, 0.01, "12345", "123123213", $arrInput);
  }
  
  public function action_getparam() {
  	$strPayType = "alipay";
    $arrInput = array(
    	'service' => 'create_direct_pay_by_user',
		 'payment_type' => '',
		 'partner' => '2088101568338364',
		 '_input_charset' => '',
		 'seller_email' => 'taobao@taobao.com',
		 'return_url' => 'http://127.0.0.1',
		 'notify_url' => '',
		 'out_trade_no' => "12345",
		 'subject' => 'kaka',
		 'body' => '支付吧',
		 'total_fee' => 125,
		 'paymethod' => 'directPay',
		 'show_url' => '',
	);
//    $strPayType = "shengpay";
//   $arrInput = array(
//    	"Amount" => 0.01,
//		"OrderNo" => 2011101703,
//		"PostBackUrl" => "http://localhost/i_md5_prj/PostBack.php",
//		"BackUrl" => "http://localhost/i_md5_prj/back.php",
//		"OrderTime" => '20111017030834',
//		"ProductNo" => "123123213",
//		"ProductDesc" => "this is a product!",
//    'Remark1' => 'remark1',
//  'Remark2' => 'remark2',
//  'BankCode' => 'SDTBNK',
//  'DefaultChannel' => '04'
//    );
    print_r( Payment::getPayParam($strPayType, $arrInput) );
  }
  
  public function action_verify() {
  	$strPayType = "ralipay";
    $arrInput = array(
    	"is_success" => "T", 
		"sign" => "be8194df0a260b642a4690063fdb6673", 
		"sign_type" => "MD5", 
    	"out_trade_no" => "6402757654153618",
    	"subject" => "手套",
    	"payment_type" => 1,
    	"exterface" => "create_direct_pay_by_user",
    	"trade_no" => "2008102303210710", 
    
    	"trade_status" => "TRADE_FINISHED",
    	"notify_id" => "RqPnCoPT3K9%2Fvwbh3I%2BODmZS9o4qChHwPWbaS7UMBJpUnBJlzg42y9A8gQlzU6m3fOhG", 
    	"notify_time" => "2008-10-23 13:17:39", 
   		"notify_type" => "trade_status_sync", 
    	"seller_email" => "chao.chenc1@alipay.com", 
    	"buyer_email" => "xinjie_xj@163.com", 
		
		"seller_id" => "2088002007018916", 
		"buyer_id" => "2088101000082594", 
		"body" => "Hello", 
		"total_fee" => "10.00", 
		"extra_common_param" => "你好，这是测试商户的广告。",
//    	"agent_user_id" => "2088101000071628"
    );
//    $strPayType = "shengpay";
//    $arrInput = array(
//    	 'Amount' => '0.01',
//		 'PayAmount' => '0.01',
//		 'OrderNo' => '2011101703',
//		 'serialno' => 12345,
//		 'Status' => '01',
//		 'MerchantNo' => '715226',
//		  'PayChannel' => '03,04,07,14,18',
//		
//		  'Discount' => '0.95',
//		  'SignType' => 2,
//		  'PayTime' => '20111017083445',
//		  'CurrencyType' => 'RMB',
//		  'ProductNo' => '123123213',
//		  'ProductDesc' => 'this is a product!',
//		  'Remark1' => 'remark1',
//		  'Remark2' => 'remark2',
//		  'ExInfo' => '',
//		  'DefaultChannel' => '04',
//		  'CharSet' => '',
//		  'MAC' => 'e38da70702fc555a9aca2189de08620a',
//    );
   var_dump( Payment::verifyNotify($strPayType, $arrInput) );
   
  }
  
  public function action_finished() {
  	$strPayType = "alipay";
//  	$strPayType = "shengpay";
  	$strStatus = "01";
  	$strStatus = "TRADE_FINISHED";
  	$strStatus = "tRADE_SUCCESS";
  	var_dump( Payment::isOrderFinished($strPayType, $strStatus));
  }
}
