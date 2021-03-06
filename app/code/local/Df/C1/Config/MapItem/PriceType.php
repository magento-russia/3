<?php
namespace Df\C1\Config\MapItem;
class PriceType extends \Df_Admin_Config_MapItem {
	/** @return \Df_Customer_Model_Group|null */
	public function getCustomerGroup() {return dfc($this, function() {return
		\Df_Customer_Model_Group::cs()->getItemById($this->getCustomerGroupId())				
	;});}

	/** @return string|null */
	public function getНазваниеТиповогоСоглашения() {return $this->cfg(self::P__PRICE_TYPE);}

	/**
	 * Обратите внимание, что для категорий покупателей вполне допустим идентификатор «0»,
	 * однако в данном случае категория покупателей с идентификатором «0» нам не нужна,
	 * потому что для данной категории покупателей типовое соглашение (или тип цен)
	 * настраивается в отдельной графе «Название основной цены или типового соглашения».
	 * @see Df_Admin_Config_MapItem::isValid()
	 * @override
	 * @return bool
	 */
	public function isValid() {return
		$this->getCustomerGroupId() && $this->getНазваниеТиповогоСоглашения()
	;}

	/** @return int|null */
	private function getCustomerGroupId() {return $this->cfg(self::P__CUSTOMER_GROUP);}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this
			->_prop(self::P__CUSTOMER_GROUP, DF_V_INT, false)
			->_prop(self::P__PRICE_TYPE, DF_V_STRING, false)
		;
	}
	/** @used-by \Df\C1\Config\Api\Product\Prices::getMapFromCustomerGroupIdToНазваниеТиповогоСоглашения() */
	const P__CUSTOMER_GROUP = 'customer_group';
	/** @used-by \Df\C1\Config\Api\Product\Prices::getMapFromCustomerGroupIdToНазваниеТиповогоСоглашения() */
	const P__PRICE_TYPE = 'price_type';

	/**
	 * 2015-04-18
	 * Описывает поля структуры данных.
	 * Используется для распаковки значений по умолчанию.
	 * @used-by Df_Admin_Config_Backend_Table::unserialize()
	 * @return string[]
	 */
	public static function fields() {return [self::P__CUSTOMER_GROUP, self::P__PRICE_TYPE];}
}