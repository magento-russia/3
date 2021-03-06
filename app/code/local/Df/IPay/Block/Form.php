<?php
namespace Df\IPay\Block;
/** @method \Df\IPay\Method method() */
class Form extends \Df\Payment\Block\Form {
	/** @return array */
	public function getPaymentOptions() {return $this->method()->constManager()->methodsCA();}

	/**
	 * @override
	 * @see \Df_Core_Block_Template::defaultTemplate()
	 * @used-by \Df_Core_Block_Template::getTemplate()
	 * @return string
	 */
	protected function defaultTemplate() {return 'df/ipay/form.phtml';}
}