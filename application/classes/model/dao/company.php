<?php defined('SYSPATH') or die('No direct script access.');

class Model_Dao_Company extends Model_Dao_Base {
	
	protected $strTableName = "";
	
	protected $strPk = "";
	
	public function __construct($strTableName="company", $strPkName='id') {
		parent::__construct();
		$this->setTable($strTableName, $strPkName);
		
	}
	
	public function setTable($strTableName, $strPkName) {
		$this->strTableName = $strTableName;
		$this->strPk = $strPkName;
	}
	
	public function getById($intId) {
		$query = DB::select()->select_array(array('id', 'name', 'code'))->from($this->strTableName)->where('id', '=', $intId);
		try {
			$result = $query->execute($this->objDB);
			print_r($result);
		} catch (Exception $e) {
			print_r($e);
		}
		$arrReturn = $result->as_array();
		return $arrReturn[0];
	}
	
	public function insert($arrRow) {
		if(!isset($arrRow['create_date'])) {
			$arrRow['create_date'] = date("Y-m-d H:i:s");
		}
		$query = DB::insert($this->strTableName, array_keys($arrRow))->values($arrRow);
		try {
			$result = $query->execute($this->objDB);
			print_r($result);
		} catch (Exception $e) {
			print_r($e);
		}
		
		return $result[0];
	}
	
	public function updateById($intId, $arrRow) {
		$query = DB::update($this->strTableName)->where('id', '=', $intId)->set($arrRow);
		try {
			$result = $query->execute($this->objDB);
		} catch (Exception $e) {
			print_r($e);
		}
		
		return $result>1;
	}
	
	public function getList($arrFilters=null, $intOffset=0, $intCount=10, $strOrderBy=null, $mixedSort=null) {
		
		$query = DB::select('id', 'name', 'code')->from($this->strTableName);
		if ($arrFilters) {
			foreach($arrFilters as $k=>$val) {
				$strOp = "=";
				if(is_array($val)) {
					$strOp = "in";
				} 
				$query = $query->and_where($k, $strOp, $val);			
			}
		}
		$query = $query->and_where('id', 'between', 1)->and_where('', '', 3);
		$query = $query->limit("{$intOffset}, {$intCount}");
		if($strOrderBy) {
			$direction = "DESC";
			if($mixedSort) {
				$direction = $mixedSort;
			}
			$query = $query->order_by($strOrderBy, $direction);
		}
		try {
			$result = $query->execute($this->objDB);
			print_r($result);
		} catch (Exception $e) {
			print_r($e);
		}
		return $result->as_array();
	}
}