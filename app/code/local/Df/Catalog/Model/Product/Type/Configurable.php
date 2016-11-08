<?php
class Df_Catalog_Model_Product_Type_Configurable extends Mage_Catalog_Model_Product_Type_Configurable {
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}