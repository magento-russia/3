<?php
class Df_Wishlist_Helper_Data extends Mage_Wishlist_Helper_Data {
	/**
	 * Цель перекрытия —
	 * предоставление администратору возможности скрывать ссылку для добавления товара в план покупок
	 * со страницы товара и с мини-карточек товаров со страницы товарного раздела.
	 * @override
	 * @return bool
	 */
	public function isAllow() {
		$result = parent::isAllow();
		if ($result) {
			if (df_module_enabled(Df_Core_Module::TWEAKS)) {
				if (
						(
								df_handle(Df_Core_Model_Layout_Handle::CATALOG_PRODUCT_VIEW)
							&&
								df_cfgr()->tweaks()->catalog()->product()->view()->needHideAddToWishlist()
						)
					||
						(
								df_cfgr()->tweaks()->catalog()->product()->_list()->needHideAddToWishlist()
							&&
								df_h()->tweaks()->isItCatalogProductList()
						)
				) {
					$result = false;
				}
			}
		}
		return $result;
	}

	

	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}