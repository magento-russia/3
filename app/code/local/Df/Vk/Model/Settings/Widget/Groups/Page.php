<?php
class Df_Vk_Model_Settings_Widget_Groups_Page extends Df_Core_Model_Settings {
	/** @return string */
	public function getColumn() {return $this->v($this->getConfigKey('column'));}
	/** @return boolean */
	public function getEnabled() {return $this->getYesNo($this->getConfigKey('show'));}
	/** @return int */
	public function getPosition() {return $this->int($this->getConfigKey('vertical_ordering'));}
	/** @return string */
	public function getType() {
		return $this->_type;
	}
	/** @var string */
	private $_type;

	/**
	 * @param string $type
	 * @return $this
	 */
	public function setType($type) {
		df_param_string($type, 0);
		$this->_type = $type;
		return $this;
	}

	/**
	 * @param string $uniquePart
	 * @return string
	 */
	private function getConfigKey($uniquePart) {
		df_param_string($uniquePart, 0);
		return df_cc_path('df_vk', 'groups', implode('_', array($this->getType(), 'page', $uniquePart)));
	}
	/**
	 * @static
	 * @param array(string => mixed) $parameters [optional]
	 * @return Df_Vk_Model_Settings_Widget_Groups_Page
	 */
	public static function i(array $parameters = []) {return new self($parameters);}
}