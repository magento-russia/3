<?php
class Df_Catalog_Model_Resource_Product_Flat extends Mage_Catalog_Model_Resource_Product_Flat {
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}