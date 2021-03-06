<?php
class Df_Catalog_Block_Layer_View extends Mage_Catalog_Block_Layer_View {
	/**
	 * Цель перекрытия —
	 * кэширование блока пошаговой фильтрации.
	 * Обратите внимание, что блок @see Mage_Catalog_Block_Product_View
	 * выводится несколько раз на одной и той же странице
	 * с разным именем и разными шаблонами для отображения разных данных.
	 * Родительский метод @uses Mage_Core_Block_Template::getCacheKeyInfo()
	 * включает в ключ кэширование файловый путь к шаблону,
	 * поэтому путаница кэша тем самым исключена.
	 * @override
	 * @see Mage_Core_Block_Template::getCacheKeyInfo()
	 * @used-by Df_Core_Block_Abstract::getCacheKey()
	 * @return string[]
	 */
	public function getCacheKeyInfo() {
		return array_merge(parent::getCacheKeyInfo(), array(
			$this->getCategoryRm()->getId()
			,df_session_customer()->getCustomerGroupId()
			,df_store_id()
			,http_build_query($this->getRequest()->getParams())
		));
	}

	/** @return Df_Catalog_Model_Category */
	private function getCategoryRm() {
		/**
		 * Возможность извлечения текущего товарного раздела из ключа «current_category_filter» реестра
		 * взята из @see Mage_Catalog_Model_Layer_Filter_Price::getPriceRange()
		 */
		/** @var Df_Catalog_Model_Category $result */
		$result = Mage::registry('current_category_filter');
		return $result ? $result : df_state()->getCurrentCategory();
	}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->addData(array(
			/**
			 * Чтобы блок кэшировался стандартным, заложенным в @see Mage_Core_Block_Abstract способом,
			 * продолжительность хранения кэша надо указывать обязательно,
			 * потому что значением продолжительности по умолчанию является «null»,
			 * что в контексте @see Mage_Core_Block_Abstract
			 * (и в полную противоположность Zend Framework
			 * и всем остальным частям Magento, где используется кэширование)
			 * означает, что блок не удет кэшироваться вовсе!
			 * @used-by Mage_Core_Block_Abstract::_loadCache()
			 */
			'cache_lifetime' => Df_Core_Block_Template::CACHE_LIFETIME_STANDARD
			/**
			 * При такой инициализации тегов
			 * (без перекрытия метода @see Mage_Core_Block_Abstract::getCacheTags())
			 * тег @see Mage_Core_Block_Abstract::CACHE_GROUP будет добавлен автоматически.
			 * @used-by Mage_Core_Block_Abstract::getCacheTags()
			 */
			,'cache_tags' => array(Mage_Catalog_Model_Product::CACHE_TAG)
		));
	}
}