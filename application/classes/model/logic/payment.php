<?php defined('SYSPATH') or die('No direct script access.');

class Model_Logic_Payment {
	
	public function verrifyOrderSuccess($strPayType, $arrRequest) {
		$strPayType = "alipay";
		if(! Payment::verifyNotify($strPayType, $arrRequest) ) {
			return false;
		}
		if(! Payment::isOrderFinished($strPayType, $arrRequest['trade_status']) ) {
			return false;
		}
		
		#TODO 修改订单状态
		
		return true;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param $arrParams
	 */
	public function toAlipay($arrParams) {
		
	}
}