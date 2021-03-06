<?php
namespace Df\OnPay\Config\Source;
class ReceiptCurrency extends \Df\Payment\Config\Source {
	/**
	 * @override
	 * @param bool $isMultiSelect
	 * @return array(array(string => string))
	 */
	protected function toOptionArrayInternal($isMultiSelect = false) {
		return df_map_to_options(array(
			self::VALUE__PAYMENT => self::VALUE__PAYMENT
			,self::VALUE__BILL => self::VALUE__BILL
		));
	}
	const VALUE__BILL = 'в валюте счёта';
	const VALUE__PAYMENT = 'в валюте платежа';
}