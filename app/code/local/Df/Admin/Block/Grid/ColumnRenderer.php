<?php
use Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract as AbstractRenderer;
class Df_Admin_Block_Grid_ColumnRender extends Df_Core_Block_Admin {
	/** @return Varien_Object */
	protected function getColumn() {return $this->getRenderer()->getColumn();}

	/**
	 * @param string $paramName
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	protected function getColumnParam($paramName, $defaultValue = null) {return
		dfo($this->getColumn(), $paramName, $defaultValue)
	;}

	/** @return Varien_Object */
	protected function getRow() {return $this->cfg(self::$P__ROW);}

	/**
	 * @param string $paramName
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	protected function getRowParam($paramName, $defaultValue = null) {return
		dfo($this->getRow(), $paramName, $defaultValue)
	;}

	/** @return AbstractRenderer */
	private function getRenderer() {return $this->_getData(self::$P__RENDERER);}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this
			->_prop(self::$P__ROW, Varien_Object::class)
			->_prop(self::$P__RENDERER, AbstractRenderer::class)
		;
	}
	/** @var string */
	private static $P__RENDERER = 'renderer';
	/** @var string */
	private static $P__ROW = 'row';

	/**
	 * @used-by Df_Sales_Block_Admin_Grid_OrderItems::r()
	 * @param string $class
	 * @param AbstractRenderer $renderer
	 * @param Varien_Object $row
	 * @return string
	 */
	protected static function rc($class, AbstractRenderer $renderer, Varien_Object $row) {return
		df_render(df_ic($class, __CLASS__, [self::$P__RENDERER => $renderer, self::$P__ROW => $row]));
	}
}