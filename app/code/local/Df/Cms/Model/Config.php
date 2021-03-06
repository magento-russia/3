<?php
class Df_Cms_Model_Config extends Df_Core_Model {
	/** @return bool */
	public function canCurrentUserDeletePage() {return $this->_isAllowedAction('delete');}
	/** @return bool */
	public function canCurrentUserDeleteRevision() {return $this->_isAllowedAction('delete_revision');}
	/** @return bool */
	public function canCurrentUserPublishRevision() {return $this->_isAllowedAction('publish_revision');}
	/** @return bool */
	public function canCurrentUserDeleteVersion() {return $this->canCurrentUserDeleteRevision();}
	/** @return bool */
	public function canCurrentUserSavePage() {return $this->_isAllowedAction('save');}
	/** @return bool */
	public function canCurrentUserSaveRevision() {return $this->_isAllowedAction('save_revision');}
	/** @return bool */
	public function canCurrentUserSaveVersion() {return $this->canCurrentUserSaveRevision();}

	/**
	 * Returns array of access levels which can be viewed by current user.
	 * @return string[]
	 */
	public function getAllowedAccessLevel() {
		/** @var string[] $result */
		$result = array(Df_Cms_Model_Page_Version::ACCESS_LEVEL_PUBLIC);
		if ($this->canCurrentUserPublishRevision()) {
			$result[]= Df_Cms_Model_Page_Version::ACCESS_LEVEL_PROTECTED;
		}
		return $result;
	}

	/**
	 * Get default value for versioning from configuration.
	 * @return bool
	 */
	public function getDefaultVersioningStatus() {
		return Mage::getStoreConfigFlag(self::XML_PATH_CONTENT_VERSIONING);
	}

	/** @return string[] */
	public function getPageRevisionControledAttributes() {
		return $this->_getRevisionControledAttributes('page');
	}

	/**
	 * Compare current user with passed owner of version or author of revision.
	 * @param $userId
	 * @return bool
	 */
	public function isCurrentUserOwner($userId) {return df_nat0($userId) === df_admin_id();}

	/**
	 * @param string $type
	 * @return string[]
	 */
	private function _getRevisionControledAttributes($type) {
		if (isset($this->_revisionControlledAttributes[$type])) {
			return $this->_revisionControlledAttributes[$type];
		}
		return [];
	}

	/**
	 * @param string $action
	 * @return bool
	 */
	private function _isAllowedAction($action) {return df_admin_allowed('cms/page/' . $action);}


	const XML_PATH_CONTENT_VERSIONING = 'df_cms/versioning/default';
	/** @var string[][] */
	protected $_revisionControlledAttributes = array('page' => array(
		'root_template'
		,'meta_keywords'
		,'meta_description'
		,'content_heading'
		,'content'
		,'layout_update_xml'
		,'custom_theme'
		,'custom_root_template'
		,'custom_layout_update_xml'
		,'custom_theme_from'
		,'custom_theme_to'
	));
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}