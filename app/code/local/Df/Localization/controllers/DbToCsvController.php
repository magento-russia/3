<?php
class Df_Localization_DbToCsvController extends Df_Core_Controller_Admin {
	/** @return void */
	public function indexAction() {
		$this
			->_title('Система')
			->_title('Локализация')
			->_title('Запись переводов из БД в CSV')
			->loadLayout()
			->_setActiveMenu('system/df_localization')
			->renderLayout()
		;
	}

	/** @return void */
	public function exportAction() {
		Df_Localization_Exporter::i()->process();
		$this->_redirect('*/*/*');
	}
}