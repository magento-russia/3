<?php
/** @method Df_Core_Model_Event_Controller_Action_Layout_GenerateBlocksAfter getEvent() */
class Df_Tweaks_Model_Handler_AdjustPaypalLogo extends Df_Core_Model_Handler {
	/**
	 * Метод-обработчик события
	 * @override
	 * @return void
	 */
	public function handle() {
		/** @var Df_Tweaks_Model_Settings_PaypalLogo $s */
		$s = Df_Tweaks_Model_Settings_PaypalLogo::s();
		if (
					$s->removeFromFrontpage()
				&&
					df_handle(Df_Core_Model_Layout_Handle::CMS_INDEX_INDEX)
			||
					$s->removeFromCatalogProductList()
				&&
					df_handle(Df_Core_Model_Layout_Handle::CATALOG_CATEGORY_VIEW)
			||
					$s->removeFromCatalogProductView()
				&&
					df_handle(Df_Core_Model_Layout_Handle::CATALOG_PRODUCT_VIEW)
		) {
			df_block_remove('paypal.partner.right.logo');
		}
	}

	/**
	 * Класс события (для валидации события)
	 * @override
	 * @return string
	 */
	protected function getEventClass() {
		return Df_Core_Model_Event_Controller_Action_Layout_GenerateBlocksAfter::class;
	}

	/** @used-by Df_Tweaks_Observer::controller_action_layout_generate_blocks_after() */

}