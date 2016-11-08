<?php
class Df_Core_Helper_Data extends Mage_Core_Helper_Abstract {
	/** @return \Df\Core\Helper\Path */
	public function path() {return \Df\Core\Helper\Path::s();}
	/** @return Df_Dataflow_Model_Registry */
	public function registry() {return Df_Dataflow_Model_Registry::s();}
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}