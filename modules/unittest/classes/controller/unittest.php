<?php defined('SYSPATH') or die('No direct script access.');

require_once Kohana::find_file('vendor', 'simpletest/unit_tester');

/**
 * UnitTest 模块的 控制器，集成 [SimpleTest](http://www.simpletest.org/) 进行单元测试
 *
 * [!!] 使用方法：直接在bootstrap.php中激活这个模块，然后把测试用例放在application的unittest目录下  
 * TestCase写法例子在 sample/sample.php
 *
 * @package    UnitTest
 * @category   Base
 * @author     akira.cn@gmail.com
 * @copyright  (c) 2011 WED Team
 * @license    http://kohanaframework.org/license
 */
class Controller_UnitTest extends Controller{
	function before(){
		$this->suite = new TestSuite();
	}
	
	/**
	 * 跑单元测试的 action
	 *
	 * [!!]如果设置了路由规则下的 case 参数，则跑对应的unittest，否则跑全部的unittest
	 */
	public function action_run(){
		$case = $this->request->param('case');
		if(isSet($case)){
			$case = str_replace('_', '/', $case);
			$case = Kohana::find_file('unittest', $case);
		
			if(!$case)
				throw new Kohana_Exception("UnitTest doesn't exists! (unittest/$case.php)");
			else
				$this->suite->addFile($case);
		}else{ //find all cases
			$filelist = Kohana::list_files('unittest');

			foreach($filelist as $case){
				$this->suite->addFile($case);
			}
		}
	}
	
	/**
	 * 自动运行 MyReporter 生成测试报告
	 */
	function after(){
		$this->suite->run(new MyReporter());
		exit;
	}
}

class MyReporter extends HtmlReporter{
	protected $failed = false;

	function __construct($character_set = 'utf-8') {
		parent::__construct($character_set);
	}

	function paintCaseStart($test_name){
		parent::paintCaseStart($test_name);
		print "<div><h3>$test_name</h3>";
	}
	function paintCaseEnd($test_name){
		if(!$this->failed)
			print ' <span class="pass">success</span>';
		parent::paintCaseEnd($test_name);
		print '</div>';
		$this->failed = false;
	}
	function paintFail($message) {
		print '<div>';
		parent::paintFail($message);
		print '</div>';
		$this->failed = true;
	}
	protected function getCss() {
		return ".fail { background-color: inherit; color: red; }" .
				".pass { background-color: inherit; color: green; }" .
				" pre { background-color: lightgray; color: inherit; }".
				" h3 { display: inline; }";
	}
}