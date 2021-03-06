<?php
use Mage_Core_Model_Store as Store;

/**
 * 2015-10-09
 * https://mage2.pro/t/128
 * https://github.com/magento/magento2/issues/2064
 *
 * @param string $key
 * @param null|string|int|Store $store [optional]
 * @param mixed|callable $default [optional]
 * @return array|string|null|mixed
 */
function df_cfg($key, $store = null, $default = null) {
	/** @var array|string|null|mixed $result */
	$result = df_store($store)->getConfig($key);
	return df_if(is_null($result) || '' === $result, $default, $result);
}

/** @return Df_Admin_Model_Settings */
function df_cfgr() {return Df_Admin_Model_Settings::s();}

/**
 * @param Mage_Core_Model_Config_Element|string|string[] $path
 * @return array(string => mixed)
 */
function df_config_a($path) {
	/** @var bool $pathIsArray */
	$pathIsArray = is_array($path);
	/** @var array(string => mixed) $result */
	if ($pathIsArray || (1 < func_num_args())) {
		/** @var string[] $pathA */
		$pathA = $pathIsArray ? $path : func_get_args();
		$result = df_config_a(df_cc_path($pathA));
	}
	else {
		/** @var Mage_Core_Model_Config_Element|null $node */
		/** @var string $hash */
		if (is_object($path)) {
			$hash = spl_object_hash($path);
			$node = $path;
		}
		else {
			df_param_string_not_empty($path, 0);
			$hash = $path;
			$node = df_config_node($path);
		}
		if (!$node) {
			$result = [];
		}
		else {
			/** @var array(string => array(string => mixed)) $cache */
			static $cache;
			if (!isset($cache[$hash])) {
				/**
				 * Вызываем именно @uses Varien_Simplexml_Element::asCanonicalArray(),
				 * а не @see Varien_Simplexml_Element::asArray(),
				 * потому что @see Varien_Simplexml_Element::asArray()
				 * делает то же, что и @uses Varien_Simplexml_Element::asCanonicalArray(),
				 * но дополнительно смотрит, есть ли у настроечных элементов атрибуты
				 * и при их наличии добавляет их в массив.
				 * Когда атрибутов у настроечных элементов заведомо нет,
				 * то выгоднее вызывывать @uses Varien_Simplexml_Element::asCanonicalArray() —
				 * этот метод работает быстрее, чем @see Varien_Simplexml_Element::asArray().
				 */
				$cache[$hash] = $node->asCanonicalArray();
				/**
				 * @uses Varien_Simplexml_Element::asCanonicalArray()
				 * может вернуть не только массив, но и строку.
				 * Обратите внимание, что если
				 * @uses Varien_Simplexml_Element::asCanonicalArray() возвращает массив,
				 * то этот массив — ассоциативный:
				 * его ключами являются имена настрочных узлов.
				 */
				df_result_array($cache[$hash]);
			}
			$result = $cache[$hash];
		}
	}
	return $result;
}

/**
 * @param string|string[] $path
 * @return Mage_Core_Model_Config_Element|null
 */
function df_config_node($path) {
	/** @var bool $pathIsArray */
	$pathIsArray = is_array($path);
	/** @var Mage_Core_Model_Config_Element|null $result */
	if ($pathIsArray || (1 < func_num_args())) {
		/** @var string[] $pathA */
		$pathA = $pathIsArray ? $path : func_get_args();
		$result = df_config_node(df_cc_path($pathA));
	}
	else {
		df_param_string_not_empty($path, 0);
		/**
		 * @uses Mage_Core_Model_Config::getNode() кэширует объект-результат,
		 * но не очень качественно: кэширует только секцию, а внутри секции
		 * кеширование отсутствует, там всегд выполняется @see Varien_Simplexml_Element::descend()
		 * Да и вообще дополнительный уровень кэширования не помешает.
		 */
		/** @var array(string => Mage_Core_Model_Config_Element|null) $cache */
		static $cache;
		if (!isset($cache[$path])) {
			$cache[$path] = df_n_set(df_ftn(Mage::getConfig()->getNode($path)));
		}
		$result = df_n_get($cache[$path]);
	}
	return $result;
}

/**
 * 2015-04-18
 * @used-by Df_Admin_Config_Backend::getModuleName()
 * @used-by Df_Admin_Config_Source::getSectionConfigNode()
 * @used-by Df_Adminhtml_Model_Config_Data::save_patchFor_1_4_0_1()
 * @used-by Df_Adminhtml_Model_Config_Data::save_patchFor_1_7_0_2()
 * @used-by Df_Logging_Model_Handler_Controllers::postDispatchConfigSave()
 * @used-by \Df\Shipping\Config\Backend\Validator\Strategy::moduleTitle()
 * @return Mage_Adminhtml_Model_Config
 */
function df_config_adminhtml() {
	static $result;
	if (!$result) {
		/** @var Mage_Adminhtml_Model_Config $result */
		$result = Mage::getSingleton('adminhtml/config');
		$result->getSections();
	}
	return $result;
}

/**
 * 2015-04-18
 * @used-by Df_Admin_Config_Backend::getFieldConfig()
 * @param string $path
 * @return Mage_Core_Model_Config_Element
 */
function df_config_adminhtml_field($path) {
	df_param_string_not_empty($path, 0);
	/** @var string[] $pathA */
	$pathA = explode('/', $path);
	df_assert_eq(3, count($pathA));
	/** @var string $pathFull */
	$pathFull = df_cc_path($pathA[0], 'groups', $pathA[1], 'fields', $pathA[2]);
	/** @var Mage_Core_Model_Config_Element $result */
	$result = df_config_adminhtml()->getSections()->descend($pathFull);
	df_assert($result);
	return $result;
}