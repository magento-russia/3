<?php
class Df_Cms_Model_Resource_Page_Version extends Df_Core_Model_Resource {
	/**
	 * Checking if version id not last public for its page
	 *
	 * @param Mage_Core_Model_Abstract $object
	 * @return bool
	 */
	public function isVersionLastPublic(Mage_Core_Model_Abstract $object)
	{
		$select = $this->_getReadAdapter()->select();
		$select->from($this->getMainTable(), 'count(*)')
			->where('page_id = ?', $object->getPageId())
			->where('access_level = ?', Df_Cms_Model_Page_Version::ACCESS_LEVEL_PUBLIC)
			->where('version_id <> ? ', $object->getVersionId());
		return !$this->_getReadAdapter()->fetchOne($select);
	}

	/**
	 * Checking if Version does not contain published revision
	 *
	 * @param Mage_Core_Model_Abstract $object
	 * @return bool
	 */
	public function isVersionHasPublishedRevision(Mage_Core_Model_Abstract $object)
	{
		$select = $this->_getReadAdapter()->select();
		$select->from(array('p' => df_table('cms/page')), null)
			->where('p.page_id = ?', $object->getPageId())
			->join(
				array('r' => df_table(Df_Cms_Model_Resource_Page_Revision::TABLE))
				,'r.revision_id = p.published_revision_id'
				, 'r.version_id'
			)
		;
		$result = $this->_getReadAdapter()->fetchOne($select);
		return $result === $object->getVersionId();
	}

	/**
	 * Add access restriction filters to allow load only by granted user.
	 *
	 * @param Zend_Db_Select $select
	 * @param int $accessLevel
	 * @param int $userId
	 * @return Zend_Db_Select
	 */
	protected function _addAccessRestrictionsToSelect($select, $accessLevel, $userId)
	{
		$conditions = array('user_id = ' . $userId);
		if (is_array($accessLevel) && !empty($accessLevel)) {
			$conditions[]= 'access_level in ("' . implode('","', $accessLevel) . '")';
		} else if ($accessLevel) {
			$conditions[]= 'access_level = "' . $accessLevel . '"';
		} else {
			$conditions[]= 'access_level = ""';
		}

		$conditions = implode(' OR ', $conditions);
		$select->where($conditions);
		return $select;
	}

	/**
	 * Loading data with extra access level checking.
	 *
	 * @param Df_Cms_Model_Page_Version $object
	 * @param array|string $accessLevel
	 * @param int $userId
	 * @param int|string $value
	 * @param string|null $field
	 * @return $this
	 */
	public function loadWithRestrictions($object, $accessLevel, $userId, $value, $field = null)
	{
		if (is_null($field)) {
			$field = $this->getIdFieldName();
		}

		$read = $this->_getReadAdapter();
		if ($read && $value) {
			$select = $this->_getLoadSelect($field, $value, $object);
			$select = $this->_addAccessRestrictionsToSelect($select, $accessLevel, $userId);
			$data = $read->fetchRow($select);
			if ($data) {
				$object->setData($data);
			}
		}
		$this->_afterLoad($object);
		return $this;
	}

	/**
	 * Нельзя вызывать @see parent::_construct(),
	 * потому что это метод в родительском классе — абстрактный.
	 * @see Mage_Core_Model_Resource_Abstract::_construct()
	 * @override
	 * @return void
	 */
	protected function _construct() {$this->_init(self::TABLE, Df_Cms_Model_Page_Version::P__ID);}
	/**
	 * @used-by Df_Cms_Model_Resource_Page_Revision::_construct()
	 * @used-by Df_Cms_Model_Resource_Page_Revision::_aggregateVersionData()
	 * @used-by Df_Cms_Model_Resource_Page_Revision_Collection::joinVersions()
	 * @used-by Df_Cms_Setup_2_0_0::_process()
	 */
	const TABLE = 'df_cms/page_version';
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}