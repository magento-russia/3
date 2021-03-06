<?php
class Df_Admin_Model_Notifier_DeleteDemoStores extends Df_Admin_Model_Notifier {
	/**
	 * @override
	 * @return bool
	 */
	public function needToShow() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = parent::needToShow() && $this->getDemoStores();
		}
		return $this->{__METHOD__};
	}

	/**
	 * @override
	 * @see Df_Admin_Model_Notifier::messageTemplate()
	 * @return string
	 */
	protected function messageTemplate() {return
		df_cc_br(Df_Admin_Block_Notifier_DeleteDemoStore::renderA($this->getDemoStores()))
	;}

	/** @return Df_Core_Model_StoreM[] */
	private function getDemoStores() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} =
				dfa_select(
					Mage::app()->getStores($withDefault = false, $codeKey = true)
					, $this->getDemoStoreCodes()
				)
			;
		}
		return $this->{__METHOD__};
	}

	/** @return array(mixed => mixed) */
	private function getDemoStoreCodes() {return ['french', 'german', 'spanish'];}
}