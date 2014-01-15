<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 封装 HTTP 请求对象. 框架使用 [Route] 决定将请求发送给哪个 [Controller] 
 *
 *     $request = Request::factory($uri);
 *     $response = $request->execute();
 *     echo $response->headers('content-type','text/html')->send_headers()->body();
 *
 * [!!]扩展 [Kohana_Request]，并修复 bug
 *
 * @package    JKit
 * @category   Base
 * @author     akira.cn@gmail.com
 * @copyright  (c) 2011 WED Team
 * @license    http://kohanaframework.org/license
 */
class JKit_Request extends Kohana_Request{
    private static $_bolAddPorxyIp2Trusted = false;

    /**
     * 如果存在负载均衡，则需要把负载均衡的IP加入parent::$trusted_proxies，从而才能生成正确的Request::$client_ip
     *
     * @param   string  $uri URI of the request
     * @param   Cache   $cache
     * @param   array   $injected_routes an array of routes to use, for testing
     * @return  Request
     * @throws  Request_Exception
     * @uses    Route::all
     * @uses    Route::matches
     */
    public static function factory($uri = TRUE, HTTP_Cache $cache = NULL, $injected_routes = array())
    {
        if (! self::$_bolAddPorxyIp2Trusted) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) || isset($_SERVER['HTTP_CLIENT_IP'])) {
                $arrTmp = explode('.', $_SERVER['REMOTE_ADDR'], 3);
                if ($arrTmp[0] == '10' 
                    || ($arrTmp[0] == '192' && $arrTmp[1] == '168')
                    || ($arrTmp[0] == '172' && $arrTmp[1] >= 16 && $arrTmp[1] <= 31)) {
                    parent::$trusted_proxies[0] = $_SERVER['REMOTE_ADDR'];
                }
            }
            self::$_bolAddPorxyIp2Trusted = true;
        }
        
        return parent::factory($uri, $cache, $injected_routes);        
    }    
    
	/**
     *  当前的kohana版本中这个函数有个bug  
	 *	传Array进去的时候忘记return $this  
	 *
	 * [!!] 修复bug
	 *
	 * @param  string key
	 * @param  string value
	 * @return mixed  设置或读取cookie
	 */
	public function cookie($key = NULL, $value = NULL){
		if (is_array($key))
		{
			// Act as a setter, replace all cookies
			$this->_cookies = $key;
			return $this;
		}
		return parent::cookie($key, $value);
	}

	/**
	 * 覆盖了 [Kohana_Request::param]，改变了它的功能  
	 * 让param返回所有的参数，不论是route还是get还是post
	 *
	 * [!!] 如果有重名，优先返回 route 然后 post 最后是 get
	 * 
	 * @param  string key
	 * @param  string 默认值，当取不到结果的时候，设为这个值
	 * @param  bool $bolParamOnly true的时候只从param数组取参数
	 * @return string
	 */
	public function param($key = NULL, $default = NULL, $bolParamOnly = false)
	{
	    if ($bolParamOnly) {
	        return parent::param($key, $default);
	    }		
		if ($key === NULL) {
			// Return the full array
			return $this->_params + $this->_post + $this->_get;;
		}
		if (isset($this->_params[$key])) {
		    return $this->_params[$key];
		} elseif (isset($this->_post[$key])) {
		    return $this->_post[$key];
		} elseif (isset($this->_get[$key])) {
		    return $this->_get[$key];
		} else {
		    return $default;
		}
	}

	/**
	 * 让流程跳转到指定action
	 *
	 * @param string 要跳转到的uri
	 * @param string 新增query参数，原有参数会保留
	 * @param int    跳转状态码 20x
	 * @uses  Profiler::stop_all
	 */
	public function forward($uri, $params = array(), $code = 200){
		
		$forward = Request::process_uri($uri);
	
		$this->route($forward['route']);
		$this->directory($forward['params']['directory']);
		$this->action($forward['params']['action']);
		$this->controller($forward['params']['controller']);

		unset($forward['params']['controller'], $forward['params']['action'], $forward['params']['directory']);

		$this->_params = $forward['params'] + $params;

		Profiler::stop_all(); //结束所有的Profiler

		echo $this->execute()->status($code)
			      ->send_headers()
			      ->body();
		exit;
	}

	/**
	 * Redirects as the request response. If the URL does not include a
	 * protocol, it will be converted into a complete URL.
	 *
	 *     $request->redirect($url);
	 *
	 * [!!] No further processing can be done after this method is called!
	 *
	 * @param   string   $url   Redirect location
	 * @param   integer  $code  Status code: 301, 302, etc
	 * @return  void
	 * @uses    URL::site
	 * @uses    Request::send_headers
	 * @uses	Profiler::stop_all
	 */
	public function redirect($url = '', $code = 302)
	{
		Profiler::stop_all(); //结束所有的Profiler

		parent::redirect($url, $code);
	}

	/**
	 * 将请求中可能出现跨站攻击的内容过滤
	 * 
	 *     $data = $this->request->xss_clean()->param(); //过滤 xss 
	 *
	 * [!!]
	 * 框架中如果设定了 `JKit::$security['xss'] = true`，Controller 会在before中自动调用 `$this->request->xss_clean`
	 *
	 * @return Request
	 * @uses   Arr::map
	 */
	public function xss_clean(){
		$this->_params = Arr::map('HTML::clean', $this->_params);
		$this->_get = Arr::map('HTML::clean', $this->_get);
		$this->_post = Arr::map('HTML::clean', $this->_post);

		return $this;
	}
	
	/**
	 * 将 Response 发送回客户端
	 *
	 * [!!] 这个操作将会中断程序执行的流程，立即发送数据流到客户端浏览器
	 *
	 *    $response->status(200)->body('Hello World');
	 *    $request->send_response($response);
	 *
	 * @param  Response 要返回的 Response
	 * @uses   Profiler::stop_by_group
	 * @uses   Response::send
	 */
	public function send_response(Response $response = null){
		if($response){
			$this->response($response);
		}
		if (($response = $this->response()) === NULL)
		{
			$response = $this->create_response();
		}
		
		$response->send();
	}
}