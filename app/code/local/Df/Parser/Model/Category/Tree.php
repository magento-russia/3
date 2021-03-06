<?php
class Df_Parser_Model_Category_Tree extends Df_Varien_Data_Tree {
	/**
	 * @param Df_Parser_Model_Category $category
	 * @param Df_Parser_Model_Category_Node|null $parent [optional]
	 * @return Df_Parser_Model_Category_Node
	 */
	public function createNodeRm(Df_Parser_Model_Category $category, $parent = null) {
		/** @var Df_Parser_Model_Category_Node $result */
		$result =
			new Df_Parser_Model_Category_Node(
				$data = array(
					'id' => $category->getId()
					,Df_Parser_Model_Category_Node::P__CATEGORY => $category
				)
				,$idField = 'id'
				,$this
				,$parent
			)
		;
		$this->addNode($result, $parent);
		return $result;
	}

	/** @used-by Df_Parser_Model_Category_Importer::_construct() */

	/**
	 * @static
	 * @param array(string => mixed) $parameters [optional]
	 * @return Df_Parser_Model_Category_Tree
	 */
	public static function i(array $parameters = []) {return new self($parameters);}
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}