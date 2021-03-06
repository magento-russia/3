<?php
abstract class Df_Page_Model_MenuSource extends Df_Core_Model {
	/**
	 * @abstract
	 * @return Varien_Data_Tree
	 */
	abstract public function getTree();
	/**
	 * @abstract
	 * @return bool
	 */
	abstract public function isEnabled();

	/** @return int */
	public function getWeight() {return $this->cfg(self::P__WEIGHT);}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->_prop(self::P__WEIGHT, DF_V_INT);
	}

	const P__WEIGHT = 'weight';
	/**
	 * @param Df_Page_Model_MenuSource $a
	 * @param Df_Page_Model_MenuSource $b
	 * @return int
	 */
	public static function sort(Df_Page_Model_MenuSource $a, Df_Page_Model_MenuSource $b) {
		/**
		 * The comparison function
		 * must return an integer less than, equal to, or greater than zero
		 * if the first argument is considered to be respectively
		 * less than, equal to, or greater than the second.
		 * http://php.net/manual/function.usort.php
		 * @see usort
		 */
		return $a->getWeight() - $b->getWeight();
	}
}