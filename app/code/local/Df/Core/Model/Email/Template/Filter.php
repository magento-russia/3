<?php
class Df_Core_Model_Email_Template_Filter extends Mage_Core_Model_Email_Template_Filter {

	/**
	 * @static
	 * @param array(string => mixed) $parameters [optional]
	 * @return Df_Core_Model_Email_Template_Filter
	 */
	public static function i(array $parameters = []) {return new self($parameters);}
}