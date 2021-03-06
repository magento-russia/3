<?php
/**
 * @singleton
 * Система создаёт объект-одиночку для потомков этого класса.
 * Не забывайте об этом при реализации кеширования результатов вычислений внутри этого класса!
 */
class Df_Admin_Config_Source_Layout_Shift_Direction_Horizontal extends Df_Admin_Config_Source {
	/**
	 * Константы нам не нужны, потому что эти значения прямо преобразуются
	 * в значения правила CSS «margin»:
	 * @see Df_Checkout_Block_Frontend_Review_OrderComments::getMarginRule()
	 * @override
	 * @param bool $isMultiSelect
	 * @return array(array(string => string))
	 */
	protected function toOptionArrayInternal($isMultiSelect = false) {return df_map_to_options([
		'left' => 'влево', 'right' => 'вправо'
	]);}
}