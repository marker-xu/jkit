<?php defined('SYSPATH') or die('No direct script access.');

class Payment_Alipay_Validate {
	
	public static function check($arrInput) {
		$arrNeedField = array(
			'out_trade_no',
			'subject',
			'total_fee',
			'return_url'
		);
		
		$arrDiff = array_diff($arrNeedField, array_keys($arrInput) );
		if( $arrDiff ) {
			$field = array_shift($arrDiff);
			throw new Exception("lack field({$field})", -2001);
		}
		
	}
}