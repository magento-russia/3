<?php
use Df_Catalog_Model_Product as P;
class Df_Shipping_Setup_2_16_3 extends Df_Core_Setup {
	/**
	 * @override
	 * @see Df_Core_Setup::_process()
	 * @used-by Df_Core_Setup::process()
	 * @return void
	 */
	protected function _process() {
		foreach ([P::P__WIDTH, P::P__HEIGHT, P::P__LENGTH] as $code) {
			/** @var string $code */
			self::attribute()->updateAttribute(P::ENTITY, $code, 'is_user_defined', 0);
		}
		df_eav_reset();
	}
}