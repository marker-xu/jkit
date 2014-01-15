<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Company extends Controller {
	
	private $objDataCompany;
	
	public function before() {
		$this->objDataCompany = new Model_Dao_Company();	
	}
	
	public function action_index(){
		$arrFilters = array('id'=>array(1,2));
		print_r($this->objDataCompany->getList($arrFilters));
	    echo $this->response->body('hello world');
	}
	
	public function action_insert() {
	  	$arrParam = array(
	  		'name' =>'wenguang',
	  		'code' => 123
	  	);
	  	print_r($this->objDataCompany->insert($arrParam));
	  	
	}
	
	public function action_update() {
	  	$arrParam = array(
	  		'code' =>'456',
	  	);
	  	print_r($this->objDataCompany->updateById(3, $arrParam));
	  	
	}
	
	public function action_get() {
	  	print_r($this->objDataCompany->getById(3));
	  	
	}
}