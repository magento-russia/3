<?php
/**
 * @method Df_Catalog_Model_Event_Category_Collection_Load_Before getEvent()
 */
class Df_AccessControl_Model_Handler_Catalog_Category_Collection_ExcludeForbiddenCategories
	extends Df_Core_Model_Handler {
	/**
	 * Метод-обработчик события
	 * @override
	 * @return void
	 */
	public function handle() {
		if (Df_AccessControl_Settings::s()->getEnabled()
			&& !is_null(df_h()->accessControl()->getCurrentRole())
			&& df_h()->accessControl()->getCurrentRole()->isModuleEnabled()
		) {
			// добавляем фильтр по разрешённым товарным разделам
			/** @var Mage_Catalog_Model_Resource_Category_Collection $c */
			$c = $this->getEvent()->getCollection();
			if (!Df_AccessControl_Helper_Data::disable($c)) {
				$c->addIdFilter(df_h()->accessControl()->getCurrentRole()->getCategoryIdsWithAncestors());
			}
		}
	}

	/**
	 * Класс события (для валидации события)
	 * @override
	 * @return string
	 */
	protected function getEventClass() {return Df_Catalog_Model_Event_Category_Collection_Load_Before::class;}

	/** @used-by Df_AccessControl_Observer::catalog_category_collection_load_before() */

}