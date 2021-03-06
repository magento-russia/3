<?php
namespace Df\Shipping\Settings;
class InHouseProcessing extends \Df_Core_Model_Settings {
	/**
	 * @override
	 * @see Df_Core_Model_Settings::getKeyPrefix()
	 * @used-by Df_Core_Model_Settings::adaptKey()
	 * @return string
	 */
	protected function getKeyPrefix() {return 'df_shipping/in_house_processing/';}

	/**
	 * 2015-04-07
	 * Возвращает планируемую дату передачи заказа интернет-магазином в службу доставки
	 * (к текущей дате прибавляются сроки обработки заказа интернет-магазином
	 * и нерабочие дни интернет-магазина).
	 * @used-by \Df\Shipping\Rate\Result\Method::prepareMethodTitle()
	 * @return \Zend_Date
	 */
	public static function date() {static $r; return $r ?: $r = df_today_add(self::days());}

	/**
	 * Возвращает количество дней между текущей датой
	 * и датой передачи заказа интернет-магазином в службу доставки
	 * (к текущей дате прибавляются сроки обработки заказа интернет-магазином
	 * и нерабочие дни интернет-магазина)
	 * @used-by \Df\Shipping\Rate\Result\Method::prepareMethodTitle()
	 * @return int
	 */
	public static function days() {
		static $result;
		if (is_null($result)) {
			/** @var $this $s */
			$s = new self;
			/** @var int $result */
			$result = $s->nat0('days');
			/** @var bool $canShipToday */
			$canShipToday = df_hour() < $s->nat('can_ship_today_untill');
			if (!$canShipToday) {
				$result++;
			}
			if ($s->getYesNo('consider_days_off')) {
				$result = df_num_calendar_days_by_num_working_days(
					$startDate = $canShipToday ? \Zend_Date::now() : df_tomorrow()
					,$numWorkingDays = $result
					,$store = $s->store()
				);
			}
		}
		return $result;
	}
}