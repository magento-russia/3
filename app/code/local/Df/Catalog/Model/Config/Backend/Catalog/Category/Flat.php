<?php
class Df_Catalog_Model_Config_Backend_Catalog_Category_Flat
	extends Mage_Catalog_Model_System_Config_Backend_Catalog_Category_Flat {
	/**
	 * @override
	 * @return $this
	 */
	protected function _afterSave() {
		Mage::dispatchEvent(
			'df__config_after_save__catalog__frontend__flat_catalog_category', array('object' => $this)
		);
		parent::_afterSave();
		return $this;
	}
}


 