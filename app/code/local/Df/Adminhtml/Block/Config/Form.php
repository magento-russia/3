<?php
/**
 * Обратите внимание, что мы перекрываем класс @see Mage_Adminhtml_Block_System_Config_Form
 * не посредством rewrite,
 * а иначе:
	<config>
		<sections>
			<df_shipping translate='label' module='df_shipping'>
				<frontend_model>Df_Adminhtml_Block_Config_Form</frontend_model>
			</df_shipping>
			<df_payment translate='label' module='df_payment'>
				<frontend_model>Df_Adminhtml_Block_Config_Form</frontend_model>
			</df_payment>
		</sections>
	</config>
 */
class Df_Adminhtml_Block_Config_Form extends Mage_Adminhtml_Block_System_Config_Form {
	/**
	 * @override
	 * @return array(string => string)
	 */
	protected function _getAdditionalElementTypes() {return dfc($this, function() {return
		df_config_a('df/admin/config-form/element-types') + parent::_getAdditionalElementTypes()		
	;});}
}