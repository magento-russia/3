<?php
class Df_Rating_Helper_Data extends Mage_Core_Helper_Abstract {
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}