<?php
class Df_Cms_Model_Resource_Block extends Mage_Cms_Model_Resource_Block {
	/** @return Df_Cms_Model_Resource_Block_Collection */
	public function findOrphanBlocks() {
		/** @var Df_Cms_Model_Resource_Block_Collection $result */
		$result = Df_Cms_Model_Resource_Block_Collection::i();
		$result->addFieldToFilter('block_id', array('in' => $this->findOrphanIds()));
		return $result;
	}

	/**
	 * Возвращает идентификаторы блоков, не привязанных ни к одной из витрин.
	 * @return int[]
	 */
	public function findOrphanIds() {
		return df_conn()->fetchCol(
			df_select()
				->from(array('b' => df_table('cms_block')), 'block_id')
				->joinLeft(
					array('bs' => df_table('cms_block_store'))
					,'b.block_id = bs.block_id'
					,[]
				)
				// Отфильтровываем блоки, которые привязаны к ранее удалённым витринам.
				->where(df_conn()->prepareSqlCondition('bs.store_id', array(
					'nin' => array_keys(Mage::app()->getStores($withDefault = true, $codeKey = false))
				)))
				/**
				 * Это условие всё равно нужно,
				 * потому что условие выше говорит, каким не должно быть store_id у сирот,
				 * а данное условие, напротив, говорит, каким может быть store_id у сирот.
				 */
				->orWhere('bs.store_id IS NULL')
		);
	}

	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}