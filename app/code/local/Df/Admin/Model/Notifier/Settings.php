<?php
abstract class Df_Admin_Model_Notifier_Settings extends Df_Admin_Model_Notifier {
	/** @return string */
	protected function getUrlSettingsSuffix() {return '';}

	/**
	 * @param Df_Core_Model_StoreM $store
	 * @return bool
	 */
	abstract protected function isStoreAffected(Df_Core_Model_StoreM $store);

	/**
	 * @override
	 * @return bool
	 */
	public function needToShow() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = parent::needToShow() && (0 < $this->getStoresAffectedCount());
		}
		return $this->{__METHOD__};
	}

	/**
	 * @override
	 * @return string
	 */
	public function getMessage() {return dfc($this, function() {return parent::getMessage() . (
		!$this->getUrlSettingsSuffix() ? null : df_url_bake(
			'<br/><span class="rm-url-settings">[[открыть раздел настроек]]</span>'
			,df_url_backend('adminhtml/system_config/edit/section/' . $this->getUrlSettingsSuffix())
		)				
	);});}

	/**
	 * @override
	 * @return array(string => string)
	 */
	protected function getMessageVariables() {return dfc($this, function() {return [
		self::MESSAGE_VAR__STORES_AFFECTED =>
			Mage::app()->isSingleStoreMode() ? '' : df_ccc(' '
				,(1 === $this->getStoresAffectedCount()) ? ' для магазина ' : 'для магазинов'
				,Df_Core_Model_Resource_Store_Collection::getNamesStatic($this->getStoresAffected())
			)
	] + parent::getMessageVariables();});}

	/** @return Df_Core_Model_StoreM[] */
	private function getStoresAffected() {
		if (!isset($this->{__METHOD__})) {
			/** @var Df_Core_Model_StoreM[] $result */
			$result = [];
			foreach (Mage::app()->getStores() as $store) {
				/** @var Df_Core_Model_StoreM $store */
				if ($this->isStoreAffected($store)) {
					$result[]= $store;
				}
			}
			$this->{__METHOD__} = $result;
		}
		return $this->{__METHOD__};
	}

	/** @return int */
	private function getStoresAffectedCount() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = count($this->getStoresAffected());
		}
		return $this->{__METHOD__};
	}

	const MESSAGE_VAR__STORES_AFFECTED = '{перечисление магазинов}';
}