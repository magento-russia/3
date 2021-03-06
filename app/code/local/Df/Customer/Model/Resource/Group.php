<?php
class Df_Customer_Model_Resource_Group extends Mage_Customer_Model_Entity_Group {
	/**
	 * @override
	 * @param Mage_Core_Model_Abstract|Df_Customer_Model_Group $object
	 * @return $this
	 * @throws Mage_Core_Exception
	 */
	protected function _checkUnique(Mage_Core_Model_Abstract $object) {
		Df_Core_Model_Resource_Db_UniqueChecker::check(
			$object, $this->_getWriteAdapter(), $this->_prepareDataForSave($object)
		);
		return $this;
	}
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}