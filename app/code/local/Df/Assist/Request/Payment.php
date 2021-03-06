<?php
namespace Df\Assist\Request;
/** @method \Df\Assist\Method method() */
class Payment extends \Df\Payment\Request\Payment {
	/**
	 * @override
	 * @see \Df\Payment\Request\Payment::_params()
	 * @used-by \Df\Payment\Request\Payment::params()
	 * @return array(string => string|int)
	 */
	protected function _params() {return
		array_fill_keys($this->configS()->getDisabledPaymentMethods(), 0) + [
			'Address' => $this->street()
			,'City' => $this->city()
			,'Country' => $this->iso3()
			,'Email' => $this->email()
			,'Firstname' => $this->nameFirst()
			,'Lastname' => $this->nameLast()
			,'Middlename' => $this->nameMiddle()
			,'HomePhone' => $this->phone()
			,'State' => $this->regionCode()
			,'Zip' => $this->postCode()
			,'OrderAmount' => $this->amountS()
			,'OrderCurrency' => $this->currencyCode()
			,'OrderNumber' => $this->orderIId()
			,'Delay' => df_01($this->configS()->isCardPaymentActionAuthorize())
			,'Language' => $this->localeCode()
			,'TestMode' => df_01($this->method()->isTestMode())
			,'RecurringIndicator' => 0
			,'Merchant_ID' => $this->shopId()
			,'URL_RETURN_OK' => df_url_checkout_success()
			,'URL_RETURN_NO' => df_url_checkout_fail()
		]
	;}
}