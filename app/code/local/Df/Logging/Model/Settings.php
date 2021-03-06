<?php
class Df_Logging_Model_Settings extends Df_Core_Model_Settings {
	/** @return Df_Logging_Model_Settings_Archiving */
	public function archiving() {return Df_Logging_Model_Settings_Archiving::s();}
	/** @return string|null */
	public function getActions() {return $this->v('actions/actions');}
	/** @return boolean */
	public function isEnabled() {return $this->getYesNo('archiving/enabled');}
	/**
	 * @override
	 * @return string
	 */
	protected function getKeyPrefix() {return 'df_tweaks_admin/logging__';}
	/**
	 * @used-by Df_Admin_Model_Settings_Admin::logging()
	 * @return Df_Logging_Model_Settings
	 */
	public static function s() {static $r; return $r ?: $r = new self;}
}