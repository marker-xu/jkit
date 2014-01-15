<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Xhprof extends Controller {
	public function before() {
		// start profiling
		xhprof_enable();
//		phpinfo();
//		exit;
	}
	public function action_index(){
		echo strtr("abcdefgh", array("a"=>"b"));
		// stop profiler
		$xhprof_data = xhprof_disable();
		$XHPROF_ROOT = "/home/xucongbin/snda-php/webroot";
		//
		// Saving the XHProf run
		// using the default implementation of iXHProfRuns.
		//
		include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
		include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
		
		$xhprof_runs = new XHProfRuns_Default();
		
		// Save the run under a namespace "xhprof_foo".
		//
		// **NOTE**:
		// By default save_run() will automatically generate a unique
		// run id for you. [You can override that behavior by passing
		// a run id (optional arg) to the save_run() method instead.]
		//
		$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");
		
		echo "---------------\n".
		"Assuming you have set up the http based UI for \n".
		"XHProf at some address, you can view run at \n".
		"<a href='http://test.youtube.com:9080/xhprof_html/index.php?run=$run_id&source=xhprof_foo'>Link</a>\n".
		"---------------\n"; 
	}
}
