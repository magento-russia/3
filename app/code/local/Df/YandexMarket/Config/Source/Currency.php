<?php
namespace Df\YandexMarket\Config\Source;
class Currency extends \Df_Admin_Config_Source {
	/**
	 * @override
	 * @param bool $isMultiSelect
	 * @return array(array(string => string))
	 */
	protected function toOptionArrayInternal($isMultiSelect = false) {return
		$this->getAsOptionArray()
	;}

	/**
	 * Здесь кэшировать результат можно,
	 * потому что у класса нет параметров.
	 * @return string[][]
	 */
	private function getAsOptionArray() {
		if (!isset($this->{__METHOD__})) {
			/** @var string[] $optionCurrencyMap */
			/** @noinspection PhpParamsInspection */
			$optionCurrencyMap = df_options_to_map(\Mage::app()->getLocale()->getOptionCurrencies());
			/** @var string[][] $result */
			$result = [];
			foreach (self::$_currencies as $currencyCode) {
				/** @var string $currencyCode */
				/** @var string|null $label */
				$label = dfa($optionCurrencyMap, $currencyCode);
				if (!is_null($label)) {
					$result[]= df_option($currencyCode, $label);
				}
			}
			$this->{__METHOD__} = $result;
		}
		return $this->{__METHOD__};
	}

	/**
	 * @used-by \Df\YandexMarket\Config\Backend\Currency::_beforeSave()
	 * @param string $code
	 * @throws \Df\Core\Exception
	 */
	public static function check($code) {
		if (!in_array($code, self::$_currencies)) {
			df_error('Яндекс.Маркет не допускает указанную Вами валюту «%s».',
				df_currency_name($code)
			);
		}
	}

	/**
	 * «В качестве основной валюты (для которой установлено rate="1")
	 * могут быть использованы только рубль (RUR, RUB),
	 * белорусский рубль (BYR), гривна (UAH) или тенге (KZT).»
	 * @const
	 * http://help.yandex.ru/partnermarket/?id=1111480
	 * @return string[]
	 */
	private static $_currencies =  array('RUB', 'UAH', 'KZT', 'BYR');
}