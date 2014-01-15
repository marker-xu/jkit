<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 
 * 支付类 需要先在config目录下配置payment文件
 * @author xucongbin
 *
 */
class Payment {
	/**
	 * 
	 * 工厂
	 * @param string $strPayType 支付类型
	 */
	public static function factory($strPayType) {
		$arrConf = JKit::config("payment.{$strPayType}");
		
		if (! is_array($arrConf)) {
			JKit::$log->warn("Payment {$strPayType} config not exist", $arrInput);
			return false;
		}
		
		$objRealPayment = null;
		try {
			switch ($arrConf['type']) {
				case PAYMENT_TYPE_ALIPAY:
					$objRealPayment = new Payment_Alipay($arrConf['options']);
					$arrCallParam = $arrInput;
					break;
				case PAYMENT_TYPE_SHENGPAY:
					$objRealPayment = new Payment_Shengpay($arrConf['options']);
					break;
				default:
					JKit::$log->warn("Payment {$strPayType} type not support", $arrInput);
					break;
			}
		} catch (Kohana_Exception $e) {
			JKit::$log->warn("create {$strPayType} payment failure, code-".$e->getCode.", msg-".$e->getMessage());
		}
		
		return $objRealPayment;
	}
	/**
	 * 
	 * 去支付吧
	 * @param string $strPayType
	 * @param float $floatTotalFee 金额
	 * @param string $strOrderNo 订单号
	 * @param string $strSubject 产品名称
	 * @param array $arrOtherInput 其它必填或选填内容
	 * @throws Kohana_Exception
	 */
	public static function topay($strPayType, $floatTotalFee, $strOrderNo, $strSubject, $arrOtherInput=array()) {
		JKit::$log->info("topay: type-{$strPayType}, params-", $arrInput);
		$objPayment = self::factory($strPayType);
		if (!$objPayment) {
			throw new Kohana_Exception("Payment {$strPayType} type not support", -1001);
		}
		
		return $objPayment->topay($floatTotalFee, $strOrderNo, $strSubject, $arrOtherInput);
	}
	/**
	 * 
	 * 验证回调签名
	 * @param string $strPayType
	 * @param array $arrInput
	 * @throws Kohana_Exception
	 * 
	 * @return boolean
	 */
	public static function verifyNotify($strPayType, $arrInput) {
		JKit::$log->info("topay: type-{$strPayType}, params-", $arrInput);
		$objPayment = self::factory($strPayType);
		if (!$objPayment) {
			throw new Kohana_Exception("Payment {$strPayType} type not support", -1001);
		}
		
		return $objPayment->verifyNotify($arrInput);
	}
	/**
	 * 
	 * 验证跳转签名
	 * @param string $strPayType
	 * @param array $arrInput
	 * @throws Kohana_Exception
	 * 
	 * @return boolean
	 */
	public static function verifyReturn($strPayType, $arrInput) {
		JKit::$log->info("topay: type-{$strPayType}, params-", $arrInput);
		$objPayment = self::factory($strPayType);
		if (!$objPayment) {
			throw new Kohana_Exception("Payment {$strPayType} type not support", -1001);
		}
		
		return $objPayment->verifyReturn($arrInput);
	}
	/**
	 * 
	 * 获取跳转参数
	 * @param string $strPayType
	 * @param array $arrInput
	 * @throws Kohana_Exception
	 */
	public static function getPayParam($strPayType, $arrInput) {
		$objPayment = self::factory($strPayType);
		if (!$objPayment) {
			throw new Kohana_Exception("Payment {$strPayType} type not support", -1001);
		}
		
		return $objPayment->getPayParam($arrInput);
	}
	/**
	 * 
	 * 判断支付订单状态是否完成
	 * @param string $strPayType 支付类型
	 * @param $strStatus 支付状态
	 * 
	 * @return boolean
	 */
	public static function isOrderFinished($strPayType, $strStatus) {
		$objPayment = self::factory($strPayType);
		if (!$objPayment) {
			throw new Kohana_Exception("Payment {$strPayType} type not support", -1001);
		}
		
		return $objPayment->isOrderFinished($strStatus);
	}
	/**
	 * 
	 * 获取回调成功返回码
	 * @param string $strPayType
	 */
	public static function getCallbackSuccessCode($strPayType) {
		$objPayment = self::factory($strPayType);
		if (!$objPayment) {
			throw new Kohana_Exception("Payment {$strPayType} type not support", -1001);
		}
		
		return $objPayment->getSuccessCode();
	}
}