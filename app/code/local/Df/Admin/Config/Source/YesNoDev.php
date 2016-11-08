<?php
/**
 * @singleton
 * Система создаёт объект-одиночку для потомков этого класса.
 * Не забывайте об этом при реализации кеширования результатов вычислений внутри этого класса!
 */
class Df_Admin_Config_Source_YesNoDev extends Df_Admin_Config_Source {
	/**
	 * @override
	 * @param bool $isMultiSelect
	 * @return array(array(string => string))
	 */
	protected function toOptionArrayInternal($isMultiSelect = false) {return df_map_to_options([
		self::YES => 'да'
		,self::NO => 'нет'
		,self::DEVELOPER_MODE => "только при {$this->mode()} режиме разработчика"
	]);}

	/** @return string */
	private function mode() {return
		df_bool($this->getFieldParam('df_enable_in_developer_mode', 1)) ? 'включенном' : 'отключенном'
	;}
	/**
	 * @used-by toOptionArrayInternal()
	 * @used-by Df_Core_Model_Translate::_addData()
	 */
	const DEVELOPER_MODE = 'developer-mode';
	/**
	 * @used-by toOptionArrayInternal()
	 * @used-by Df_Core_Model_Translate::_addData()
	 */
	const NO = 'no';
	/**
	 * @used-by toOptionArrayInternal()
	 * @used-by Df_Core_Model_Translate::_addData()
	 * @used-by Df_Localization_Settings_Area::allowInterference()
	 */
	const YES = 'yes';
}