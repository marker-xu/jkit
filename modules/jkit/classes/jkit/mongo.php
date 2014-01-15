<?php
/**
 * 获取Mongo数据库的连接句柄
 */
class JKit_Mongo {
	protected static $_objInstance = null;
	protected $_arrMongo = array();
	
	private function __construct () {

	}
	
	public static function instance() {
		if (! self::$_objInstance) {
			self::$_objInstance = new self();
		}
		
		return self::$_objInstance;
	}
	
	public function getMongo($strCluster) {
		if (isset($this->_arrMongo[$strCluster])) {
			return $this->_arrMongo[$strCluster];
		}
		$arrClusterCfg = JKit::config("mongo.{$strCluster}");
		if (empty($arrClusterCfg)) {
			throw new Exception("no cluster {$strCluster} config");
		}
		
		$intRetry = (int) $arrClusterCfg['retry'];
		if ($intRetry < 1) {
			$intRetry = 2;
		}
		$intTimeout = (int) $arrClusterCfg['timeout'];
		if ($intTimeout < 1) {
			$intTimeout = 1000;
		}
		
		$arrOption = array('timeout' => $intTimeout, 'connect' => true);
		if (! empty($arrClusterCfg['option'])) {
			$arrOption = array_merge($arrOption, (array) $arrClusterCfg['option']);
		}
		for ($i = 0; $i < $intRetry; $i++) {
			try{
				$objMongo = new Mongo($arrClusterCfg['host'], $arrOption);
			} catch (Exception $e) {
				JKit::$log->warn("mongo conn fail[{$e->getMessage()}] retry[$i]", $arrClusterCfg);
				if ($i == ($intRetry - 1)) {
					throw $e;
				}
				continue;
			}
			break;
		}
		$this->_arrMongo[$strCluster] = $objMongo;
		
		return $objMongo;
	}
	
	public function __destruct() {
		foreach ($this->_arrMongo as $k => $objMongo) {
			$objMongo->close();
		}
	}
} 