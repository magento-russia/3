<?php
class Df_Sales_Helper_Assert extends Mage_Core_Helper_Abstract {
	/**
	 * @var Varien_Data_Collection_Db $collection
	 * @return void
	 */
	public function orderCollection(Varien_Data_Collection_Db $collection) {
		df_assert(df_h()->sales()->check()->orderCollection($collection));
	}

	/**
	 * @var Varien_Data_Collection_Db $collection
	 * @return void
	 */
	public function orderGridCollection(Varien_Data_Collection_Db $collection) {
		df_assert(df_h()->sales()->check()->orderGridCollection($collection));
	}

	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}