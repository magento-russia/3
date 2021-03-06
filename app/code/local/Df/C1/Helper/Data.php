<?php
use Df\C1\Cml2\Session\ByCookie\C1 as SessionByCookieC1;
use Df\C1\Config\Api\General as S;
class Df_C1_Helper_Data extends Mage_Core_Helper_Abstract implements Df_Dataflow_Logger {
	/**
	 * @param int $attributeSetId
	 * @return void
	 */
	public function create1CAttributeGroupIfNeeded($attributeSetId) {
		df_param_integer($attributeSetId, 0);
		df_param_between($attributeSetId, 0, 1);
		df_h()->catalog()->product()->addGroupToAttributeSetIfNeeded(
			$attributeSetId
			,\Df\C1\C::PRODUCT_ATTRIBUTE_GROUP_NAME
			,$sortOrder = 2
		);
	}

	/**
	 * @param string $attributeLabel
	 * @param string|null $prefix [optional]
	 * @return string
	 */
	public function generateAttributeCode($attributeLabel, $prefix = null) {
		df_param_string_not_empty($attributeLabel, 0);
		return Df_Eav_Model_Entity_Attribute_Namer::i(
			$attributeLabel, array_filter(['rm_1c', $prefix])
		)->getResult();
	}

	/**
	 * @see Df_Dataflow_Logger::log()
	 * @override
	 * @param mixed[] $args
	 * @return void
	 */
	public function log(...$args) {
		if (S::s()->needLogging()) {
			self::logger()->log(df_format($args));
		}
	}
	
	/**
	 * @param mixed[] $args
	 * @return void
	 */
	public function logRaw(...$args) {
		if (S::s()->needLogging()) {
			self::logger()->logRaw(df_format($args));
		}
	}

	/**
	 * @used-by \Df\C1\Cml2\Action\Orders\Export::processFinish()
	 * @param string $path
	 * @param string $value
	 * @return void
	 */
	public function saveConfigValue($path, $value) {
		Mage::getConfig()->saveConfig(
			$path, $value, $scope = 'stores', $scopeId = df_state()->getStoreProcessed()->getId()
		);
		df_store()->setConfig($path, $value);
	}

	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}

	/**
	 * @used-by log()
	 * @used-by logRaw()
	 * @return Df_Core_Model_Logger
	 */
	private static function logger() {return dfcf(function() {
		/** @var string $fileName */
		$path = SessionByCookieC1::s()->getFileName_Log();
		if (!$path) {
			$path = df_file_name(Mage::getBaseDir('var') . '/log', S::s()->logName(), '.');
			SessionByCookieC1::s()->setFileName_Log($path);
		}
		return Df_Core_Model_Logger::s($path);
	});}
}