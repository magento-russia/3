<?php
namespace Df\Uniteller\Request;
/** @method \Df\Uniteller\Method method() */
class Payment extends \Df\Payment\Request\Payment {
	/**
	 * @override
	 * @see \Df\Payment\Request\Payment::_params()
	 * @used-by \Df\Payment\Request\Payment::params()
	 * @return array(string => string|int)
	 */
	protected function _params() {
		/** @var array(string => string) $result */
		$result = array(
			self::REQUEST_VAR__CUSTOMER__ADDRESS =>	$this->street()
			,self::REQUEST_VAR__CUSTOMER__CITY => $this->city()
			,self::REQUEST_VAR__CUSTOMER__COUNTRY => $this->countryName()
			,self::REQUEST_VAR__CUSTOMER__EMAIL => $this->email()
			,self::REQUEST_VAR__CUSTOMER__NAME_FIRST => $this->nameFirst()
			,self::REQUEST_VAR__CUSTOMER__NAME_LAST => $this->nameLast()
			,self::REQUEST_VAR__CUSTOMER__NAME_MIDDLE => $this->nameMiddle()
			,self::REQUEST_VAR__CUSTOMER__PHONE =>
				'+' . \Df\Core\Format\MobilePhoneNumber::i($this->phone())->getOnlyDigits()
			,self::REQUEST_VAR__CUSTOMER__STATE => $this->regionCode()
			,self::REQUEST_VAR__CUSTOMER__ZIP => $this->postCode()
			,self::REQUEST_VAR__ORDER_AMOUNT => $this->amountS()
			,self::REQUEST_VAR__ORDER_COMMENT => $this->description()
			,self::REQUEST_VAR__ORDER_NUMBER => $this->orderIId()
			,self::REQUEST_VAR__SIGNATURE => $this->getSignature()
			,self::REQUEST_VAR__SHOP_ID => $this->shopId()
			,self::REQUEST_VAR__URL_RETURN_OK => df_url_checkout_success()
			,self::REQUEST_VAR__URL_RETURN_NO => df_url_checkout_fail()
		);
		return $result;
	}

	/** @return string */
	private function getSignature() {
		return strtoupper(md5(implode(Df_Uniteller_Helper_Data::SIGNATURE_PARTS_SEPARATOR,
			array_map('md5', $this->preprocessParams(array(
				self::REQUEST_VAR__SHOP_ID => $this->shopId()
				,self::REQUEST_VAR__ORDER_NUMBER => $this->orderIId()
				,self::REQUEST_VAR__ORDER_AMOUNT => $this->amountS()
				,'dummy-1' => ''
				,'dummy-2' => ''
				,'dummy-3' => ''
				,'dummy-4' => ''
				,'dummy-5' => ''
				,'dummy-6' => ''
				,'dummy-7' => ''
				,'dummy-8' => $this->password()
			)))
		)));
	}

	const REQUEST_VAR__CUSTOMER__ADDRESS = 'Address';
	const REQUEST_VAR__CUSTOMER__CITY = 'City';
	const REQUEST_VAR__CUSTOMER__COUNTRY = 'Country';
	const REQUEST_VAR__CUSTOMER__EMAIL = 'Email';
	const REQUEST_VAR__CUSTOMER__NAME_FIRST = 'FirstName';
	const REQUEST_VAR__CUSTOMER__NAME_LAST = 'LastName';
	const REQUEST_VAR__CUSTOMER__NAME_MIDDLE = 'MiddleName';
	const REQUEST_VAR__CUSTOMER__PHONE = 'Phone';
	const REQUEST_VAR__CUSTOMER__STATE = 'State';
	const REQUEST_VAR__CUSTOMER__ZIP = 'Zip';
	const REQUEST_VAR__ENCODING = 'Encoding';
	const REQUEST_VAR__ORDER_AMOUNT = 'Subtotal_P';
	const REQUEST_VAR__ORDER_COMMENT = 'Comment';
	const REQUEST_VAR__ORDER_CURRENCY = 'Currency';
	const REQUEST_VAR__ORDER_NUMBER = 'Order_IDP';
	const REQUEST_VAR__PAYMENT_SERVICE__LANGUAGE = 'Language';
	const REQUEST_VAR__PAYMENT_SERVICE__PAYMENT_ACTION = 'Preauth';
	const REQUEST_VAR__SIGNATURE = 'Signature';
	const REQUEST_VAR__SHOP_ID = 'Shop_IDP';
	const REQUEST_VAR__URL_RETURN_OK = 'URL_RETURN_OK';
	const REQUEST_VAR__URL_RETURN_NO = 'URL_RETURN_NO';
}