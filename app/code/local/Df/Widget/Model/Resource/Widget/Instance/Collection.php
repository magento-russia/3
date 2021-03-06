<?php
class Df_Widget_Model_Resource_Widget_Instance_Collection
	extends Mage_Widget_Model_Resource_Widget_Instance_Collection {
	/**
	 * @override
	 * @param Mage_Core_Model_Resource_Db_Abstract|array(string => mixed) $resource
	 */
	public function __construct($resource = null) {
		if (is_array($resource)) {
			$this->_rmData = $resource;
			$resource = null;
		}
		parent::__construct($resource);
	}

	/**
	 * @override
	 * @return Df_Widget_Model_Resource_Widget_Instance
	 */
	public function getResource() {return Df_Widget_Model_Resource_Widget_Instance::s();}

	/**
	 * @param string|null $paramName [optional]
	 * @return mixed
	 */
	public function getRmData($paramName = null) {
		return is_null($paramName) ?  $this->_rmData : dfa($this->_rmData, $paramName);
	}

	/**
	 * @override
	 * @return $this
	 */
	protected function _afterLoad() {
		parent::_afterLoad();
		if ($this->isForUpdating()) {
			foreach ($this->_items as $widgetInstance) {
				/** @var Df_Widget_Model_Widget_Instance $widgetInstance */
				Df_Widget_Model_Resource_Widget_Instance::s()->afterLoad($widgetInstance);
				$widgetInstance->skipSaveRelations();
				$widgetInstance->setDataChanges(false);
			}
		}
		return $this;
	}

	/**
	 * @override
	 * @return bool
	 */
	private function isForUpdating() {return $this->getRmData(self::$P__FOR_UPDATING);}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {$this->_itemObjectClass = Df_Widget_Model_Widget_Instance::class;}
	/** @var array(string => mixed) */
	private $_rmData = [];
	
	/** @var string */
	private static $P__FOR_UPDATING = 'for_updating';

	/**
	 * @param bool $forUpdating [optional]
	 * @return Df_Widget_Model_Resource_Widget_Instance_Collection
	 */
	public static function i($forUpdating = false) {
		return new self(array(self::$P__FOR_UPDATING => $forUpdating));
	}
} 