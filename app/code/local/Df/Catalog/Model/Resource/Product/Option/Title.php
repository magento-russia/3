<?php
class Df_Catalog_Model_Resource_Product_Option_Title extends Df_Core_Model_Resource {
	/**
	 * Нельзя вызывать @see parent::_construct(),
	 * потому что это метод в родительском классе — абстрактный.
	 * @see Mage_Core_Model_Resource_Abstract::_construct()
	 * @override
	 * @return void
	 */
	protected function _construct() {
		$this->_init(self::TABLE, Df_Catalog_Model_Product_Option_Title::P__ID);
	}

	const TABLE = 'catalog/product_option_title';
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}