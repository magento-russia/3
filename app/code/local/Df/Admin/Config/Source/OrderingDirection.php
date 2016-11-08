<?php
use Varien_Data_Collection as C;
/**
 * @singleton
 * Система создаёт объект-одиночку для потомков этого класса.
 * Не забывайте об этом при реализации кеширования результатов вычислений внутри этого класса!
 */
class Df_Admin_Config_Source_OrderingDirection extends Df_Admin_Config_Source {
	/**
	 * @override
	 * @param bool $isMultiSelect
	 * @return array(array(string => string))
	 */
	protected function toOptionArrayInternal($isMultiSelect = false) {return df_map_to_options([
		C::SORT_ORDER_ASC => 'по возрастанию', C::SORT_ORDER_DESC => 'по убыванию'
	]);}
}