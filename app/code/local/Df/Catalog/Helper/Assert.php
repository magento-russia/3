<?php
class Df_Catalog_Helper_Assert extends Mage_Core_Helper_Abstract {
	/**
	 * @var Mage_Core_Model_Resource_Abstract $resource
	 * @return void
	 */
	public function categoryResource(Mage_Core_Model_Resource_Abstract $resource) {
		df_assert(df_h()->catalog()->check()->categoryResource($resource));
	}

	/**
	 * @var Varien_Data_Collection_Db $collection
	 * @return void
	 */
	public function productAttributeCollection(Varien_Data_Collection_Db $collection) {
		df_assert(df_h()->catalog()->check()->productAttributeCollection($collection));
	}

	/**
	 * @var Varien_Data_Collection_Db $collection
	 * @return void
	 */
	public function productCollection(Varien_Data_Collection_Db $collection) {
		df_assert(df_h()->catalog()->check()->productCollection($collection));
	}

	/**
	 * @var Mage_Core_Model_Resource_Abstract $resource
	 * @return void
	 */
	public function productResource(Mage_Core_Model_Resource_Abstract $resource) {
		df_assert(df_h()->catalog()->check()->productResource($resource));
	}

	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}