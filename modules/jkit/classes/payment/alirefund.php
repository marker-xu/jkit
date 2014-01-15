<?php defined('SYSPATH') or die('No direct script access.');
require_once Jkit::find_file('classes/payment/alipay', 'alipay_service.class');
require_once Jkit::find_file('classes/payment/alipay', 'alipay_notify.class');
class Payment_Alipay {
	
	private $strPayService = "create_direct_pay_by_user";
	
	private $strRefundService = "refund_fastpay_by_platform_pwd";
	
	private $strUrl="";
	
	private $strMd5Key = "";
	
	private $arrOptions = array();
	
	public function __construct($arrConf) {
		$this->strUrl = $arrConf['url'];
		unset($arrConf['url']);
		$this->arrOptions = $arrConf;
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
	public function topay($arrInput) {
		
		/************************************************************/
		
		//构造要请求的参数数组
		$parameter = array(
				"service"			=> $this->strPayService,
				"payment_type"		=> $this->arrOptions['payment_type'],
				
				"partner"			=> trim($this->arrOptions['partner']),
				"_input_charset"	=> trim(strtolower($this->arrOptions['input_charset'])),
		        "seller_email"		=> trim($this->arrOptions['seller_email']),
		        "return_url"		=> trim($this->arrOptions['return_url']),
		        "notify_url"		=> trim($this->arrOptions['notify_url']),
				
				"paymethod"			=> trim($this->arrOptions['paymethod']),
		);
		$parameter = array_merge($parameter, $arrInput);
		//构造即时到帐接口
		$alipayService = new AlipayService($this->arrOptions);
		$alipayService->alipay_gateway_new = strstr($this->strUrl, "?") ? $this->strUrl : $this->strUrl."?";
		$html_text = $alipayService->create_direct_pay_by_user($parameter);
		echo $html_text;
	}
	/**
	 * 
	 * Enter description here ...
	 * @param array $arrInput array(
	 * 	refund_date => 退款请求的当前时间。格式为：yyyy-MM-dd hh:mm:ss。
	 * 	batch_no => 退款批次号, 格式为：退款日期（8位）+流水号（3～24位）
	 *  batch_num => 总笔数,即参数detail_data的值中，“#”字符出现的数量加1，最大支持1000笔（即“#”字符出现的最大数量为999个
	 *  detail_data => 单笔数据集, "原付款支付宝交易号^退款总金额^退款理由#..."
	 * )
	 */
	public function torefund($arrInput) {
		//构造要请求的参数数组
		$parameter = array(
				"service"			=> $this->strRefundService,
				"partner"			=> trim($this->arrOptions['partner']),
				"_input_charset"	=> trim(strtolower($this->arrOptions['input_charset'])),
		        "seller_email"		=> trim($this->arrOptions['seller_email']),
		        "notify_url"		=> trim($this->arrOptions['refund_notify_url']),
				
		);
		$parameter = array_merge($parameter, $arrInput);
		//构造即时到帐接口
		$alipayService = new AlipayService($this->arrOptions);
		$alipayService->alipay_gateway_new = strstr($this->strUrl, "?") ? $this->strUrl : $this->strUrl."?";
		$html_text = $alipayService->refund_fastpay_by_platform_pwd($parameter);
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
	
	public function verifyRefundNotify($arrInput) {
		
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
}