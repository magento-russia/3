<?php
class Df_Checkout_Block_Onepage_Shipping_Method_Available
	extends Mage_Checkout_Block_Onepage_Shipping_Method_Available {
	/**
	 * 2015-03-03
	 * Цель перекрытия —
	 * ускорение реакции системы на экране оформления заказа.
	 * Стандартный блок ошибочно дважды пересчитывает одни и те же тарифы доставки,
	 * что замедляет реакцию системы.
	 *
	 * Пока не связываемся со стандартным для Magento CE / EE экраном оформления заказа
	 * с целью экономии времени на тестировании.
	 * Применяем улучшение только для экрана удобного оформления заказа Российской сборки Magento.
	 * @override
	 * @return array(string => Mage_Sales_Model_Quote_Address_Rate[])
	 */
	public function getShippingRates() {
		return
			isset($this->_rates) || df_is_admin() || !df_checkout_ergonomic()
			? parent::getShippingRates()
			: $this->_rates = $this->getAddress()->getGroupedAllShippingRates()
		;
	}

	/**
	 * 2015-03-29
	 * Перекрываем родительский метод ради упрощения и улучшения шаблона
	 * checkout/onepage/shipping_method/available.phtml
	 * @override
	 * @see Mage_Core_Block_Template::_toHtml()
	 * @used-by Mage_Core_Block_Abstract::toHtml()
	 * @return string
	 */
	protected function _toHtml() {
		return
			!$this->getShippingRates()
			? "<p>{$this->__('Sorry, no quotes are available for this order at this time.')}</p>"
			: df_tag('dl', array('class' => 'sp-methods'), $this->renderCarriers())
		;
	}

	/**
	 * @used-by _toHtml()
	 * @return string
	 */
	private function renderCarriers() {/** @noinspection PhpParamsInspection */ return
		df_cc_n(df_map_k(function($code, array $rates) {return
			\Df\Shipping\Block\Carrier::r($this, $code, $rates)
		;}, $this->getShippingRates()))
	;}
}