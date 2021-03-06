<?php
namespace Df\C1\Cml2\Import\Data\Entity\ProductPart\AttributeValue;
use Df_Catalog_Model_Resource_Eav_Attribute as CatalogAttribute;
use Df\C1\Cml2\Import\Data\Entity\Product as EntityProduct;
use Df\Xml\X;
use Mage_Eav_Model_Entity_Attribute as EavAttribute;
class Custom extends \Df\C1\Cml2\Import\Data\Entity\AttributeValue {
	/**
	 * @override
	 * @return string
	 */
	public function getAttributeExternalId() {return $this->leafSne('Ид');}

	/** @return string */
	public function getAttributeName() {return $this->am()->getName();}

	/**
	 * @override
	 * @return string
	 */
	public function getExternalId() {return
		implode('::', [$this->getAttributeExternalId(), $this->getValue()])
	;}

	/** @return string */
	public function getValue() {return $this->leaf('Значение');}

	/**
	 * 2015-02-06
	 * @used-by \Df\C1\Cml2\Import\Processor\Product\Type::getProductDataNewOrUpdateAttributeValues()
	 * Метод @used-by Df_Dataflow_Model_Import_Abstract_Row::getFieldValue()
	 * проверяет принадлежность результата @see getValueForDataflow()
	 * одному из типов: string|int|float|bool|null
	 *
	 * Обратите внимание на отличие метода @see getValueForDataflow()
	 * от метода @see getValueForObject().
	 * Опция имеет следующую структуру данных:
		  array(
				[option_id] => 35
				[attribute_id] => 148
				[sort_order] => 2
				[df_1c_id] => 14ed8b52-55bd-11d9-848a-00112f43529a
				[default_value] => натуральная кожа
				[store_default_value] =>
				[value] => натуральная кожа
			 )
	 * Для приведённой выше структуры данных
	 * @see getValueForObject() вернёт значение «35»,
	 * а @see getValueForDataflow() вернёт значение «натуральная кожа».
	 *
	 * @override
	 * @return string|int|float|bool|null
	 */
	public function getValueForDataflow() {return
		$this->getAttributeEntity()->convertValueToMagentoFormat($this->getValue())
	;}

	/**
	 * 2015-02-06
	 * Обратите внимание на отличие метода @see getValueForObject() от метода @see getValueForDataflow()
	 * Опция имеет следующую структуру данных:
		  array(
				[option_id] => 35
				[attribute_id] => 148
				[sort_order] => 2
				[df_1c_id] => 14ed8b52-55bd-11d9-848a-00112f43529a
				[default_value] => натуральная кожа
				[store_default_value] =>
				[value] => натуральная кожа
			 )
	 * Для приведённой выше структуры данных
	 * @see getValueForObject() вернёт значение «35»,
	 * а @see getValueForDataflow() вернёт значение «натуральная кожа».
	 * @used-by \Df\C1\Cml2\Import\Processor\Product\Type\Configurable\Update::getProductDataNewOrUpdateAttributeValueIdsCustom()
	 * @return string|int|float|bool|null
	 */
	public function getValueForObject() {return
		$this->getAttributeEntity()->convertValueToMagentoFormat($this->getValue())
	;}

	/**
	 * @override
	 * @return bool
	 */
	public function isValidForImport() {return
		// 1C для каждого товара
		// указывает не только значения свойств, относящихся к товару,
		// но и значения свойств, к товару никак не относящихся,
		// при этом значения — пустые, например:
		//
		//	<ЗначенияСвойства>
		//		<Ид>b79b0fe0-c8a5-11e1-a928-4061868fc6eb</Ид>
		//		<Значение/>
		//	</ЗначенияСвойства>
		//
		// Мы не обрабатываем эти свойства, потому что их обработка приведёт к добавлению
		// к прикладному типу товара данного свойства, а нам это не нужно, потому что
		// свойство не имеет отношения к прикладному типу товара.
		$this->leaf('Значение')
		|| $this->leaf('ИдЗначения')
		// 11 февраля 2014 года заметил,
		// что 1С:Управление торговлей 11.1 при использовании версии 2.05 протокола CommerceML
		// и версии 8.3 платформы 1С:Предприятие
		// при обмене данными с интернет-магазином передаёт информацию о производителе
		// не в виде стандартного атрибута, а иначе:
		//
		//	<КоммерческаяИнформация ВерсияСхемы="2.05" ДатаФормирования="2014-02-11T15:32:13">
		//		(...)
		//		<Каталог СодержитТолькоИзменения="false">
		//			(...)
		//			<Товары>
		//				<Товар>
		//					(...)
		//					<Изготовитель>
		//						<Ид>9bf2b1bf-8e9a-11e3-bd2c-742f68ccd0fb</Ид>
		//						<Наименование>Tecumseh</Наименование>
		//						<ОфициальноеНаименование>Tecumseh</ОфициальноеНаименование>
		//					</Изготовитель>
		//					(...)
		//				</Товар>
		//			</Товары>
		//		</Каталог>
		//	</КоммерческаяИнформация>
		|| $this->leaf('Наименование')
		|| $this->isAttributeExistAndBelongToTheProductType()
	;}

	/**
	 * @override
	 * @return array(string => string|int)
	 */
	protected function getCreationParamsCustom() {return ['is_configurable' => 1];}

	/**
	 * @override
	 * @return \Df_Catalog_Model_Resource_Eav_Attribute
	 */
	protected function findMagentoAttributeInRegistry() {return
		df_attributes()->findByExternalId($this->getAttributeExternalId())
	;}

	/**
	 * @override
	 * @return string
	 */
	protected function getAttributeCodeNew() {return dfc($this, function() {return
		df_c1()->generateAttributeCode(
			$this->getAttributeEntity()->getName()
			// Намеренно убрал второй параметр ($this->getProduct()->getAppliedTypeName()),
			// потому что счёл ненужным в данном случае
			// использовать приставку для системных имён товарных свойств,
			// потому что приставка (прикладной тип товара),
			// как правило, получается слишком длинной,
			// а название системного имени товарного свойства
			// ограничено 32 символами
		)
	;});}

	/** @return string */
	protected function getAttributeFrontendLabel() {return $this->getAttributeEntity()->getName();}

	/**
	 * @override
	 * @return \Df\C1\Cml2\Import\Data\Entity\Attribute
	 */
	protected function getAttributeTemplate() {return $this->getAttributeEntity();}

	/**
	 * @param CatalogAttribute $attribute
	 * @return string
	 */
	protected function getGroupForAttribute(CatalogAttribute $attribute) {return
		\Df\C1\C::PRODUCT_ATTRIBUTE_GROUP_NAME
	;}

	/**
	 * @override
	 * @return EntityProduct
	 */
	protected function getProduct() {return $this->cfg(self::$P__PRODUCT);}

	/** @return \Df\C1\Cml2\Import\Data\Entity\Attribute */
	private function getAttributeEntity() {
		/** @var \Df\C1\Cml2\Import\Data\Entity\Attribute $result */
		$result =
			$this->getState()->import()->cl()->getAttributes()->findByExternalId(
				$this->getAttributeExternalId()
			)
		;
		if (is_null($result)) {
			df_error(
				'В реестре отсутствует требуемое свойство с внешним идентификатором «%s»'
				,$this->getAttributeExternalId()
			);
		}
		return $result;
	}

	/** @return bool */
	private function isAttributeExistAndBelongToTheProductType() {return dfc($this, function() {
		/** @var bool $result */
		$result = false;
		/** @var EavAttribute|null $attribute */
		$attribute = df_attributes()->findByExternalId($this->getAttributeExternalId());
		if ($attribute) {
			df_assert($attribute instanceof EavAttribute);
			// Смотрим, принадлежит ли свойство типу товара
			/** @var \Mage_Eav_Model_Resource_Entity_Attribute_Collection $attributes */
			$attributes = \Mage::getResourceModel('eav/entity_attribute_collection');
			$attributes->setEntityTypeFilter(df_eav_id_product());
			$attributes->addSetInfo();
			$attributes->addFieldToFilter('attribute_code', $attribute->getAttributeCode());
			$attributes->load();
			/** @var EavAttribute $attributeInfo */
			$attributeInfo = null;
			foreach ($attributes as $attributeInfoCurrent) {
				$attributeInfo = $attributeInfoCurrent;
				break;
			}
			df_assert($attributeInfo instanceof EavAttribute);
			/** @var mixed[] $setsInfo */
			$setsInfo = $attributeInfo->getData('attribute_set_info');
			df_assert_array($setsInfo);
			$result = in_array($this->getProduct()->getAttributeSet()->getId(), array_keys($setsInfo));
		}
		return $result;
	});}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->_prop(self::$P__PRODUCT, EntityProduct::class);
	}

	/** @var string  */
	private static $P__PRODUCT = 'product';

	/**
	 * @used-by \Df\C1\Cml2\Import\Data\Collection\ProductPart\AttributeValues\Custom::createItem()
	 * @used-by \Df\C1\Cml2\Import\Data\Entity\ProductPart\AttributeValue\Custom\Option\Manufacturer::i()
	 * @param string $class
	 * @param X $e
	 * @param EntityProduct $product
	 * @return self
	 */
	public static function ic($class, X $e, EntityProduct $product) {return
		df_ic($class, __CLASS__, [self::$P__E => $e, self::$P__PRODUCT => $product])
	;}
}