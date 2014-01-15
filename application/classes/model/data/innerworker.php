<?php 

class Model_Data_Innerworker {
	
	/**
	 * 
	 * 获取内部帐号信息
	 */
	public function getUserinfoByName($strUsername) {
		$strUsername= iconv("UTF-8", "GB18030", $strUsername);
    	$url		= "http://61.172.241.94:8084/Tivoli/SsoDetailInfo?code=";
    	$url		.= $strUsername;
    	$ret		= file_get_contents($url);
    	if (!$ret) {
    		return false;
    	}
    		
		$ret	= iconv("GB18030","UTF-8",$ret);
    	$row	= explode('|', $ret);
    	$info	= array();
    	$info['realname']	= $row[2];
    	$info['department']	= $row[4].$row[5];
    	$info['email']		= $row[7];
    	return $info;
	}
	
	
}