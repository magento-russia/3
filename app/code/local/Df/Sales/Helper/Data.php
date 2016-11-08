<?php
class Df_Sales_Helper_Data extends Mage_Core_Helper_Abstract {
	/** @return Df_Sales_Helper_Assert */
	public function assert() {
		return Df_Sales_Helper_Assert::s();
	}

	/** @return Df_Sales_Helper_Check */
	public function check() {
		return Df_Sales_Helper_Check::s();
	}

	/** @return Df_Sales_Helper_Order */
	public function order() {
		return Df_Sales_Helper_Order::s();
	}

	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}