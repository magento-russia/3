<?php
namespace Df\C1\Cml2\Import\Data\Entity\ProductPart\AttributeValue\Custom\Option;
use Df_Catalog_Model_Resource_Eav_Attribute as Attribute;
use Df\C1\Cml2\Import\Data\Entity\Attribute\ReferenceList;
use Df\C1\Cml2\Import\Data\Entity\Product as EntityProduct;
class Manufacturer extends \Df\C1\Cml2\Import\Data\Entity\ProductPart\AttributeValue\Custom\Option {
	/**
	 * @override
	 * @return Attribute
	 */
	public function am() {
		if (!isset($this->{__METHOD__})) {
			/** @var Attribute $result */
			$result = parent::am();
			// Все обычные справочники мы импортируем перед товарами.
			// Однако справочник «Изготовители» («Производители») в УТ 11 — необычный.
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
			//
			// Другими словами, в данной ситуации
			// элементы справочника «Изготовители» («Производители») не сгруппированы вместе,
			// а вместо этого разбросаны по товарам.
			// Поэтому нам надо здесь вручную добавить новое значение в этот справочник.
			\Df\C1\Cml2\Import\Processor\ReferenceList::removeDuplicateOptionsWithTheSameExternalId(
				$result
			);
			/** @var array(string => mixed) $attributeData */
			$attributeData = array_merge($result->getData(), [
				\Df\C1\C::ENTITY_EXTERNAL_ID => 'Изготовитель'
				,'option' => \Df_Eav_Model_Entity_Attribute_Option_Calculator::calculateStatic(
					$result
					, ['option_0' => [$this->getName()]]
					,$isModeInsert = true
					,$caseInsensitive = true
				)
			]);
			df_c1_log('Обновление справочника «%s».', 'Изготовитель');
			$result = df_attributes()->createOrUpdate($attributeData);
			df_c1_log('Добавили в справочник «%s» значение «%s».', 'Изготовитель', $this->getName());
			// Назначаем новому справочному значению идентификатор из 1С
			/** @var \Df_Eav_Model_Resource_Entity_Attribute_Option_Collection $options */
			$options = \Df_Eav_Model_Entity_Attribute_Option::c();
			$options->setPositionOrder('asc');
			$options->setAttributeFilter($result->getId());
			$options->setStoreFilter($result->getStoreId());
			/**
			 * Похоже, мы не можем просто добавить фильтр
			 * $options->addFieldToFilter('value', $this->getName())
			 * потому что запрос SQL коллекции выглядит следующим образом:
			 	SELECT
			 		`main_table`.*
			  		, `tdv`.`value` AS `default_value`
			  		, `tsv`.`value` AS `store_default_value`
			  		, IF(tsv.value_id > 0, tsv.value, tdv.value) AS `value`
			  FROM `eav_attribute_option` AS `main_table`
			 		INNER JOIN `eav_attribute_option_value` AS `tdv` ON tdv.option_id = main_table.option_id
			 	 	LEFT JOIN `eav_attribute_option_value` AS `tsv` ON
			  			tsv.option_id = main_table.option_id
			  			AND tsv.store_id = '2'
			  WHERE (attribute_id = '81') AND (tdv.store_id = 0)
			 *
			 * Как можно увидеть, значение value является вычисляемым.
			 */
			foreach ($options as $option) {
				/** @var \Df_Eav_Model_Entity_Attribute_Option $option */
				if (
						df_strings_are_equal_ci($this->getName(), $option->getData('value'))
					&&
						!$option->get1CId()
				) {
					$option->set1CId($this->getExternalId());
					$option->save();
					break;
				}
			}
			$this->{__METHOD__} = $result;
		}
		return $this->{__METHOD__};
	}

	/**
	 * @override
	 * @return string
	 */
	public function getExternalId() {
		/**
		 * ПРИМЕЧАНИЕ НЕ УДАЛЯТЬ!
		 * Оно важно для метода @see getValue().
		 *
		 * Раньше метод почему-то возвращал
		 * $this->leaf('Ид')
		 * Похоже, что это был дефект, ибо в разметке
			<Изготовитель>
				<Ид>061e1ea9-e4e9-11e0-af8f-0015e9b8c48d</Ид>
				<Наименование>Продуктовая база</Наименование>
				<ОфициальноеНаименование>Продуктовая база</ОфициальноеНаименование>
			</Изготовитель>
		 * «Ид» — это идентификатор свойства («Изготовитель»),
		 * а не идентификатор значения свойства.
		 */
		return $this->getName();
	}

	/**
	 * В принципе, код родительского метода
	 * @see \Df\C1\Cml2\Import\Data\Entity\ProductPart\AttributeValue\Custom\Option::getValue()
	 * $this->getOption()->getId()
	 * должен работать правильно,
	 * однако метод @see getExternalId() ранее был реализован некорректно (см. примечание к нему),
	 * и поэтому если опции были занесены в базу при этом некорректном коде,
	 * то и $this->getOption()->getId() будет работать неправильно.
	 * @override
	 * @return string
	 */
	public function getValue() {return 'Изготовитель';}

	/**
	 * Перекрываем по причине, изложенной в комментарии к методу @see getValue()
	 * @override
	 * @used-by \Df\C1\Cml2\Import\Processor\Product\Type\Configurable\Update::getProductDataNewOrUpdateAttributeValueIdsCustom()
	 * @return string|int|float|bool|null
	 */
	public function getValueForObject() {return 'Изготовитель';}

	/**
	 * @override
	 * @return Attribute
	 */
	protected function createMagentoAttribute() {
		/** @var ReferenceList $referenceList */
		$referenceList = new ReferenceList;
		/** @var Attribute $result */
		$result = df_attributes()->createOrUpdate([
			'entity_type_id' => df_eav_id_product()
			,'attribute_code' => $this->getAttributeCode()
			/**
			 * В Magento CE 1.4, если поле «attribute_model» присутствует,
			 * то его значение не может быть пустым
			 * @see Mage_Eav_Model_Config::_createAttribute()
			 */
			,'backend_model' => $referenceList->getBackendModel()
			,'backend_type' => $referenceList->getBackendType()
			,'backend_table' => null
			,'frontend_model' => null
			,'frontend_input' => $referenceList->getFrontendInput()
			,'frontend_label' => 'Изготовитель'
			,'frontend_class' => null
			,'source_model' => $referenceList->getSourceModel()
			,'is_required' => 0
			,'is_user_defined' => 1
			,'default_value' => null
			,'is_unique' => 0
			// В Magento CE 1.4 значением поля «note» не может быть null
			,'note' => ''
			,'frontend_input_renderer' => null
			,'is_global' => 1
			,'is_visible' => 1
			,'is_searchable' => 1
			,'is_filterable' => 1
			,'is_comparable' => 1
			,'is_visible_on_front' => 1
			,'is_html_allowed_on_front' => 0
			,'is_used_for_price_rules' => 0
			,'is_filterable_in_search' => 1
			,'used_in_product_listing' => 0
			,'used_for_sort_by' => 0
			,'is_configurable' => 1
			,'is_visible_in_advanced_search' => 1
			,'position' => 0
			,'is_wysiwyg_enabled' => 0
			,'is_used_for_promo_rules' => 0
			,\Df\C1\C::ENTITY_EXTERNAL_ID => 'Изготовитель'
		]);
		df_assert($result->_getData(\Df\C1\C::ENTITY_EXTERNAL_ID));
		df_c1_log('Добавлено свойство «%s».', 'Изготовитель');
		return $result;
	}

	/**
	 * @override
	 * @return Attribute|null
	 */
	protected function findMagentoAttributeInRegistry() {
		//rm_1c__manufacturer
		/** @var Attribute $result */
		$result = df_attributes()->findByCode($this->getAttributeCode());
		/** @var bool $oldAttributeProcessed */
		static $oldAttributeProcessed = false;
		if (!$oldAttributeProcessed) {
			/** @var string $oldCode */
			$oldCode = 'rm_1c__manufacturer';
			/** @var Attribute $oldAttribute */
			$oldAttribute = df_attributes()->findByCode($oldCode);
			if ($oldAttribute) {
				df_remove_product_attribute($oldCode);
				df_attributes()->removeEntity($oldAttribute);
			}
			// Русифицируем свойство
			if ($result && ('Manufacturer' === $result->getFrontendLabel())) {
				$result = df_attributes()->createOrUpdate(
					['frontend_label' => 'Производитель'], $this->getAttributeCode()
				);
			}
			$oldAttributeProcessed = true;
		}
		return $result;
	}

	/**
	 * Если свойство «manufacturer» («Изготовитель», «Производитель»)
	 * было создано вручную, то размещаем его на вкладке 1C. 
	 * Если же это свойство уже присуствовало в системе   
	 * (а оно присутствует в стандартной комплектации Magento  
	 * и может отсутствовать в системе только в случае ручного удаления),  
	 * то мы оставляем это свойство на главной вкладке товара.     
	 * @override
	 * @param Attribute $attribute
	 * @return string
	 */
	protected function getGroupForAttribute(Attribute $attribute) {return
		('Изготовитель' === $attribute->_getData(\Df\C1\C::ENTITY_EXTERNAL_ID))
		? parent::getGroupForAttribute($attribute)
		: null
	;}

	/** @return string */
	private function getAttributeCode() {return 'manufacturer';}

	/**
	 * @param \Df\Xml\X $e
	 * @param EntityProduct $entityProduct
	 * @return self
	 */
	public static function i(\Df\Xml\X $e, EntityProduct $entityProduct) {return
		self::ic(__CLASS__, $e, $entityProduct)
	;}
}