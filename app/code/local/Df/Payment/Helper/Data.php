<?php
class Df_Payment_Helper_Data extends Mage_Payment_Helper_Data {
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}