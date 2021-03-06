<?php
namespace Df\C1\Config\Api;
class CatalogExport extends \Df\C1\Config\Api\Cml2 {
	/** @return \Mage_CatalogRule_Model_Rule|null */
	public function getRule() {return dfc($this, function() {return
		\Df_Catalog_Model_ConditionsLoader::i(
			$this->getRuleId()
			, '1С:Управление торговлей'
			, '«1С:Управление торговлей» → «Экспорт товаров в 1С» → «Условия»'
		)->getRule()				
	;});}

	/**
	 * @override
	 * @return string
	 */
	protected function getKeyPrefix() {return 'df_1c/catalog_export/';}

	/** @return int */
	private function getRuleId() {return $this->nat0('product_conditions');}

	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}