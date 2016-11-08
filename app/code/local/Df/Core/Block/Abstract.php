<?php
abstract class Df_Core_Block_Abstract extends Mage_Core_Block_Abstract {
	/**
	 * Этот метод отличается от методов @see getData(), @see offsetGet(), @see _getData()
	 * возможностью указать значение по умолчанию.
	 * @param string $key
	 * @param mixed $default [optional]
	 * @return mixed
	 */
	public function cfg($key, $default = null) {
		/** @var mixed $result */
		/**
		 * 2015-03-26
		 * Раньше здесь стоял вызов @see getData()
		 * Однако при новой реализации @see getData()
		 * разумнее вызывать сразу @uses offsetGet():
		 * нам тогда не приходится обрабатывать ситуацию с пустым ключом $key:
		 * при вызове @see cfg() ключ не может быть пустым.
		 *
		 * Обратите внимание, что вызывать @see _getData() здесь ошибочно,
		 * потому что тогда могут не сработать валидаторы и фильтры.
		 */
		$result = $this->offsetGet($key);
		// Некоторые фильтры заменяют null на некоторое другое значение,
		// поэтому обязательно учитываем равенство null
		// значения свойства ДО применения фильтров.
		//
		// Раньше вместо !is_null($result) стояло !$result.
		// !is_null выглядит логичней.
		//
		// 2015-02-10
		// Раньше код был таким:
		// $valueWasNullBeforeFilters = dfa($this->_valueWasNullBeforeFilters, $key, true);
		// return !is_null($result) && !$valueWasNullBeforeFilters ? $result : $default;
		// Изменил его ради ускорения.
		// Неожиданным результатом стала простота и понятность нового кода.
		return
			null === $result
			|| !isset($this->_valueWasNullBeforeFilters[$key])
			|| $this->_valueWasNullBeforeFilters[$key]
			? $default
			: $result
		;
	}

	/**
	 * @override
	 * @return string
	 */
	public function getCacheKey() {
		return !$this->needToShow() ? Df_Core_Block_Template::CACHE_KEY_EMPTY : parent::getCacheKey();
	}

	/**
	 * 2015-03-11
	 * Обратите внимание, что в Magento CE 1.4.0.1
	 * метод @see Mage_Core_Block_Abstract::getCacheKeyInfo() отсутствует
	 * (он появился в Magento CE 1.4.1.0).
	 *  Нас это не очень волнует, потому что родительский метод мы не вызываем.
	 * @override
	 * @see Mage_Core_Block_Abstract::getCacheKeyInfo()
	 * @return string[]
	 */
	public function getCacheKeyInfo() {
		if (!isset($this->{__METHOD__})) {
			/** @var string[] $result */
			$result = array(get_class($this), $this->getNameInLayout(), df_store()->getCode());
			if (isset($this->_nameInLayout) && $this->_nameInLayout) {
				$result[]= $this->_nameInLayout;
			}
			if ($this->shouldCachePerRequestAction()) {
				/**
				 * 2015-08-25
				 * Раньше тут стояло:
				 * $result[]= df_action_name();
				 * Крайне неряшливый модуль Ves_Blog
				 * оформительской темы Ves Super Store (ThemeForest 8002349)
				 * ломает инициализацию системы, и в данной точке программы
				 * контроллер может быть ещё не инициализирован.
				 */
				/** @var string $a */
				$a = df_action_name();
				$result[]= $a ? $a : df_current_url();
			}
			/** @var string|string[] $suffix */
			$suffix = $this->cacheKeySuffix();
			$this->{__METHOD__} = !$suffix ? $result : array_merge($result, df_array($suffix));
		}
		return $this->{__METHOD__};
	}

	/**
	 * @override
	 * Обратите внимание, что мы сознательно никак не используем параметр $index
	 * и не поддерживаем сложные ключи $key, как это делает родительский метод.
	 * @see Varien_Object::getData()
	 * @param string $key
	 * @param null|string|int $index
	 * @return mixed
	 */
	public function getData($key = '', $index = null) {
		/** @var mixed $result */
		if ('' === $key) {
			/**
			 * Фильтры и валидаторы для присутствующих в @see $_data ключей
			 * уже были применены при вызове @see _prop(),
			 * поэтому данные уже проверены и отфильтрованы.
			 */
			$result = $this->_data;
		}
		else if (array_key_exists($key, $this->_data)) {
			/**
			 * Фильтры и валидаторы для присутствующих в @see $_data ключей
			 * уже были применены при вызове @see _prop(),
			 * поэтому данные уже проверены и отфильтрованы.
			 */
			$result = $this->_data[$key];
		}
		else {
			// Обрабатываем здесь только те случаи,
			// когда запрашиваются значения неицициализированных свойств объекта
			$result = $this->_applyFilters($key, null);
			// Обратите внимание, что фильтры и валидаторы применяются только единократно,
			// потому что повторно мы в эту ветку кода не попадём
			// из-за срабатывания условия array_key_exists($key, $this->_data) выше
			// (даже если фильтры для null вернут null, наличие ключа array('ключ' => null))
			// достаточно, чтобы не попадать в данную точку программы повторно.
			$this->_validate($key, $result);
			$this->_data[$key] = $result;
		}
		return $result;
	}

	/**
	 * @override
	 * @see Varien_Object::getId()
	 * @used-by Varien_Data_Collection::addItem()
	 * @return string|int
	 */
	public function getId() {
		return
			empty($this->_idFieldName) && is_null($this->_getData('id'))
			? $this->getAutoGeneratedId()
			: parent::getId()
		;
	}

	/**
	 * @override
	 * @used-by getData()
	 * @see Varien_Object::offsetGet()
	 * @see ArrayAccess::offsetGet()
	 * @param string $offset
	 * @return mixed
	*/
	public function offsetGet($offset) {
		/** @var mixed $result */
		if (array_key_exists($offset, $this->_data)) {
			/**
			 * Фильтры и валидаторы для присутствующих в @see $_data ключей
			 * уже были применены при вызове @see _prop(),
			 * поэтому данные уже проверены и отфильтрованы.
			 */
			$result = $this->_data[$offset];
		}
		else {
			// Обратите внимание, что фильтры и валидаторы применяются только единократно,
			// и повторно мы в эту ветку кода не попадём
			// из-за срабатывания условия array_key_exists($key, $this->_data) выше
			// (даже если фильтры для null вернут null, наличие ключа array('ключ' => null))
			// достаточно, чтобы не попадать в данную точку программы повторно.
			//
			// Обрабатываем здесь только те случаи,
			// когда запрашиваются значения неицициализированных свойств объекта
			$result = $this->_applyFilters($offset, null);
			$this->_validate($offset, $result);
			$this->_data[$offset] = $result;
		}
		return $result;
	}

	/**
	 * @override
	 * @see Varien_Object::setData()
	 * @param string|array(string => mixed) $key
	 * @param mixed $value
	 * @return $this
	 */
	public function setData($key, $value = null) {
		/**
		 * Раньше мы проводили валидацию лишь при извлечении значения свойства,
		 * в методе @see Df_Core_Block_Abstract::getData().
		 * Однако затем мы сделали улучшение:
		 * перенести валидацию на более раннюю стадию — инициализацию свойства
		 * @see Df_Core_Block_Abstract::setData(),
		 * и инициализацию валидатора/фильтра
		 * @see Df_Core_Block_Abstract::_prop().
		 * Это улучшило диагностику случаев установки объекту некорректных значений свойств,
		 * потому что теперь мы возбуждаем исключительную ситуацию
		 * сразу при попытке установки некорректного значения.
		 * А раньше, когда мы проводили валидацию лишь при извлечении значения свойства,
		 * то при диагностике было не вполне понятно,
		 * когда конкретно объекту было присвоено некорректное значение свойства.
		 */
		if (is_array($key)) {
			$this->_checkForNullArray($key);
			$key = $this->_applyFiltersToArray($key);
			$this->_validateArray($key);
		}
		else {
			$this->_checkForNull($key, $value);
			$value = $this->_applyFilters($key, $value);
			$this->_validate($key, $value);
		}
		parent::setData($key, $value);
		return $this;
	}

	/**
	 * 2015-03-12
	 * Как ни странно, перекрытие именно метода @see Mage_Core_Block_Abstract::_loadCache()
	 * является наиболее эффективным применением @uses needToShow()
	 * Обратите внимание, что метод @used-by Mage_Core_Block_Abstract::toHtml()
	 * перекрыть невозможно, потому что он объвлен как final.
	 * @see Mage_Core_Block_Abstract::_loadCache() используется только из
	 * @see Mage_Core_Block_Abstract::toHtml()
	 * _loadCache() вызывается только из единственной точки системы:
	 * из @see Mage_Core_Block_Abstract::toHtml()
	 * Это гарантирует нам, что система не будет тратить ресурсы
	 * на повторные вызовы @see needToShow()
	 * @override
	 * @see Mage_Core_Block_Abstract::_loadCache()
	 * @used-by Mage_Core_Block_Abstract::toHtml()
	 * @return string|bool
	 */
	protected function _loadCache()	{return !$this->needToShow() ? '' : parent::_loadCache();}

	/**
	 * @param string $key
	 * @param Zend_Validate_Interface|\Df\Zf\Validate\Type|string|mixed[] $validator
	 * @param bool|null $isRequired [optional]
	 * @throws \Df\Core\Exception
	 * @return $this
	 */
	protected function _prop($key, $validator, $isRequired = null) {
		/**
		 * Полезная проверка!
		 * Как-то раз ошибочно описал поле без значения:
			private static $P__TYPE;
		 * И при вызове $this->_prop(self::$P__TYPE, DF_V_STRING_NE)
		 * получил диагностическое сообщение: «значение «» недопустимо для свойства «».»
		 */
		df_param_string_not_empty($key, 0);
		/**
		 * Обратите внимание, что если метод @see _prop() был вызван с двумя параметрами,
		 * то и count($arguments) вернёт 2,
		 * хотя в методе @see _prop() всегда доступен и 3-х параметр: $isRequired.
		 * Другими словами, @see func_get_args() не возвращает параметры по умолчанию,
		 * если они не были реально указаны при вызове текущего метода.
		 */
		/**
		 * Хотя документация к PHP говорит,
		 * что @uses func_num_args() быть параметром других функций лишь с версии 5.3 PHP,
		 * однако на самом деле @uses func_num_args() быть параметром других функций
		 * в любых версиях PHP 5 и даже PHP 4.
		 * http://3v4l.org/HKFP7
		 * http://php.net/manual/function.func-num-args.php
		 */
		if (2 < func_num_args()) {
			/** @var mixed[] $arguments */
			$arguments = func_get_args();
			$isRequired = df_last($arguments);
			/** @var bool $hasRequiredFlag */
			$hasRequiredFlag = is_bool($isRequired) || is_null($isRequired);
			if ($hasRequiredFlag) {
				$validator = array_slice($arguments, 1, -1);
			}
			else {
				$isRequired = null;
				$validator = df_tail($arguments);
			}
		}
		/** @var Zend_Validate_Interface[] $additionalValidators */
		$additionalValidators = [];
		/** @var Zend_Filter_Interface[] $additionalFilters */
		$additionalFilters = [];
		if (!is_array($validator)) {
			$validator = \Df\Core\Validator::resolveForProperty(
				$this, $validator, $key, $skipOnNull = false === $isRequired
			);
			df_assert($validator instanceof Zend_Validate_Interface);
		}
		else {
			/** @var array(Zend_Validate_Interface|\Df\Zf\Validate\Type|string) $additionalValidatorsRaw */
			$additionalValidatorsRaw = df_tail($validator);
			$validator = \Df\Core\Validator::resolveForProperty(
				$this, df_first($validator), $key, $skipOnNull = false === $isRequired
			);
			df_assert($validator instanceof Zend_Validate_Interface);
			foreach ($additionalValidatorsRaw as $additionalValidatorRaw) {
				/** @var Zend_Validate_Interface|Zend_Filter_Interface|string $additionalValidatorsRaw */
				/** @var Zend_Validate_Interface|Zend_Filter_Interface $additionalValidator */
				$additionalValidator = \Df\Core\Validator::resolveForProperty(
					$this, $additionalValidatorRaw, $key
				);
				if ($additionalValidator instanceof Zend_Validate_Interface) {
					$additionalValidators[]= $additionalValidator;
				}
				if ($additionalValidator instanceof Zend_Filter_Interface) {
					$additionalFilters[]= $additionalValidator;
				}
			}
		}
		$this->_validators[$key][] = $validator;
		if ($validator instanceof Zend_Filter_Interface) {
			/** @var Zend_Filter_Interface $filter */
			$filter = $validator;
			$this->_addFilter($key, $filter);
		}
		foreach ($additionalFilters as $additionalFilter) {
			/** @var Zend_Filter_Interface $additionalFilter */
			$this->_addFilter($key, $additionalFilter);
		}
		/**
		 * Раньше мы проводили валидацию лишь при извлечении значения свойства,
		 * в методе @see getData().
		 * Однако затем мы сделали улучшение:
		 * перенести валидацию на более раннюю стадию — инициализацию свойства @see setData(),
		 * и инициализацию валидатора/фильтра @see _prop().
		 * Это улучшило диагностику случаев установки объекту некорректных значений свойств,
		 * потому что теперь мы возбуждаем исключительную ситуацию
		 * сразу при попытке установки некорректного значения.
		 * А раньше, когда мы проводили валидацию лишь при извлечении значения свойства,
		 * то при диагностике было не вполне понятно,
		 * когда конкретно объекту было присвоено некорректное значение свойства.
		 */
		/** @var bool $hasValueVorTheKey */
		$hasValueVorTheKey = array_key_exists($key, $this->_data);
		if ($hasValueVorTheKey) {
			\Df\Core\Validator::checkProperty($this, $key, $this->_data[$key], $validator);
		}
		foreach ($additionalValidators as $additionalValidator) {
			/** @var Zend_Validate_Interface $additionalValidator */
			$this->_validators[$key][] = $additionalValidator;
			if ($hasValueVorTheKey) {
				\Df\Core\Validator::checkProperty($this, $key, $this->_data[$key], $additionalValidator);
			}
		}
		return $this;
	}

	/** @return bool */
	protected function canCache() {return true;}

	/**
	 * @override
	 * @return int|null|bool
	 */
	public function getCacheLifetime() {
		return $this->canCache() ? Df_Core_Block_Template::CACHE_LIFETIME_STANDARD : null;
	}

	/**
	 * @used-by getCacheKeyInfo()
	 * @return string|string[]
	 */
	protected function cacheKeySuffix() {return [];}

	/**
	 * 2015-04-21
	 * Для иерархической декомпозиции сложных блоков.
	 * @see parent()
	 * @return Mage_Core_Block_Abstract
	 */
	protected function grandGrandParent() {return $this->grandParent()->getParentBlock();}

	/**
	 * 2015-04-02
	 * Для иерархической декомпозиции сложных блоков.
	 * @see parent()
	 * @return Mage_Core_Block_Abstract
	 */
	protected function grandParent() {return $this->parent()->getParentBlock();}

	/**
	 * @used-by _loadCache()
	 * @used-by getCacheKey()
	 * @return bool
	 */
	protected function needToShow() {return true;}

	/**
	 * 2015-04-01
	 * Для иерархической декомпозиции сложных блоков.
	 * @see grandParent()
	 * @return Mage_Core_Block_Abstract
	 */
	protected function parent() {return $this->getParentBlock();}

	/**
	 * @used-by getCacheKeyInfo()
	 * @return bool
	 */
	protected function shouldCachePerRequestAction() {return false;}

	/**
	 * @param string $key
	 * @param Zend_Filter_Interface $filter
	 * @return void
	 */
	private function _addFilter($key, Zend_Filter_Interface $filter) {
		$this->_filters[$key][] = $filter;
		/**
		 * Не используем @see isset(), потому что для массива
		 * $array = array('a' => null)
		 * isset($array['a']) вернёт false,
		 * что не позволит нам фильтровать значения параметров,
		 * сознательно установленные в null при конструировании объекта.
		 */
		if (array_key_exists($key, $this->_data)) {
			$this->_data[$key] = $filter->filter($this->_data[$key]);
		}
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return mixed
	 */
	private function _applyFilters($key, $value) {
		/** @var Zend_Filter_Interface[] $filters */
		/** @noinspection PhpParamsInspection */
		$filters = dfa($this->_filters, $key, []);
		foreach ($filters as $filter) {
			/** @var Zend_Filter_Interface $filter */
			$value = $filter->filter($value);
		}
		return $value;
	}

	/**
	 * @param array(string => mixed) $params
	 * @return array(string => mixed)
	 */
	private function _applyFiltersToArray(array $params) {
		foreach ($params as $key => $value) {
			/** @var string $key */
			/** @var mixed $value */
			$params[$key] = $this->_applyFilters($key, $value);
		}
		return $params;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	private function _checkForNull($key, $value) {
		$this->_valueWasNullBeforeFilters[$key] = is_null($value);
	}

	/**
	 * @param array(string => mixed) $params
	 * @return void
	 */
	private function _checkForNullArray(array $params) {
		foreach ($params as $key => $value) {
			/** @var string $key */
			/** @var mixed $value */
			$this->_checkForNull($key, $value);
		}
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @throws \Df\Core\Exception
	 * @return void
	 */
	private function _validate($key, $value) {
		/** @var @var array(Zend_Validate_Interface|\Df\Zf\Validate\Type) $validators */
		/** @noinspection PhpParamsInspection */
		$validators = dfa($this->_validators, $key, []);
		foreach ($validators as $validator) {
			/** @var Zend_Validate_Interface|\Df\Zf\Validate\Type $validator */
			\Df\Core\Validator::checkProperty($this, $key, $value, $validator);
		}
	}

	/**
	 * @param array(string => mixed) $params
	 * @throws \Df\Core\Exception
	 * @return void
	 */
	private function _validateArray(array $params) {
		foreach ($params as $key => $value) {
			/** @var string $key */
			/** @var mixed $value */
			$params[$key] = $this->_validate($key, $value);
		}
	}

	/** @return string */
	private function getAutoGeneratedId() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_uid();
		}
		return $this->{__METHOD__};
	}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		if ($this->_data) {
			$this->_checkForNullArray($this->_data);
			/**
			 * Фильтры мы здесь пока применять не можем,
			 * потому что они ещё не инициализированы
			 * (фильтры будут инициализированы потомками
			 * уже после вызова @see Df_Core_Block_Abstract::_construct()).
			 * Вместо этого применяем фильтры для начальных данных
			 * в методе @see Df_Core_Block_Abstract::_prop(),
			 * а для дополнительных данных — в методе @see Df_Core_Block_Abstract::setData().
			 */
		}
		parent::_construct();
	}

	/** @var array(string => Zend_Filter_Interface[]) */
	private $_filters = [];
	/** @var array(string => Zend_Validate_Interface[]) */
	private $_validators = [];
	/** @var array(string => bool) */
	private $_valueWasNullBeforeFilters = [];

	/**
	 * @param string $class
	 * @param string|string[] $functions
	 * @return string[]
	 */
	protected static function m($class, $functions) {
		df_assert($functions);
		/** @var string[] $result */
		$result = [];
		if (!is_array($functions)) {
			/** @var mixed[] $arguments */
			$arguments = func_get_args();
			$functions = df_tail($arguments);
		}
		foreach ($functions as $function) {
			/** @var string $function */
			$result[]= $class . '::' . $function;
		}
		return $result;
	}
}