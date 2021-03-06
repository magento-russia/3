<?php
class Df_Adminhtml_Model_Translator_Config extends Df_Core_Model {
	/**
	 * @param string $sectionName
	 * @return string|null
	 */
	public function getHelperModuleMf($sectionName) {return dfa($this->getMap(), $sectionName);}

	/** @return array(string => string) */
	private function getMap() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_config_a('adminhtml/translate/sections');
		}
		return $this->{__METHOD__};
	}

	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}