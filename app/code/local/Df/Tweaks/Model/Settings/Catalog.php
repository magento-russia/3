<?php
class Df_Tweaks_Model_Settings_Catalog extends Df_Core_Model_Settings {
	/** @return Df_Tweaks_Model_Settings_Catalog_Product */
	public function product() {return Df_Tweaks_Model_Settings_Catalog_Product::s();}
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}