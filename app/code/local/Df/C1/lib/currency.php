<?php
/**
 * @used-by df_c1_currency_code_to_magento_format()
 * @used-by \Df\C1\Config\MapItem\CurrencyCode::getNonStandardNormalized()
 * @param string $nonStandardCurrencyCode
 * @return string
 */
function df_c1_currency_code_normalize($nonStandardCurrencyCode) {
	df_param_string_not_empty($nonStandardCurrencyCode, 0);
	/** @var string $result */
	$result = mb_substr(df_trim(mb_strtoupper($nonStandardCurrencyCode), ' .'), 0, 3);
	df_result_string_not_empty($result);
	return $result;
}

/**
 * @used-by \Df\C1\Cml2\Export\Processor\Sale\Order::getDocumentData_Order()
 * @param string $ccInMagentoFormat
 * @return string
 */
function df_c1_currency_code_to_1c_format($ccInMagentoFormat) {
	df_param_string_not_empty($ccInMagentoFormat, 0);
	$result = dfa(df_c1_cfg()->general()->ссMapTo1C(), $ccInMagentoFormat, $ccInMagentoFormat);
	/**
	 * Раньше тут стояло df_result_string_not_empty,
	 * однако в магазине belle.com.ua это привело к сбою:
	   Результат метода забракован проверяющим «df_result_string».
	   Сообщения проверяющего:
	   Требуется строка, но вместо неё получена переменная типа «integer».
	 * Видимо, это потому, что код может быть числовым, например: 960.
	 * Поэтому вместо df_result_string_not_empty используем просто !
	 * http://magento-forum.ru/topic/3704/
	 */
	if (!$result) {
		df_error('Не могу перевести в формат 1С валютный код «%s».', $ccInMagentoFormat);
	}
	return $result;
}

/**
 * @used-by \Df\C1\Cml2\Import\Data\Entity\OfferPart\Price::getCurrencyCode()
 * @used-by \Df\C1\Cml2\Import\Data\Entity\PriceType::getCurrencyCode()
 * @param string $ccIn1CFormat
 * @return string
 */
function df_c1_currency_code_to_magento_format($ccIn1CFormat) {
	df_param_string_not_empty($ccIn1CFormat, 0);
	return dftr(
		df_c1_currency_code_normalize($ccIn1CFormat)
		,df_c1_cfg()->general()->ccMapFrom1C() + ['РУБ' => 'RUB', 'ГРН' => 'UAH']
	);
}
