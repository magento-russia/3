<?php
class Df_Core_Model_Website extends Mage_Core_Model_Website {
	/**
	 * @override
	 * @return Df_Core_Model_Resource_Website_Collection
	 */
	public function getResourceCollection() {return self::c();}

	/**
	 * @override
	 * @return Df_Core_Model_Resource_Website
	 * 2016-10-14
	 * В родительском классе метод переобъявлен через PHPDoc,
	 * и поэтому среда разработки думает, что он публичен.
	 */
	/** @noinspection PhpHierarchyChecksInspection */
	protected function _getResource() {return Df_Core_Model_Resource_Website::s();}

	/**
	 * @used-by Df_Core_Model_Resource_Website_Collection::_construct()
	 * @used-by Df_PromoGift_Model_Gift::getWebsite()
	 */

	/**
	 * @static
	 * @param bool $loadDefault [optional]
	 * @return Df_Core_Model_Resource_Website_Collection
	 */
	public static function c($loadDefault = false) {
		/** @var Df_Core_Model_Resource_Website_Collection $result */
		$result = new Df_Core_Model_Resource_Website_Collection;
		$result->setLoadDefault($loadDefault);
		return $result;
	}
	/**
	 * @static
	 * @param array(string => mixed) $parameters [optional]
	 * @return Df_Core_Model_Website
	 */
	public static function i(array $parameters = []) {return new self($parameters);}
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}