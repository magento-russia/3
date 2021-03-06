<?php
/*
 * В Magento большинство событий относятся к одному и тому же классу Varien_Event_Observer.
 * Однако у разных событий — разные свойства.
 *
 * Поэтому мы для конкретного типа события пишем класс-обёртку.
 * Этот класс-обёртка должен:
 * 1) Явно объявлять свойства события и типы свойств
 * 2) Производить первичную проверку допустимости и совмемтности атриюутов события
 * 3) Возможно: предоставлять общие для всех событий данного типа методы.
 *
 * Классы-обёртки наследуются от Df_Core_Model_Event.
 *
 *
 * Обратите внимание, что «событие» и «обработчик события» — два разных объекта.
 * Это даёт возможность инкапсулировать программный код класса «событие»
 * и повторго использовать этот программный код для разных обработчиков событий
 *
 */
abstract class Df_Core_Model_Event extends Df_Core_Model {
	/**
	 * Создаёт обертку нужного класса $class для системного события $observer.
	 * Вы также можете передать в конструктор обёртки дополнительные параметры $additionalParams
	 *
	 * @static
	 * @param string $class
	 * @param Varien_Event_Observer $observer
	 * @param array $additionalParams
	 * @return Df_Core_Model_Event
	 */
	public static function create($class, Varien_Event_Observer $observer, $additionalParams = []) {
		df_param_string($class, 0);
		df_param_array($additionalParams, 2);
		$result =
			df_model(
				$class
				, array_merge(array(self::$P__OBSERVER => $observer)
				, $additionalParams)
			)
		;
		df_assert($result instanceof Df_Core_Model_Event);
		return $result;
	}

	/**
	 * Извлекает свойство системного события из объекта класса Varien_Event_Observer,
	 * вокруг которого мы сделали объект-обёртку
	 * @param string $paramName						Название свойства
	 * @param mixed  $defaultValue [optional]		Результат по умолчанию
	 * @return mixed
	 */
	public function getEventParam($paramName, $defaultValue = null) {
		df_param_string_not_empty($paramName, 0);
		/** @var mixed $result */
		$result = $this->getObserver()->getData($paramName);
		return $result ? $result : $defaultValue;
	}

	/** @return Varien_Event_Observer */
	public function getObserver() {return $this->cfg(self::$P__OBSERVER);}

	/**
	 * Ожидаемый системный тип события.
	 * Если при конструировании данного объекта будет использовано событие другого типа —
	 * это будет считаться ошибкой и объект возбудит исключительную ситуацию.
	 * @return string|null
	 */
	protected function getExpectedEventPrefix() {return null;}

	/**
	 * Ожидаемый системный тип события.
	 * Если при конструировании данного объекта будет использовано событие другого типа —
	 * это будет считаться ошибкой и объект возбудит исключительную ситуацию.
	 * @return string|null
	 */
	protected function getExpectedEventSuffix() {return null;}

	/**
	 * @override
	 * @return void
	 * @throws Mage_Core_Exception
	 */
	protected function _construct() {
		parent::_construct();
		$this
			->validateEventType()
			->_prop(self::$P__OBSERVER, 'Varien_Event_Observer')
		;
	}
	/** @var string */
	private static $P__OBSERVER = 'observer';

	/**
	 * @used-by Df_Catalog_Model_Event_AttributeSet_GroupAdded::i()
	 * @param $class
	 * @param Varien_Event_Observer $observer
	 * @return Df_Core_Model_Event
	 */
	protected static function ic($class, Varien_Event_Observer $observer) {
		return df_ic($class, __CLASS__, array(self::$P__OBSERVER => $observer));
	}

	/**
	 * @return $this
	 * @throws \Df\Core\Exception
	 */
	private function validateEventType() {
		/** @var string $eventType */
		$eventType = $this->getObserver()->getEvent()->getName();
		if (
				$this->getExpectedEventPrefix()
			&&
				!df_starts_with($eventType, $this->getExpectedEventPrefix())
		) {
			df_error(
				'Объект класса «%s» ожидает событие с приставкой «%s», но получил событие «%s».'
				,get_class($this)
				,$this->getExpectedEventPrefix()
				,$eventType
			);
		}
		if (
				$this->getExpectedEventSuffix()
			&&
				!df_ends_with($eventType, $this->getExpectedEventSuffix())
		) {
			df_error(
				'Объект класса «%s» ожидает событие с окончанием «%s», но получил событие «%s».'
				,get_class($this)
				,$this->getExpectedEventSuffix()
				,$eventType
			);
		}
		if (!$this->getExpectedEventPrefix() && !$this->getExpectedEventSuffix()) {
			df_error(
				'Программист! Укажи префикс или суффикс события для класса %s!'
				,get_class($this)
			);
		}
		return $this;
	}
}