<?php
class Df_Catalog_Model_ConditionsLoader extends Df_Core_Model {
	/** @return Mage_CatalogRule_Model_Rule|null */
	public function getRule() {
		if (!isset($this->{__METHOD__})) {
			/** @var Mage_CatalogRule_Model_Rule|null $result */
			if (!$this->getRuleId()) {
				$result = null;
			}
			else {
				df_nat($this->getRuleId());
				$result = df_model('catalogrule/rule');
				$result->load($this->getRuleId());
				if (!$result->getId()) {
					df_error(
						'Кто-то удалил используемое модулем «{moduleName}» ценовое правило для каталога.'
						."\nПеренастройте его заново в административном разделе"
						."\n«Система» → «Настройки» → {settingsPath}."
						,array(
							'{moduleName}' => $this->getModuleName()
							, '{settingsPath}' => $this->getSettingsPath()
						)
					);
				}
			}
			$this->{__METHOD__} = df_n_set($result);
		}
		return df_n_get($this->{__METHOD__});
	}

	/** @return string */
	private function getModuleName() {return $this->cfg(self::$P__MODULE_NAME);}

	/** @return int */
	private function getRuleId() {return $this->cfg(self::$P__RULE_ID);}

	/** @return string */
	private function getSettingsPath() {return $this->cfg(self::$P__SETTINGS_PATH);}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this
			->_prop(self::$P__MODULE_NAME, DF_V_STRING)
			->_prop(self::$P__RULE_ID, DF_V_NAT0)
			->_prop(self::$P__SETTINGS_PATH, DF_V_STRING)
		;
	}

	/** @var string */
	private static $P__MODULE_NAME = 'module_name';
	/** @var string */
	private static $P__RULE_ID = 'rule_id';
	/** @var string */
	private static $P__SETTINGS_PATH = 'settings_path';

	/**
	 * @param int $ruleId
	 * @param string $moduleName
	 * @param string $settingsPath
	 * @return Df_Catalog_Model_ConditionsLoader
	 */
	public static function i($ruleId, $moduleName, $settingsPath) {
		return new self(array(
			self::$P__RULE_ID => $ruleId
			, self::$P__MODULE_NAME => $moduleName
			, self::$P__SETTINGS_PATH => $settingsPath
		));
	}
}