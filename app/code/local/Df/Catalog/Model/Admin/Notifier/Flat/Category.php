<?php
class Df_Catalog_Model_Admin_Notifier_Flat_Category extends Df_Catalog_Model_Admin_Notifier_Flat {
	/**
	 * @override
	 * @return string
	 */
	protected function getConfigPathSuffix() {return 'flat_catalog_category';}
	/**
	 * @override
	 * @return string
	 */
	protected function getTableTypeInGenitiveCase() {return 'товарных разделов';}

	/**
	 * @used-by Df_Catalog_Observer::df__config_after_save__catalog__frontend__flat_catalog_category()
	 * @return Df_Catalog_Model_Admin_Notifier_Flat_Category
	 */
	public static function s() {static $r; return $r ?: $r = new self;}
}