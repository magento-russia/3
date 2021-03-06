<?php
namespace Df\WalletOne;
class AddPaymentMethods extends \Df_Varien_Data_Form_Processor {
	/** @return void */
	public function process() {
		foreach ($this->getFieldValues() as $subFieldName => $subFieldValue) {
			/** @var string|int $subFieldName */
			/** @var string|array $subFieldValue */
			$this->getForm()->addHiddenField(
				implode('_', array($this->getFieldName(), df_string($subFieldName)))
				,$this->getFieldName()
				,$subFieldValue
			);
		}
	}

	/** @return string */
	private function getFieldName() {return $this->cfg(self::P__FIELD_NAME);}

	/** @return array */
	private function getFieldValues() {return $this->cfg(self::P__FIELD_VALUES);}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this
			->_prop(self::P__FIELD_NAME, DF_V_STRING_NE)
			->_prop(self::P__FIELD_VALUES, DF_V_ARRAY)
		;
	}
	
	const P__FIELD_NAME = 'field_name';
	const P__FIELD_VALUES = 'field_values';
	/**
	 * @static
	 * @param array(string => mixed) $parameters [optional]
	 * @return self
	 */
	public static function i(array $parameters = []) {return new self($parameters);}

}