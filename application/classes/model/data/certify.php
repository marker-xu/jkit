<?php 
/*
 * 盛大验证接口封装
 * @author Mei xueting
 */
class Model_Data_Certify {
    protected static $subSystem = "1004211";
    //protected static $subSystem = "1003152";
    
    /*
	 * 接口Url列表
	 */
    protected static $sUrl;
    /*
	 * 接口序列号
	 */
    const SSO_CERTIFY_NO_DNY = 0;
    const SSO_CERTIFY = 1;
    const SSO_CERTIFY_NO_PWD = 2;
    
    const CERTIFY_CONFIG_NAME = "certify";
    /*
	 * 接口Host
	 */
    const SSO_CERTIFY_HOST = 'http://61.172.241.94:8083';
    /*
	 * 
	 */
    public function __construct ()
    {
        $this->init();
    }
    /*
     * 初始化
     */
    protected function init ()
    {
        //初始化接口Url
        if (! isset(self::$sUrl)) {
//            self::$sUrl = array(
//            self::SSO_CERTIFY_NO_DNY => self::SSO_CERTIFY_HOST .
//             "/Tivoli/SsoCertifyNull?", 
//            self::SSO_CERTIFY => self::SSO_CERTIFY_HOST .
//             "/Tivoli/SsoCertify?", 
//            self::SSO_CERTIFY_NO_PWD => self::SSO_CERTIFY_HOST .
//             "/Tivoli/SsoCertifyNullPwd?");
            self::$sUrl = array(
	            self::SSO_CERTIFY_NO_DNY => "/Tivoli/SsoCertifyNull?", 
	            self::SSO_CERTIFY => "/Tivoli/SsoCertify?", 
	            self::SSO_CERTIFY_NO_PWD => "/Tivoli/SsoCertifyNullPwd?"
            );
        }
    }
    /*
	 * 拼接url参数
	 * @param array $param 参数数组
	 * @return string 
	 */
    protected function makeParam ($param)
    {
        $param_str = '';
        if (is_array($param)) {
            foreach ($param as $key => $value) {
                $encode_value = urlencode($value);
                $param_str .= "{$key}={$encode_value}&";
            }
        }
        return $param_str;
    }
    /*
     * 获取url返回数据
     * @param string $url
     * @param array $param
     * @return array | boolean 
     */
    protected function getResult ($url, $param)
    {
        if (isset($param)) {
            $param_str = $this->makeParam(
            $param);
        }
        
//        $rawResult = $this->getRawResult( $url . $param_str);
		echo $url . $param_str."<br>\n";
		try {
			$rawResult = RPC::call(self::CERTIFY_CONFIG_NAME, $url . $param_str);
		} catch (Exception $e) {
			print_r($e);
		}
        
        if (false === $rawResult) {
            return false;
        }
        echo $rawResult."<br>\n";
        return explode('|', 
        $this->decodeRawResult($rawResult));
    }
    /*
	 * 获取url返回的原始数据
	 * @param string $url
	 * @return string | boolean 
	 */
    protected function getRawResult ($url)
    {
        $fp = fopen($url, 'r');
        if (false === $fp) {
            return false;
        }
        $result = fgets($fp);
        if (false === $result) {
            return false;
        }
        fclose($fp);
        return $result;
    }
    /*
     * 解码返回数据
     * @param string $raw
     * @return string 
     */
    protected function decodeRawResult ($raw)
    {
        $decodeResult = mb_convert_encoding($raw, 
        "UTF-8", "GBK");
        return $decodeResult;
    }
    /*
     * 构造结果map
     * @param array $result
     * @return array 
     */
    protected function makeResult ($result)
    {
        $parsedResult = array();
        $parsedResult['success'] = $result[0] == 1;
        $parsedResult['return_value'] = $result[0];
        if ($parsedResult['success']) {
            $parsedResult['user_id'] = $result[1];
            $parsedResult['center'] = $result[2];
            $parsedResult['department'] = $result[3];
            $parsedResult['role'] = $result[4];
        } else {
            $parsedResult['error_message'] = $result[1];
        }
        return $parsedResult;
    }
    /*
	 * 验证用户名，密码
	 * @param $user
	 * @param $pwd
	 * @param $ip
	 */
    public function certifyNoDyn ($user, $pwd, $ip = '')
    {
        $param = array('user' => $user, 
        'pwd' => strtoupper(md5($pwd)), 
        'sub' => self::$subSystem, 'ip' => $ip);
        $result = $this->getResult(
        self::$sUrl[self::SSO_CERTIFY_NO_DNY], 
        $param);
        return $this->makeResult($result);
    }
    /*
     * 验证用户名，密码
     * @param $user
     * @param $pwd
     * @param $dyn
     * @param $ip
     */
    public function certify ($user, $pwd, $dyn, $ip = '')
    {
        $param = array('user' => $user, 
        'pwd' => strtoupper(md5($pwd)), 
        'dyn' => $dyn, 'sub' => self::$subSystem, 
        'ip' => $ip);
        $result = $this->getResult(
        self::$sUrl[self::SSO_CERTIFY], $param);
        return $this->makeResult($result);
    }
    /*
     * 验证用户名,密保号
     * @param $user
     * @param $dyn
     * @param $ip
     */
    public function certifyNoPwd ($user, $dyn, $ip = '')
    {
        $param = array('user' => $user, 
        'dyn' => $dyn, 'sub' => self::$subSystem, 
        'ip' => $ip);
        $result = $this->getResult(
        self::$sUrl[self::SSO_CERTIFY_NO_PWD], 
        $param);
        return $this->makeResult($result);
    }
}
