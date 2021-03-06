<?php
/** @method Df_Catalog_Model_XmlExport_Catalog getDocument() */
abstract class Df_Catalog_Model_XmlExport_Category extends \Df\Xml\Generator\Part {
	/** @return Df_Catalog_Model_Category */
	protected function getCategory() {return $this->_getData(self::$P__CATEGORY);}

	/** @return Df_Catalog_Model_Category[] */
	protected function getChildren() {
		return df_nta($this->getCategory()->getData(
			Df_Catalog_Model_XmlExport_Catalog::CATEGORY__CHILDREN
		));
	}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this
			->_prop(self::$P__DOCUMENT, Df_Catalog_Model_XmlExport_Catalog::class)
			->_prop(self::$P__CATEGORY, Df_Catalog_Model_Category::class)
		;
	}

	/** @var string */
	private static $P__CATEGORY = 'product';

	/**
	 * @used-by \Df\C1\Cml2\Export\Processor\Catalog\Category::process()
	 * @param string $class
	 * @param Df_Catalog_Model_Category $category
	 * @param \Df\Xml\Generator\Document $document
	 * @return Df_Catalog_Model_XmlExport_Category
	 */
	protected static function ic(
		$class
		,Df_Catalog_Model_Category $category
		,\Df\Xml\Generator\Document $document
	) {
		return df_ic($class, __CLASS__, array(
			self::$P__DOCUMENT => $document, self::$P__CATEGORY => $category
		));
	}
}