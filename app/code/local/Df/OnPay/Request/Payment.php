<?php
namespace Df\OnPay\Request;
/**
 * @method \Df\OnPay\Method method()
 * @method \Df\OnPay\Config\Area\Service configS()
 */
class Payment extends \Df\Payment\Request\Payment {
	/**
	 * @override
	 * @see \Df\Payment\Request\Payment::_params()
	 * @used-by \Df\Payment\Request\Payment::params()
	 * @return array(string => string|int)
	 */
	protected function _params() {
		return array(
			self::REQUEST_VAR__CUSTOMER__EMAIL => $this->email()
			,self::REQUEST_VAR__ORDER_AMOUNT => df_h()->onPay()->priceToString($this->amount())
			,self::REQUEST_VAR__ORDER_COMMENT => $this->description()
			,self::REQUEST_VAR__ORDER_CURRENCY =>
				$this->configS()->getCurrencyCodeInServiceFormat()
			,self::REQUEST_VAR__ORDER_NUMBER => $this->orderIId()
			,self::REQUEST_VAR__PAYMENT_SERVICE__IS_FEE_PAYED_BY_SHOP => df_bts(
					\Df\Payment\Config\Source\FeePayer::VALUE__SHOP
				===
					$this->configS()->getFeePayer()
			)
			,self::REQUEST_VAR__PAYMENT_SERVICE__LANGUAGE => $this->localeCode()
			,self::REQUEST_VAR__PAYMENT_SERVICE__NEED_CONVERT_RECEIPTS =>
				$this->needConvertReceipts()
			,self::REQUEST_VAR__PAYMENT_SERVICE__PAYMENT_MODE => self::PAYMENT_MODE__FIX
			,self::REQUEST_VAR__SIGNATURE => $this->getSignature()
			,self::REQUEST_VAR__URL_RETURN_OK => df_url_checkout_success()
			,self::REQUEST_VAR__URL_RETURN_NO => df_url_checkout_fail()
		);
	}

	/** @return string */
	private function getSignature() {
		return df_h()->onPay()->generateSignature($this->preprocessParams(array(
			self::REQUEST_VAR__PAYMENT_SERVICE__PAYMENT_MODE=> self::PAYMENT_MODE__FIX
			,self::REQUEST_VAR__ORDER_AMOUNT => df_h()->onPay()->priceToString($this->amount())
			,self::REQUEST_VAR__ORDER_CURRENCY => $this->configS()->getCurrencyCodeInServiceFormat()
			,self::REQUEST_VAR__ORDER_NUMBER => $this->orderIId()
			,self::REQUEST_VAR__PAYMENT_SERVICE__NEED_CONVERT_RECEIPTS => $this->needConvertReceipts()
			,self::SIGNATURE_PARAM__ENCRYPTION_KEY => $this->password()
		)));
	}

	/** @return string */
	private function needConvertReceipts() {
		return
				\Df\OnPay\Config\Source\ReceiptCurrency::VALUE__BILL
			===
				$this->configS()->getReceiptCurrency()
			? 'yes'
			: 'no'
		;
	}

	const PAYMENT_MODE__FIX = 'fix';
	const REQUEST_VAR__CUSTOMER__EMAIL = 'user_email';
	const REQUEST_VAR__ORDER_AMOUNT = 'price';
	const REQUEST_VAR__ORDER_COMMENT = 'note';
	const REQUEST_VAR__ORDER_CURRENCY = 'currency';
	const REQUEST_VAR__ORDER_NUMBER = 'pay_for';
	const REQUEST_VAR__PAYMENT_SERVICE__IS_FEE_PAYED_BY_SHOP = 'price_final';
	const REQUEST_VAR__PAYMENT_SERVICE__LANGUAGE = 'ln';
	const REQUEST_VAR__PAYMENT_SERVICE__NEED_CONVERT_RECEIPTS = 'convert';
	const REQUEST_VAR__PAYMENT_SERVICE__PAYMENT_MODE = 'pay_mode';
	const REQUEST_VAR__SIGNATURE = 'md5';
	const REQUEST_VAR__URL_RETURN_OK = 'url_success';
	const REQUEST_VAR__URL_RETURN_NO = 'url_fail';
	const SIGNATURE_PARAM__ENCRYPTION_KEY = 'encryption_key';
}