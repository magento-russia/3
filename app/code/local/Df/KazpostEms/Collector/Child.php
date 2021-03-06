<?php
namespace Df\KazpostEms\Collector;
abstract class Child extends \Df\Shipping\Collector\Child {
	/**
	 * @override
	 * @see \Df\Shipping\Collector::feePercentOfDeclaredValue()
	 * @used-by \Df\Shipping\Collector::rate()
	 * «Плата за объявленную ценность (процент от стоимости вложения): 1%»
	 * http://www.kazpost.kz/uploads/content/files/СТАНДАРТ%20Тарифы%20по%20почтовым%20услугам.docx
	 * @return int|float
	 */
	protected function feePercentOfDeclaredValue() {return 1;}

	/**
	 * @used-by Df_KazpostEms_Collector_Foreign::_collect()
	 * @used-by Df_KazpostEms_Collector_Domestic::_collect()
	 * @param array(int => int[]) $rates
	 * @param int[] $ratesMore
	 * @param int $zone
	 * @return float
	 */
	protected function choose($rates, $ratesMore, $zone) {
		/** @var int $result */
		/** @var int $weightG */
		$weightG = $this->weightG();
		/** @var int[] $rates */
		foreach ($rates as $maxWeight => $currentRates) {
			/** @var int $maxWeight */
			/** @var int[] $currentRates */
			if ($weightG < $maxWeight) {
				$result = $currentRates[$zone];
				break;
			}
		}
		if (!isset($result)) {
			/** @var int[] $last */
			$last = df_last($rates);
			/** @var int $diff */
			$diff = (int)floor($this->weightKg() - 10);
			$result = $last[$zone] + $diff * $ratesMore[$zone];
		}
		return $result;
	}
}