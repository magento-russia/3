<?php
class Df_Core_Model_Entity_Dependency extends Df_Core_Model {
	/** @return string */
	public function getActionSaveClassName() {
		return $this->cfg(self::P__ACTION_SAVE__CLASS_NAME);
	}

	/** @return string */
	public function getEntityClassName() {
		return $this->cfg(self::P__CLASS_NAME);
	}

	/** @return string */
	public function getEntityIdFieldName() {
		return $this->cfg(self::P__ID_FIELD_NAME);
	}

	/** @return string */
	public function getId() {
		return $this->getName();
	}

	/** @return string */
	public function getName() {
		return $this->cfg(self::P__NAME);
	}

	/** @return bool */
	public function needDeleteCascade() {
		return $this->cfg(self::P__DELETE_CASCADE, false);
	}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this
			->_prop(self::P__ACTION_SAVE__CLASS_NAME, DF_V_STRING_NE)
			->_prop(self::P__CLASS_NAME, DF_V_STRING_NE)
			->_prop(self::P__DELETE_CASCADE, DF_V_BOOL, false)
			->_prop(self::P__ID_FIELD_NAME, DF_V_STRING_NE)
			->_prop(self::P__NAME, DF_V_STRING_NE)
		;
	}

	/** @used-by Df_Core_Model_Entity_Dependency_Collection::itemClass() */

	const P__ACTION_SAVE__CLASS_NAME = 'action_save__class_name';
	const P__CLASS_NAME = 'class_name';
	const P__DELETE_CASCADE = 'delete_cascade';
	const P__ID_FIELD_NAME = 'id_field_name';
	const P__NAME = 'name';
	/**
	 * @static
	 * @param array(string => mixed) $parameters [optional]
	 * @return Df_Core_Model_Entity_Dependency
	 */
	public static function i(array $parameters = []) {return new self($parameters);}
}