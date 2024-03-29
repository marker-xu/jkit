<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 封装 HTTP 应答对象. [Request] 被 `execute()` 后返回该对象
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
class JKit_Response extends Kohana_Response{
	/**
	 * 与response关联的request对象
	 *
	 * [!!] 可通过构造函数参数 $config 的 `_request` 属性传入
	 *
	 * @var Request
	 */
	protected $_request;

	/**
	 * HTTP 返回 $data 数据的 json 格式
	 *
	 *     $this->json(array('foo' => 'bar')); // {'foo' : 'bar'}
	 *     $this->json(array('foo' => 'bar'), 'cb')); // cb({'foo' : 'bar'});
	 *
	 * @param  array	要应答的数据
	 * @param  string	回调的方法，可缺省。如果传入这个参数，那么返回的数据为js回调格式
	 * @return Response 
	 */
	public function json($data, $callback=null){
		$json = json_encode($data);		

		if($callback){
			$this->headers(array('content-type'=>'application/x-javascript', 'charset'=>'UTF-8'));
			return $this->body("{$callback}({$json});"); 
		}else{
			$this->headers(array('content-type'=>'application/json', 'charset'=>'UTF-8'));
			return $this->body($json);
		}
	}

	/**
	 * HTTP 返回 $data 数据的 jsonp 格式  
	 * 通过在 url 中的 cb 参数传入回调函数名，如果缺省，则只返回 json 数据  
	 *
	 *     $this->jsonp(array('foo' => 'bar')); // {'foo' : 'bar'}
	 *
	 * @param  array	要应答的数据
	 * @return Response
	 */
	public function jsonp($data){
		$cb = Controller::current_controller()->request->param('cb');
		return $this->json($data, $cb);
	}

	/**
	 * 让 Response 输出debug信息
	 *
	 *     $this->response->debug(array('foo' => 'bar'), array('another' => 'value') ...);
	 *
	 * [!!]	可以传入任意多个对象，将依次传入这些对象的信息
	 *
	 * @return Response
	 */
	public function debug(){
		$this->headers(array('jkit-debugger'=>'1.0'));

		if(!($this->_body instanceof JKit_View)){ //view是template或字符串
			$content = (string)$this->_body;
			$this->_body = View::factory("string:{$content}");
		}
		$this->_body->debugging = true;

		$args = func_get_args();
		
		//可以传额外参数进去
		foreach($args as $arg){
			$this->_body->set($arg);
		}

		return $this;
	}

	/**
	 * Gets or sets the body of the response  
	 *
	 * [!!] 取消(string)的强制转换，这样的话在echo前就不会触发 view 的render操作，避免Requests的Profile的失效
	 *
	 * @return  mixed
	 */
	public function body($content = NULL)
	{
		if ($content === NULL)
			return $this->_body;
		
		if (is_string($content) && strtolower($content) === '__template__'){
			$this->_body = Controller::template();
		}
		else{
			$this->_body = $content;
		}
		
		return $this;
	}

	/**
	 * Outputs the body when cast to string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return (string)$this->_body;
	}

	/**
	 * 将 Response 发送回客户端
	 *
	 * [!!] 这个操作将会中断程序执行的流程，立即发送数据流到客户端浏览器
	 *
	 *    $this->status(200)->body('Hello World')->send();
	 *
	 * @param  Response 要返回的 Response
	 * @uses Profiler::stop_by_group
	 */
	public function send(){
		Controller::current_controller()->after();
		Profiler::stop_by_group('Requests'); //结束Request的Profiler
		echo $this->send_headers()->body();
		exit;
	}

	/**
	 * 魔术方法，支持Response直接发送回某个状态码
	 *
	 *     $this->body('bad request')->__400();
	 */
	function __call($name, $args) {
		if(preg_match('/__(\d{3})/',$name, $matches)){
			$this->status(intval($matches[1]))->send();
		}
		else{
			throw new Kohana_Exception("Call to undefined method Response::{$name}");
		}
	}
}
