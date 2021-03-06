<?php
namespace Df\Core\Format;
class Date extends \Df_Core_Model {
	/** @return \Zend_Date */
	public function getDate() {
		return $this->cfg(self::P__DATE);
	}

	/** @return string */
	public function getDayWith2Digits() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_dts($this->getDate(), \Zend_Date::DAY);
		}
		return $this->{__METHOD__};
	}

	/** @return string */
	public function getInRussianFormat() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_dts($this->getDate(), self::FORMAT__RUSSIAN);
		}
		return $this->{__METHOD__};
	}

	/** @return string */
	public function getMonthInGenetiveCase() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = $this->getDate()->toString(\Zend_Date::MONTH_NAME, null, 'ru_RU');
		}
		return $this->{__METHOD__};
	}

	/** @return string */
	public function getYear() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_dts($this->getDate(), \Zend_Date::YEAR);
		}
		return $this->{__METHOD__};
	}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->_prop(self::P__DATE, 'Zend_Date');
	}

	const FORMAT__RUSSIAN = 'dd.MM.yyyy';
	const P__DATE = 'date';
	/**
	 * @static
	 * @param array(string => mixed) $parameters [optional]
	 * @return $this
	 */
	public static function i(array $parameters = []) {return new self($parameters);}
}