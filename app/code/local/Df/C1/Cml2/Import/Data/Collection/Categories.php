<?php
namespace Df\C1\Cml2\Import\Data\Collection;
use Df\C1\Cml2\Import\Data\Entity\Category;
use Df\Xml\X;
class Categories extends \Df\C1\Cml2\Import\Data\Collection {
	/**
	 * @override
	 * @see \Df\Xml\Parser\Collection::itemClass()
	 * @return string
	 */
	protected function itemClass() {return Category::class;}

	/**
	 * @override
	 * @see \Df\Xml\Parser\Collection::itemPath()
	 * @return string|string[]
	 */
	protected function itemPath() {return $this->cfg(self::$P__PATH, 'Группы/Группа');}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->_prop(self::$P__PATH, DF_V_STRING, false);
	}
	/** @var string */
	private static $P__PATH = 'path';
	/**
	 * @used-by \Df\C1\Cml2\Import\Data\Entity\Category::getChildren()
	 * @used-by \Df\C1\Cml2\State\Import\Collections::getCategories()
	 * @static
	 * @param X $xml
	 * @param string|null $path [optional]
	 * @return self
	 */
	public static function i(X $xml, $path = null) {return new self([
		self::$P__E => $xml, self::$P__PATH => $path
	]);}
}