<?php
class Df_Catalog_Model_Resource_Product extends Mage_Catalog_Model_Resource_Product {
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}