<?php
class Df_WalletOne_Helper_Data extends Mage_Core_Helper_Data {
	/**
	 * @param array $params
	 * @return array
	 */
	public function preprocessSignatureParams(array $params) {
		/** @var array $result */
		$result = [];
		foreach ($params as $key => $value) {
			/** @var string $key */
			/** @var mixed $value */
			$result[]= implode(self::SIGNATURE_KEY_VALUE_SEPARATOR, array($key, df_string($value)));
		}
		return $result;
	}

	
	const SIGNATURE_KEY_VALUE_SEPARATOR = '=';
	const SIGNATURE_PARTS_SEPARATOR = '&';

	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}