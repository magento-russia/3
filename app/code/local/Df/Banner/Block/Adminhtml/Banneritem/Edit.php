<?php
class Df_Banner_Block_Adminhtml_Banneritem_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	/**
	 * Перекрывать надо именно конструктор, а не метод @see _construct(),
	 * потому что родительский класс пихает инициализацию именно в конструктор.
	 * @see Mage_Adminhtml_Block_Widget_Form_Container::__construct()
	 * @override
	 */
	public function __construct() {
		parent::__construct();
		$this->_objectId = 'id';
		$this->_blockGroup = 'df_banner';
		$this->_controller = 'adminhtml_banneritem';
		$this->_updateButton('save', 'label', 'Утвердить и вернуться');
		$this->_updateButton('delete', 'label', 'Удалить');
		$this->_addButton('saveandcontinue', array(
			'label'	=> 'Утвердить и остаться'
			,'onclick' => 'saveAndContinueEdit()'
			,'class' => 'save'
		), -100);
		$this->_formScripts[]= "
			function toggleEditor() {
				if (null === tinyMCE.getInstanceById('df_banner_content')) {
					tinyMCE.execCommand('mceAddControl', false, 'df_banner_content');
				} else {
					tinyMCE.execCommand('mceRemoveControl', false, 'df_banner_content');
				}
			}
			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
	}

	/**
	 * @override
	 * @return string
	 */
	public function getHeaderText() {
		return
			Mage::registry('df_banner_item_data') && Mage::registry('df_banner_item_data')->getId()
			? sprintf('Объявление «%s»', df_e(Mage::registry('df_banner_item_data')->getTitle()))
			: 'Составить новое объявление...'
		;
	}
}