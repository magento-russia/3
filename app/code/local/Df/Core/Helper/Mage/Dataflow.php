<?php
class Df_Core_Helper_Mage_Dataflow extends Mage_Core_Helper_Abstract {
	/** @return Mage_Dataflow_Model_Batch */
	public function batch() {return Mage::getSingleton('dataflow/batch');}
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}