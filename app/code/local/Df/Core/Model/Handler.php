<?php
/**
 * Обработчик события.
 *
 * Обратите внимание, что «событие» и «обработчик события» — два разных объекта.
 * Это даёт возможность инкапсулировать программный код класса «событие»
 * и повторго использовать этот программный код для разных обработчиков событий
 */
abstract class Df_Core_Model_Handler extends Df_Core_Model {
	/**
	 * @static
	 * @param string $class
	 * @param Df_Core_Model_Event $event
	 * @param array $additionalParams [optional]
	 * @return Df_Core_Model_Handler
	 */
	public static function create($class, Df_Core_Model_Event $event, $additionalParams = []) {
		return df_ic($class, __CLASS__, array(self::P__EVENT => $event) + $additionalParams);
	}

	/**
	 * Метод-обработчик события
	 *
	 * @abstract
	 * @return void
	 */
	abstract public function handle();

	/**
	 * Класс события (для валидации события)
	 * @abstract
	 * @return string
	 */
	abstract protected function getEventClass();

	/**
	 * 2016-10-16
	 * @return Mage_Core_Model_Resource_Db_Collection_Abstract
	 */
	protected function c() {return $this->getEvent()->getCollection();}

	/** @return Df_Core_Model_Event */
	protected function getEvent() {return $this->cfg(self::P__EVENT);}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->_prop(self::P__EVENT, $this->getEventClass());
	}

	const P__EVENT = 'event';
}