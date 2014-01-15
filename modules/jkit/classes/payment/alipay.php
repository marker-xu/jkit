<?php defined('SYSPATH') or die('No direct script access.');
require_once Jkit::find_file('classes/payment/alipay', 'alipay_service.class');
require_once Jkit::find_file('classes/payment/alipay', 'alipay_notify.class');
class Payment_Alipay {
	
	private $strPayService = "create_direct_pay_by_user";
	
	private $strUrl="https://mapi.alipay.com/gateway.do";
	
	private $strMd5Key = "";
	
	private $arrOptions = array(
		'sign_type' => 'MD5',//签名方式, DSA、RSA、MD5三个值可选，必须大写。
		'_input_charset' => 'utf-8',//编码类型
		'payment_type' => 1,//支付类型
		'paymethod' => 'directPay',//默认支付方式directPay（余额支付）bankPay（网银支付）...
		
		'transport' =>'http',//通信方式，http或https
	);
	
	public function __construct($arrConf) {
		$this->arrOptions = array_merge($this->arrOptions, $arrConf);
	}
	/**
	 * 
	 * 去支付
	 * @param array $arrInput array(
	 * 	return_url  => 页面跳转同步通知页面路径，支付宝处理完请求后，当前页面自动跳转到商户网站里指定页面的http路径。，String 
	 *  out_trade_no  => 商户网站唯一订单号，支付宝合作商户网站唯一订单号。，String(64) 
	 *  subject  => 商品名称 ，商品的标题/交易标题/订单标题/订单关键字等。，String(256) 
	 *  payment_type  => 支付类型 ，取值范围请参见附录“12.5 收款类型”。，String(4) 	
	 *  total_fee  => 交易金额 ，该笔订单的资金总额，单位为RMB-Yuan。取值范围为[0.01，100000000.00]，精确到小数点后两位。，Number 
	 *  body  => 商品描述 ，对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。，String(1000) 	
	 *  show_url  => 商品展示网址，收银台页面上，商品展示的超链接。，String(400) 		
	 * )
	 * 
	 * @return void
	 */
	public function topay($floatTotalFee, $strOrderNo, $strSubject, $arrInput) {
		
		/************************************************************/
		$arrNeedField = array(
			'out_trade_no',
			'subject',
			'total_fee'
		);
		$arrInput['total_fee'] = $floatTotalFee;
		$arrInput['out_trade_no'] = $strOrderNo;
		$arrInput['subject'] = $strSubject;
		
		$arrDiff = array_diff($arrNeedField, array_keys($arrInput) );
		if( $arrDiff ) {
			throw new Kohana_Exception("lack field({$arrDiff[0]})", array(), -2001);
		}
		
		//构造要请求的参数数组
		$parameter = array(
				"service"			=> $this->strPayService,
				"payment_type"		=> $this->arrOptions['payment_type'],
				
				"partner"			=> trim($this->arrOptions['partner']),
				"_input_charset"	=> trim(strtolower($this->arrOptions['_input_charset'])),
		        "seller_email"		=> trim($this->arrOptions['seller_email']),
		        "return_url"		=> trim($this->arrOptions['return_url']),
		        "notify_url"		=> trim($this->arrOptions['notify_url']),
				"error_notify_url"	=> trim($this->arrOptions['error_notify_url']),
		
				"paymethod"			=> trim($this->arrOptions['paymethod']),
		);
		$arrInput['total_fee'] = round($arrInput['total_fee'], 2);
		$parameter = array_merge($parameter, $arrInput);
		//构造即时到帐接口
		$alipayService = new AlipayService($this->arrOptions);
		$alipayService->alipay_gateway_new = strstr($this->strUrl, "?") ? $this->strUrl : $this->strUrl."?";
		$html_text = $alipayService->create_direct_pay_by_user($parameter);
		echo $html_text;
	}
	/**
	 * 
	 * 验证回调是否合法
	 * @param array $arrInput post回来的参数
	 * 
	 * @return boolean
	 */
	public function verifyNotify($arrInput) {
		$alipayNotify = new AlipayNotify($this->arrOptions);
		$alipayNotify->setParams($arrInput);
		return $alipayNotify->verifyNotify();
	}
	
	/**
	 * 
	 * 验证返回跳转是否合法
	 * @param array $arrInput get回来的参数
	 * 
	 * @return boolean
	 */
	public function verifyReturn($arrInput) {
		$alipayNotify = new AlipayNotify($this->arrOptions);
		$alipayNotify->setParams($arrInput);
		return $alipayNotify->verifyReturn();
	}
	/**
	 * 
	 * 是否支付订单结束
	 * @param string $strStatus 返回的状态
	 * 
	 * @return boolean
	 */
	public function isOrderFinished($strStatus) {
		return $strStatus=='TRADE_SUCCESS' || $strStatus=='TRADE_FINISHED';
	}
	/**
	 * 
	 * 去支付
	 * @param array $arrInput array(
	 * 	return_url  => 页面跳转同步通知页面路径，支付宝处理完请求后，当前页面自动跳转到商户网站里指定页面的http路径。，String 
	 *  out_trade_no  => 商户网站唯一订单号，支付宝合作商户网站唯一订单号。，String(64) 
	 *  subject  => 商品名称 ，商品的标题/交易标题/订单标题/订单关键字等。，String(256) 
	 *  payment_type  => 支付类型 ，取值范围请参见附录“12.5 收款类型”。，String(4) 	
	 *  total_fee  => 交易金额 ，该笔订单的资金总额，单位为RMB-Yuan。取值范围为[0.01，100000000.00]，精确到小数点后两位。，Number 
	 *  body  => 商品描述 ，对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。，String(1000) 	
	 *  show_url  => 商品展示网址，收银台页面上，商品展示的超链接。，String(400) 		
	 * )
	 */
	public function getPayParam($arrInput) {
		$alipaySubmit = new AlipaySubmit();
		//构造要请求的参数数组
		//构造要请求的参数数组
		$parameter = array(
				"service"			=> $this->strPayService,
				"payment_type"		=> $this->arrOptions['payment_type'],
				
				"partner"			=> trim($this->arrOptions['partner']),
				"_input_charset"	=> trim(strtolower($this->arrOptions['input_charset'])),
		        "seller_email"		=> trim($this->arrOptions['seller_email']),
		        "return_url"		=> trim($this->arrOptions['return_url']),
		        "notify_url"		=> trim($this->arrOptions['notify_url']),
				"error_notify_url"	=> trim($this->arrOptions['error_notify_url']),
		
				"paymethod"			=> trim($this->arrOptions['paymethod']),
		);
		$parameter = array_merge($parameter, $arrInput);
		return $alipaySubmit->buildRequestPara($parameter, $this->arrOptions);
	}
	
	/**
	 * 
	 * 成功标志，返回给第三方
	 */
	public function getSuccessCode() {
		return "success";
	}
	/**
	 * 
	 * Enter description here ...
	 * @param array $arrInput 
	 * @param string $strNotifyType 类型 "Notify"回调通知，"Return"跳转回来，其它默认回调
	 * 
	 * @return array(
	 * 	'status' => 'success',success/failure
	 *  'amount' => 0.01,//支付金额
	 *  'return_code' => ''//返回码,
	 *  'order_no' => ''
	 * )
	 */
	public function callback($arrInput, $strNotifyType="Notify") {
		$arrReturn = array(
			'status' => 'failure',
			'amount' => $arrInput['total_fee'],
			'return_code' => 'fail',
			'order_no' => $arrInput['out_trade_no']
		);
		if( ($strNotifyType!="Return" && !$this->verifyNotify($arrInput)) 
		||  ($strNotifyType=="Return" && !$this->verifyReturn($arrInput)) ) {
			throw new Kohana_Exception("Illegal Sign", array(), -3001);
		}
		
		if( $this->isOrderFinished($strStatus) ) {
			$arrReturn['status'] = "success";
			$arrReturn['return_code'] = $this->getSuccessCode();
		}
		
		return $arrReturn;
		
	}
}