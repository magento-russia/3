<?php
class Df_Directory_Model_Resource_Country extends Mage_Directory_Model_Resource_Country {
	/** @used-by Df_Directory_Model_Resource_Region_Collection::_construct() */
	const TABLE = 'directory/country';
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}