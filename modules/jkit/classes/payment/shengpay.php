<?php defined('SYSPATH') or die('No direct script access.');

class Payment_Shengpay {
	
	private static $arrServiceAPI = array(
		'default'=>'http://netpay.sdo.com/paygate/default.aspx',
		'ibankpay' => 'http://netpay.sdo.com/paygate/ibankpay.aspx'
	);
	
	private $strUrl="";
	
	private $strMd5Key = "";
	
	private $arrOptions = array(
		'Version' => '3.0', //协议版本
		'MerchantUserId' => '',//商户的用户ID
		'CurrencyType' => 'RMB',
		'NotifyUrlType' => 'http', //回调类型
		'SignType' => '2',
		'DefaultChannel' => '04',
		'CharSet' => 'UTF-8'
	);
	
	public function __construct($arrConf) {
		$this->setServiceUrl($arrConf['service']);
		$this->strMd5Key = $arrConf['key'];
		unset($arrConf['service']);
		unset($arrConf['key']);
		$this->arrOptions = array_merge($this->arrOptions, $arrConf);
	}
	/**
	 * 
	 * 去支付页面
	 * @param array $arrInput array(
	 * 	Amount => Decimal（10,2），支付金额，必须含两位小数。如：2.00
	 * 	OrderNo => String（16），商户订单号，<=32位,商户必须保证其唯一性,否则支付将失败
	 * 	PostBackUrl => String（128），回调地址，显示给终端用户的地址
	 * 	BackUrl => String（128），商户下单地址，用户取消订单返回或者重新发起订单的地址
	 * 	OrderTime => String（14），订单日期，必须为14位，格式:yyyyMMddHHmmss
	 * 	ProductNo => String(32)，商品编号，商品的编号,由商户定义
	 * 	ProductDesc => String（256），商品描述，商品的描述信息
	 * )
	 * 
	 * @return void
	 */
	public function topay($floatTotalFee, $strOrderNo, $strSubject, $arrInput) {
		
		$arrNeedField = array(
			'Amount',
			'OrderNo',
			'ProductNo'
		);
		$arrInput['Amount'] = $floatTotalFee;
		$arrInput['OrderNo'] = $strOrderNo;
		$arrInput['ProductNo'] = $strSubject;
		$arrDiff = array_diff($arrNeedField, array_keys($arrInput) );
		if( $arrDiff ) {
			$field = array_shift($arrDiff);
			throw new Exception("lack field({$field})", -2001);
		}
		if (!isset($arrInput['ProductDesc'])) {
			$arrInput['ProductDesc'] = $arrInput['ProductNo'];
		}
		$strHtml = Form::open($this->strUrl, array(
			'method'=>'post', 
			'id'=>'shengpaysubmit',
			'name'=>'shengpaysubmit'
			)
		);
		$arrElements = $this->getPayParam($arrInput);
		foreach($arrElements as $field=>$value) {
			$strHtml.= Form::hidden($field, $value);
		}
		$strHtml.= Form::submit("shengpaypay", "shengpay");
		$strHtml.= Form::close();
		$strHtml.= "<script>document.forms['shengpaysubmit'].submit();</script>";
		
		die($strHtml);
	}
	/**
	 * 
	 * 获取支付属性
	 * @param array $arrInput array(
	 * 	Amount => Decimal（10,2），支付金额，必须含两位小数。如：2.00
	 * 	OrderNo => String（16），商户订单号，<=32位,商户必须保证其唯一性,否则支付将失败
	 * 	PostBackUrl => String（128），回调地址，显示给终端用户的地址
	 * 	BackUrl => String（128），商户下单地址，用户取消订单返回或者重新发起订单的地址
	 * 	OrderTime => String（14），订单日期，必须为14位，格式:yyyyMMddHHmmss
	 * 	ProductNo => String(32)，商品编号，商品的编号,由商户定义
	 * 	ProductDesc => String（256），商品描述，商品的描述信息
	 * )
	 * 
	 * @return array
	 */
	public function getPayParam($arrInput) {
		$arrDefault = array(
			"ProductNo"=>"", 
			"ProductDesc" => "",
			"Remark1" => "",
			"Remark2" => "",
			"BankCode" => "",
			"DefaultChannel" => "",
			"ExterInvokeIp" => ""
		);
		$arrInput = array_merge($arrDefault, $this->arrOptions, $arrInput);
		if(!isset($arrInput['OrderTime'])) {
			$arrInput['OrderTime'] = date("YmdHis");
		}
		$arrInput['Amount'] = round($arrInput['Amount'], 2);
		$arrInput['MAC'] = $this->getSig($arrInput);
		return $arrInput;
	}
	
	/**
	 * 
	 * 验证回调是否合法
	 * @param array $arrInput post回来的参数
	 * 
	 * @return boolean
	 */
	public function verifyNotify($arrInput) {
		return $this->verrifySig($arrInput);
	}
	
	/**
	 * 
	 * 验证返回跳转是否合法
	 * @param array $arrInput get回来的参数
	 * 
	 * @return boolean
	 */
	public function verifyReturn($arrInput) {
		return $this->verrifySig($arrInput);
	}
	/**
	 * 
	 * 是否支付订单结束
	 * @param string $strStatus 返回的状态
	 * 
	 * @return boolean
	 */
	public function isOrderFinished($strStatus) {
		return $strStatus=='01';
	}
	
	public function verrifySig($arrInput) {
		#TODO notify_id
		$strSign = $this->getCallbackSig($arrInput);

		if( $arrInput['MAC'] != $strSign ) {
			JKit::$log->warn("the sign is not matched, {$strSign} ", $arrInput);
			return false;
		}
		
		return true;
	}
	
	/**
	 * 
	 * Origin =Amount + "|" + PayAmount + "|" + OrderNo + "|" + serialno + "|" +Status + "|" + 
	 * MerchantNo + "|" +PayChannel + "|" + Discount + "|" + SignType+ "|" + PayTime +"|" + 
	 * CurrencyType + "|" + ProductNo + "|" + ProductDesc + "|" + Remark1 + "|" + Remark2 + "|" + ExInfo
	 * @param unknown_type $arrInput
	 */
	public function getCallbackSig($arrInput) {
		$arrOrigin = array(
		$arrInput['Amount'] , $arrInput['PayAmount'] , $arrInput['OrderNo'] , 
		$arrInput['serialno'] , $arrInput['Status'] , $arrInput['MerchantNo'] , $arrInput['PayChannel'] , 
		$arrInput['Discount'] , $arrInput['SignType'] , $arrInput['PayTime'] , $arrInput['CurrencyType'] , 
		$arrInput['ProductNo'] , $arrInput['ProductDesc'] , $arrInput['Remark1'] , $arrInput['Remark2'] , 
		$arrInput['ExInfo']
		);
		return $this->createSig($arrOrigin, "|");
	}
	
	public function getSuccessCode() {
		return "OK";
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
			'amount' => $arrInput['PayAmount'],
			'return_code' => 'fail',
			'order_no' => $arrInput['OrderNo']
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
	/**
	 * 
	 * Origin＝Version + Amount + OrderNo + MerchantNo + MerchantUserId + PayChannel 
	 * + PostBackUrl + NotifyUrl + BackUrl + OrderTime + CurrencyType + NotifyUrlType 
	 * + SignType + ProductNo + ProductDesc + Remark1 + Remark2 + BankCode 
	 * + DefaultChannel + CharSet +ExterInvokeIp；
	 */
	private function getSig($arrInput) {
		$arrOrigin = array(
		$arrInput['Version'], $arrInput['Amount'] , $arrInput['OrderNo'] , $arrInput['MerchantNo'] , 
		$arrInput['MerchantUserId'] , $arrInput['PayChannel'] , $arrInput['PostBackUrl'] , $arrInput['NotifyUrl'] , 
		$arrInput['BackUrl'] , $arrInput['OrderTime'] , $arrInput['CurrencyType'] , $arrInput['NotifyUrlType'] , 
		$arrInput['SignType'] , $arrInput['ProductNo'] , $arrInput['ProductDesc'] , $arrInput['Remark1'] , $arrInput['Remark2'] , 
		$arrInput['BankCode'] , $arrInput['DefaultChannel'] , $arrInput['CharSet'], $arrInput['ExterInvokeIp']
		);
		
		return $this->createSig($arrOrigin);
	}
	
	
	private function createSig($arrInput, $strSep="") {
		$strOrigin = join($strSep, $arrInput);
		return md5($strOrigin.$strSep.$this->strMd5Key);
	}
	
	private function setServiceUrl($service) {
		if( !isset(self::$arrServiceAPI[$service]) ) {
			throw new Kohana_Exception("Invalid Payment Service-{$service} ", -1001);
		}
		$this->strUrl = self::$arrServiceAPI[$service];
	}
}