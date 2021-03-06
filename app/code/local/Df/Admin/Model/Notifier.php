<?php
abstract class Df_Admin_Model_Notifier extends Df_Core_Model {
	/** @return string */
	abstract protected function messageTemplate();

	/**
	 * @used-by app/design/adminhtml/rm/default/template/df/core/notifications.phtml
	 * @return string
	 */
	public function getMessage() {return dfc($this, function() {return df_url_bake(
		strtr($this->messageTemplate(), $this->getMessageVariables()), $this->getUrlHelp()
	);});}

	/**
	 * @used-by app/design/adminhtml/rm/default/template/df/core/notifications.phtml
	 * @return string
	 */
	public function getUrlSkip() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = \Df\Admin\Action\SkipNotification::getLink(get_class($this));
		}
		return $this->{__METHOD__};
	}

	/** @return bool */
	public function needToShow() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = !Mage::getStoreConfigFlag($this->getConfigPathSkip());
		}
		return $this->{__METHOD__};
	}

	/**
	 * Если администратор изменил значение наблюдаемой опции,
	 * то предшествующую команду администратора о скрытии предупреждения
	 * о проблемном значении этой опции считаем недействительной.
	 * Точно так же поступает и ядро Magento в сценарии предупреждений о настройках налогов:
	 * @see Mage_Tax_Model_Config_Notification::_resetNotificationFlag()
	 * @return void
	 */
	public function resetSkipStatus() {
		/** @var Df_Core_Model_Config_Data $config */
		$config = Df_Core_Model_Config_Data::i();
		$config->load($this->getConfigPathSkip(), 'path');
		$config->setValue(0);
		$config->setPath($this->getConfigPathSkip());
		$config->save();
	}

	/** @return array(string => string) */
	protected function getMessageVariables() {return ['{веб-адрес пояснений}' => $this->getUrlHelp()];}

	/** @return string */
	protected function getUrlHelp() {return '';}

	/** @return string */
	private function getConfigPathSkip() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = self::getConfigPathSkipByClass(get_class($this));
		}
		return $this->{__METHOD__};
	}

	/** @used-by Df_Admin_Model_Notifier_Collection::itemClass() */

	/**
	 * @used-by getConfigPathSkip()
	 * @used-by Df_Admin_Action_SkipNotification::getConfigPath()
	 * @param string $class
	 * @return string
	 */
	public static function getConfigPathSkipByClass($class) {return
		'df/admin/notifiers/skip/' . $class;
	}
}