<?php
class Df_Cms_Model_MenuSource extends Df_Page_Model_MenuSource {
	/**
	 * @override
	 * @return Varien_Data_Tree
	 */
	public function getTree() {return df_h()->cms()->getTree()->getTree();}

	/**
	 * @override
	 * @return bool
	 */
	public function isEnabled() {
		return
			df_cfgr()->cms()->hierarchy()->isEnabled()
			&& df_cfgr()->cms()->hierarchy()->needAddToCatalogMenu()
		;
	}


}