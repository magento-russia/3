<?php
class Df_Banner_Block_Adminhtml_Banneritem_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
	/**
	 * @override
	 * @return $this
	 */
	protected function _beforeToHtml() {
		$this->addTab('form_section', array(
			'label' => 'Основные'
			,'title' => 'Основные'
			,'content' => df_render('Df_Banner_Block_Adminhtml_Banneritem_Edit_Tab_Form')
		));
		parent::_beforeToHtml();
		return $this;
	}
	/**
	 * @override
	 * return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->setId('df_banner_item_tabs');
		$this->setDestElementId('edit_form');
		/** @noinspection PhpUndefinedMethodInspection */
		$this->setTitle('Настройки');
	}
}