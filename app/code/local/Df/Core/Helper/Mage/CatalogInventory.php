<?php
class Df_Core_Helper_Mage_CatalogInventory extends Mage_Core_Helper_Abstract {
	/** @return Mage_CatalogInventory_Model_Stock */
	public function stockSingleton() {
		return Mage::getSingleton('cataloginventory/stock');
	}
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}