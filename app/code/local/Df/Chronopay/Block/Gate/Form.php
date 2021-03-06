<?php
class Df_Chronopay_Block_Gate_Form extends Mage_Payment_Block_Form_Cc {
	/**
	 * @override
	 * @return string
	 */
	public function getArea() {
		return Df_Core_Const_Design_Area::FRONTEND;
	}

	/**
	 * @override
	 * @return string
	 */
	public function getTemplate() {
		return self::TEMPLATE;
	}

	const TEMPLATE = 'df/chronopay/gate/form.phtml';
}