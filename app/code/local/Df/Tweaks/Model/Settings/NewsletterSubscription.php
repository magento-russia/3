<?php
class Df_Tweaks_Model_Settings_NewsletterSubscription extends Df_Core_Model_Settings {
	/** @return boolean */
	public function removeFromAccount() {return $this->getYesNo('account');}
	/** @return boolean */
	public function removeFromAll() {return $this->getYesNo('all');}
	/** @return boolean */
	public function removeFromCatalogProductList() {return $this->getYesNo('catalog_product_list');}
	/** @return boolean */
	public function removeFromCatalogProductView() {return $this->getYesNo('catalog_product_view');}
	/** @return boolean */
	public function removeFromFrontpage() {return $this->getYesNo('frontpage');}
	/**
	 * @override
	 * @return string
	 */
	protected function getKeyPrefix() {return 'df_tweaks/newsletter_subscription/remove_from_';}
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}