<?php defined('SYSPATH') or die('No direct script access.');

class Model_Dao_Base {
	
	protected $objDB;
	
	public function __construct() {
		$this->objDB = Database::instance('jkit', $config);
	}
	
}