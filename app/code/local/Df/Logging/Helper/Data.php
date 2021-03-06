<?php
class Df_Logging_Helper_Data extends Mage_Core_Helper_Abstract {
	/**
	 * Join array into string except empty values
	 *
	 * @param array $array Array to join
	 * @param string $glue Separator to join
	 * @return string
	 */
	public function implodeValues($array, $glue = ', ') {
		if (!is_array($array)) {
			return $array;
		}
		$result = [];
		foreach ($array as $item) {
			if ((string)$item !== '') {
				$result[]= $item;
			}
		}
		return implode($glue, $result);
	}

	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}