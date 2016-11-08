<?php
class Df_Core_Helper_Mage_Core_Design extends Mage_Core_Helper_Abstract {
	/** @return string */
	public function getThemeFrontend() {
		/**
		 * getTheme('template') работает в том случае, когда оформительская тема задана
		 * посредством административного меню «Система» → «Оформление витрины»
		 */
		/**
		 * Раньше тут стояло
		 * $theme = df_design_package()->getTheme('template');
		 * То есть, мы использовали в качестве идентификатора темы
		 * значение опции «Нестандартная папка шаблонов».
		 * Однако в оформительской теме Gala TitanShop в одном из демо-примеров
		 * (и в других аналогично) значением опции «Нестандартная папка шаблонов» является
		 * «galatitanshop_lingries_style01»,
		 * в то время как опция «Нестандартная папка темы» имеет правильное значение
		 * «galatitanshop».
		 * Поэтому вместо
		 * $theme = df_design_package()->getTheme('template');
		 * я решил использовать
		 * $theme = df_design_package()->getTheme('default');
		 * Передавая в метод getTheme() параметр «default», мы извлекаем значение опции
		 * «Нестандартная папка темы».
		 *
		 * 2015-10-16
		 * Всё-таки, и предыдущий алгоритм не совсем верен.
		 * Заметил для темы TemplateMonster Kids Fashion (53174),
		 * что df_design_package()->getTheme('default') возвращает null,
		 * df_design_package()->getTheme('frontend') возвращает «default»,
		 * в то время как df_design_package()->getTheme('template')
		 * возвращает правильное значение «theme690».
		 */
		/** @var string $result */
		$result = df_design_package()->getTheme('default');
		if (!$result) {
			$result = df_design_package()->getTheme('frontend');
		}
		if ('default' === $result) {
			$result = df_design_package()->getTheme('template');
		}
		return $result;
	}

	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}