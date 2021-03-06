<?php
namespace Df\Interkassa\Request;
/** @method \Df\Interkassa\Method method() */
class Payment extends \Df\Payment\Request\Payment {
	/**
	 * @override
	 * @see \Df\Payment\Request\Payment::_params()
	 * @used-by \Df\Payment\Request\Payment::params()
	 * @return array(string => string|int)
	 */
	protected function _params() {return [
		'ik_status_method' => 'POST'
		,'ik_success_method' => 'POST'
		,'ik_fail_method' => 'POST'
		,'ik_payment_amount' => $this->amountS()
		,'ik_payment_desc' => $this->description()
		,'ik_payment_id' => $this->orderIId()
		,'ik_paysystem_alias' => ''
		,'ik_shop_id' => $this->shopId()
		,'ik_status_url' => $this->urlConfirm()
		,'ik_success_url' => df_url_checkout_success()
		,'ik_fail_url' => df_url_checkout_fail()
	];}
}