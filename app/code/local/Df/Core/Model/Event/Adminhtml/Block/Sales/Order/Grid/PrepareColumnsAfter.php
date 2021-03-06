<?php
/**
 * Cообщение:		«df_adminhtml_block_sales_order_grid__prepare_columns_after»
 * Источник:		Df_Adminhtml_Block_Sales_Order_Grid::_prepareColumns()
 * [code]
		Mage::dispatchEvent(
			Df_Core_Model_Event_Adminhtml_Block_Sales_Order_Grid_PrepareColumnsAfter::EVENT
			,array('grid' => $this)
		);
 * [/code]
 */
class Df_Core_Model_Event_Adminhtml_Block_Sales_Order_Grid_PrepareColumnsAfter
	extends Df_Core_Model_Event {
	/** @return Df_Adminhtml_Block_Sales_Order_Grid */
	public function getGrid() {return $this->getEventParam('grid');}
	/**
	 * @override
	 * @return string
	 */
	protected function getExpectedEventPrefix() {return self::EVENT;}

	/**
	 * @used-by Df_Sales_Observer::df_adminhtml_block_sales_order_grid__prepare_columns_after()
	 * @used-by Df_Sales_Model_Handler_AdminOrderGrid_AddProductColumn::getEventClass()_
	 */

	/**
	 * @used-by Df_Adminhtml_Block_Sales_Order_Grid::_prepareColumns()
	 */
	const EVENT = 'df_adminhtml_block_sales_order_grid__prepare_columns_after';
}