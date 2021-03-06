<?php
use Mage_Core_Model_Abstract as M;

/**
 * @param Varien_Object|object $object
 * @param string[] $absentProperties [optional]
 * @return Varien_Object
 */
function df_adapt_legacy_object(Varien_Object $object, $absentProperties = []) {
	foreach ($object->getData() as $key => $value) {
		/** @var string $key */
		/** @var mixed $value */
		if (!isset($object->$key)) {
			$object->$key = $value;
		}
	}
	/**
	 * Позволяет инициализировать те поля, которые отсутствуют в @uses Varien_Object::getData(),
	 * но обращение к которым, тем не менее, происходит в дефектных оформительских темах.
	 */
	if ($absentProperties) {
		foreach ($absentProperties as $absentProperty) {
			/** @var string $absentProperty */
			if (!isset($object->$absentProperty)) {
				$object->$absentProperty = $object->getData($absentProperty);
			}
		}
	}
	return $object;
}

/**
 * 2016-05-06
 * @param string $type
 * @return bool
 */
function df_class_exists($type) {return @class_exists($type);}

/**
 * @see df_sc()
 * @param string $resultClass
 * @param string|null|array(string => mixed) $a2 [optional]
 * @param array(string => mixed) $a3 [optional]
 * @return Varien_Object|object
 */
function df_ic($resultClass, $a2 = null, array $a3 = []) {
	/** @var string|null $expectedClass */
	/** @var array(string => mixed) $params */
	list($expectedClass, $params) = is_array($a2) ? [null, $a2] : [$a2, $a3];
	return df_ar(new $resultClass($params), $expectedClass);
}

/**
 * 2016-08-24
 * 2016-09-04
 * Метод getId присутствует не только у потомков @see \Mage_Core_Model_Abstract,
 * но и у классов сторонних библиотек, например:
 * https://github.com/CKOTech/checkout-php-library/blob/v1.2.4/com/checkout/ApiServices/Charges/ResponseModels/Charge.php?ts=4#L170-L173
 * По возможности, задействуем и сторонние реализации.
 *
 * К сожалению, нельзя здесь для проверки публичности метода использовать @see is_callable(),
 * потому что наличие @see Varien_Object::__call()
 * приводит к тому, что @see is_callable всегда возвращает true.
 * Обратите внимание, что @uses method_exists(), в отличие от @see is_callable(),
 * не гарантирует публичную доступность метода:
 * т.е. метод может у класса быть, но вызывать его всё равно извне класса нельзя,
 * потому что он имеет доступность private или protected.
 * Пока эта проблема никак не решена.
 *
 * 2016-09-05
 * Этот код прекрасно работает с объектами классов типа @see Mage_Directory_Model_Currency
 * благодаря тому, что @uses \Mage_Core_Model_Abstract::getId()
 * не просто тупо считывает значение поля id, а вызывает метод
 * @see \Mage_Core_Model_Abstract::getIdFieldName()
 * который, в ссвою очередь, узнаёт имя идентифицирующего поля из своего ресурса:
 * @see \Mage_Core_Model_Abstract::_init()
 * @see Mage_Directory_Model_Resource_Currency::_construct()
 *
 * @see dfo_hash() использует тот же алгоритм, но не вызывает @see df_id() ради ускорения.
 *
 * @param object|int|string $o
 * @param bool $allowNull [optional]
 * @return int|string|null
 */
function df_id($o, $allowNull = false) {
	/** @var int|string|null $result */
	$result = !is_object($o) ? $o : (
		$o instanceof M || method_exists($o, 'getId') ? $o->getId() : null
	);
	df_assert($allowNull || $result);
	return $result;
}

/**
 * 2016-09-05
 * @param object|int|string $o
 * @param bool $allowNull [optional]
 * @return int
 */
function df_idn($o, $allowNull = false) {return df_nat(df_id($o, $allowNull), $allowNull);}

/**
 * 2015-03-23
 * @see df_ic()
 * @param string $resultClass
 * @param string $expectedClass
 * @param array(string => mixed) $params [optional]
 * @param string $cacheKeySuffix [optional]
 * @return Varien_Object|object
 */
function df_sc($resultClass, $expectedClass, array $params = [], $cacheKeySuffix = '') {
	/** @var array(string => object) $cache */
	static $cache;
	/** @var string $key */
	$key = $resultClass . $cacheKeySuffix;
	if (!isset($cache[$key])) {
		$cache[$key] = df_ic($resultClass, $expectedClass, $params);
	}
	return $cache[$key];
}

/**
 * @param object|Varien_Object $entity
 * @param string $key
 * @param mixed $default
 * @return mixed|null
 */
function dfo($entity, $key, $default = null) {
	/**
	 * Раньше функция @see dfa() была универсальной:
	 * она принимала в качестве аргумента $entity как массивы, так и объекты.
	 * В 99.9% случаев в качестве параметра передавался массив.
	 * Поэтому ради ускорения работы системы
	 * вынес обработку объектов в отдельную функцию @see dfo()
	 */
	/** @var mixed $result */
	if (!is_object($entity)) {
		df_error('Попытка вызова df_o для переменной типа «%s».', gettype($entity));
	}
	if ($entity instanceof Varien_Object) {
		$result = $entity->getData($key);
		if (is_null($result)) {
			$result = df_call_if($default, $key);
		}
	}
	else {
		/**
		 * Например, @see stdClass.
		 * Используется, например, методом
		 * @used-by \Df\Qiwi\Action\Confirm::updateBill()
		 */
		$result = isset($entity->{$key}) ? $entity->{$key} : df_call_if($default, $key);
	}
	return $result;
}