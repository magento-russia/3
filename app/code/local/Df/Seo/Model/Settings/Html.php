<?php
class Df_Seo_Model_Settings_Html extends Df_Core_Model_Settings {
	/** @return boolean */
	public function getAppendCategoryNameToProductTitleTag() {
		return $this->getYesNo('df_seo/html/append_category_name_to_product_title_tag');
	}
	/** @return string */
	public function getDefaultPatternForProductTitleTag() {
		return $this->v('df_seo/html/product_title_tag_default_pattern');
	}
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}