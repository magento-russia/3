<?php
namespace Df\C1\Cml2\Import\Data\Entity\OfferPart;
use Df\C1\Cml2\Import\Data\Entity\Attribute\ReferenceList;
use Df\C1\Cml2\Import\Data\Entity\Offer;
use Df\C1\Cml2\State\Import\Collections as Cl;
class OptionValue extends \Df\C1\Cml2\Import\Data\Entity {
	/**
	 * Добавили к названию метода окончание «Magento»,
	 * чтобы избежать конфликта с родительским методом
	 * @see \Df\Xml\Parser\Entity::getAttribute()
	 * @return \Df_Catalog_Model_Resource_Eav_Attribute
	 */
	public function am() {return dfc($this, function() {
		/** @var \Df_Catalog_Model_Resource_Eav_Attribute|null $result */
		if ($this->getEntityAttribute()
			// Значение «[неизвестно]» в справочнике должно отсутствовать.
			// Добавляем его вручную.
			&& self::$VALUE__UNKNOWN !== $this->getValue()
		)  {
			$result = df_attributes()->findByExternalId($this->getEntityAttribute()->getExternalId());
			df_assert($result);
		}
		else {
			$result = $this->setupAttribute();
		}
		if (!$this->getEntityAttribute()) {
			$this->setupOption($result);
			df_attributes()->addEntity($result);
		}
		return $result;
	});}

	/**
	 * @override
	 * @return string
	 */
	public function getExternalId() {return dfc($this, function() {return
		mb_strtolower(implode(': ', [$this->getName(), $this->getValue()]))
	;});}

	/**
	 * В новой версии модуля 1С-Битрикс (ветка 4, CommerceML 2.0.8)
	 * мы можем получить следующую структуру:
	 *
		<ХарактеристикаТовара>
			<Наименование>Цвет (Обувь (Для характеристик))</Наименование>
			<Значение>Белый</Значение>
		</ХарактеристикаТовара>
	 *
	 * Здесь:
	 * 		«Цвет» — название товарного свойства
	 * 		«Обувь» — название типа товаров
	 *
	 * Мало того, что нам, разумеется, лучше дать товарному свойство в Magento название «Цвет»
	 * вместо «Цвет (Обувь (Для характеристик))»,
	 * так есть ещё и более важный аспект:
	 * в новой версии модуля 1С-Битрикс значения настраиваемых опций из ветки <ХарактеристикаТовара>
	 * дополнительно дублируются в соседней ветке <ЗначенияСвойств>:
	 *
		<Предложение>
			(...)
			<ХарактеристикиТовара>
				(...)
				<ХарактеристикаТовара>
					<Наименование>Цвет (Обувь (Для характеристик))</Наименование>
					<Значение>Белый</Значение>
				</ХарактеристикаТовара>
				(...)
			</ХарактеристикиТовара>
			<ЗначенияСвойств>
				(...)
				<ЗначенияСвойства>
					<Ид>05e26d72-01e4-11dc-a411-00055d80a2d1</Ид>
					<Значение>05e26d73-01e4-11dc-a411-00055d80a2d1</Значение>
				</ЗначенияСвойства>
	 			(...)
			</ЗначенияСвойств>
		</Предложение>
	 *
	 * Нам нужно сопоставить «свойство» «характеристике» —
	 * это позволит нам избежать двукратного создания в Magento одного и того же свойства.
	 * Сопоставить же можно по имени: характеристике «Цвет» будет соответствовать справочник «Цвет»:
	 *
		<Свойство>
			<Ид>05e26d72-01e4-11dc-a411-00055d80a2d1</Ид>
			<НомерВерсии>AAAACQAAAAA=</НомерВерсии>
			<ПометкаУдаления>false</ПометкаУдаления>
			<Наименование>Цвет</Наименование>
			<Внешний>false</Внешний>
			<ТипЗначений>Справочник</ТипЗначений>
			<ВариантыЗначений>
	 			(...)
				<Справочник>
					<ИдЗначения>dacc2953-7473-11df-b338-0011955cba6b</ИдЗначения>
					<Значение>Черный</Значение>
				</Справочник>
				<Справочник>
					<ИдЗначения>dacc2952-7473-11df-b338-0011955cba6b</ИдЗначения>
					<Значение>Зеленый</Значение>
				</Справочник>
				(...)
			</ВариантыЗначений>
		</Свойство>
	 *
	 * А для сопоставления нам нужно очистить имя «Цвет (Обувь (Для характеристик))»
	 * от шелухи и оставить «Цвет».
	 *
	 * @override
	 * @return string|null
	 */
	public function getName() {return dfc($this, function() {
		/** @var string|null $result */
		$result = parent::getName();
		return !$result || !df_ends_with($result, '))') ? $result : 
			df_trim(df_first(explode('(', $result)))
		;
	});}

	/**
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
	 * @return \Df_Eav_Model_Entity_Attribute_Option
	 */
	public function getOption() {return dfc($this, function() {return
		$this->getOptionByAttribute($this->am())
	;});}

	/**      
	 * Обратите внимание на важность значения «неизвестно».
	 * Мы можем получить из 1С подобное товарное предложение:
			<Предложение>
				(...)
				<ХарактеристикиТовара>
					<ХарактеристикаТовара>
						<Наименование>Размер</Наименование>
						<Значение>36</Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Тип кожи</Наименование>
						<Значение/>
					</ХарактеристикаТовара>
					(...)
				</ХарактеристикиТовара>
				<ЗначенияСвойств>
					<ЗначенияСвойства>
						<Ид>14ed8b06-55bd-11d9-848a-00112f43529a</Ид>
						<Значение>14ed8b08-55bd-11d9-848a-00112f43529a</Значение>
					</ЗначенияСвойства>
					(...)
				</ЗначенияСвойств>
			</Предложение>
	 * В 1С для этого товара неизвестно значение опции «Тип кожи».
	 * В Magento все опции внутри ветки ХарактеристикиТовара
	 * становятся настраиваемыми опциями настраиваемого товара.
	 * Если мы импортируем для данного товара
	 * пустую строку в качестве значения опции «Тип кожи»,
	 * то на витрине мы не сможем заказать данный товар,
	 * ибо в выпадающем списке опций значение будет отсутствовать.
	 * Да и даже если бы там пустое значение присутствовало,
	 * оно бы в вводило в ступор поупателя
	 * (аналогично, плохо обстояли бы дела с блоком пошаговой фильтрации,
	 * ибо непонятно, как там показывать пустую строку).
	 * Поэтому вместо пустой строки используем значение «неизвестно».
	 *
	 * Смотрите также комментарий к методу
	 * @see \Df\C1\Cml2\Import\Data\Collection\OfferPart\OptionValues::addAbsentItems()
	 * Тот метод содержит решение этой же проблемы
	 * для версий младше версии 4 модуля 1С-Битрикс.
	 * @return string 
	 */
	public function getValue() {return dfc($this, function() {return
		$this->leaf('Значение', self::$VALUE__UNKNOWN)				
	;});}

	/** @return int */
	public function getValueId() {return $this->getOption()->getId();}

	/** @return string */
	protected function getAttributeCodeGenerated() {return dfc($this, function() {return
		df_c1()->generateAttributeCode(
			$this->getName()
			// Намеренно убрал второй параметр ($this->getEntityProduct()->getAppliedTypeName()),
			// потому что счёл ненужным в данном случае
			// использовать приставку для системных имён товарных свойств,
			// потому что приставка (прикладной тип товара),
			// как правило, получается слишком длинной,
			// а название системного имени товарного свойства
			// ограничено 32 символами
		)				
	;});}

	/** @return \Df\C1\Cml2\Import\Data\Entity\Product */
	protected function getEntityProduct() {return $this->getOffer()->getEntityProduct();}

	/** @return Offer */
	protected function getOffer() {return $this->cfg(self::P__OFFER);}

	/** @return \Df_Catalog_Model_Resource_Eav_Attribute */
	protected function setupAttribute() {return
		df_attributes()->createOrUpdate($this->getAttributeData())
	;}

	/**
	 * @param \Df_Catalog_Model_Resource_Eav_Attribute $attribute
	 * @return void
	 */
	protected function setupOption(\Df_Catalog_Model_Resource_Eav_Attribute $attribute) {
		// Назначаем справочным значениям идентификаторы из 1С
		/** @var \Df_Eav_Model_Entity_Attribute_Option $option */
		$option = $this->getOptionByAttribute($attribute);
		$option->set1CId($this->getExternalId());
		$option->save();
	}

	/** @return array(string => mixed) */
	private function getAttributeData() {return dfc($this, function() {
		/** @var mixed $result */
		/** @var \Df_Catalog_Model_Resource_Eav_Attribute|null $attribute */
		$attribute =
			df_attributes()->findByExternalId(
				$this->getEntityAttribute()
				? $this->getEntityAttribute()->getExternalId()
				: $this->getAttributeExternalId()
			)
		;
		return
			$attribute
			?	array_merge($attribute->getData(), [
				/**
				 * Вот здесь, похоже, удачное место,
				 * чтобы добавить в уже присутствующий в Magento справочник
				 * значение текущей опции, если его там нет
				 */
				'option' => \Df_Eav_Model_Entity_Attribute_Option_Calculator::calculateStatic(
					$attribute
					,['option_0' => [$this->getValue()]]
					,$isModeInsert = true
					,$caseInsensitive = true
				)
			])
			: [
				'entity_type_id' => df_eav_id_product()
				,'attribute_code' => $this->getAttributeCodeGenerated()
				/**
				 * В Magento CE 1.4, если поле «attribute_model» присутствует,
				 * то его значение не может быть пустым
				 * @see Mage_Eav_Model_Config::_createAttribute()
				 */
				,'backend_model' => null
				,'backend_type' => 'int'
				,'backend_table' => null
				,'frontend_model' => null
				,'frontend_input' => 'select'
				,'frontend_label' => $this->getName()
				,'frontend_class' => null
				,'source_model' => null
				,'is_required' => 0
				,'is_user_defined' => 1
				,'default_value' => null
				,'is_unique' => 0
				// в Magento CE 1.4 значением поля «note» не может быть null
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
				// Какое-то значение тут надо установить,
				// потому что оно будет одним из ключей в реестре
				// (второй ключ — название справочника).
				,\Df\C1\C::ENTITY_EXTERNAL_ID => $this->getAttributeExternalId()
				,'option' => [
					'value' => ['option_0' => [$this->getValue()]]
					,'order' => ['option_0' => 0]
					,'delete' => ['option_0' => 0]
				]
			]
		;
	});}

	/** @return string */
	private function getAttributeExternalId() {return 'RM 1С - ' .  $this->getName();}

	/**
	 * В новой версии модуля 1С-Битрикс значения настраиваемых опций из ветки <ХарактеристикаТовара>
	 * дополнительно дублируются в соседней ветке <ЗначенияСвойств>:
	 *
		<Предложение>
			(...)
			<ХарактеристикиТовара>
				(...)
				<ХарактеристикаТовара>
					<Наименование>Цвет (Обувь (Для характеристик))</Наименование>
					<Значение>Белый</Значение>
				</ХарактеристикаТовара>
				(...)
			</ХарактеристикиТовара>
			<ЗначенияСвойств>
				(...)
				<ЗначенияСвойства>
					<Ид>05e26d72-01e4-11dc-a411-00055d80a2d1</Ид>
					<Значение>05e26d73-01e4-11dc-a411-00055d80a2d1</Значение>
				</ЗначенияСвойства>
	 			(...)
			</ЗначенияСвойств>
		</Предложение>
	 *
	 * Нам нужно сопоставить «свойство» «характеристике» —
	 * это позволит нам избежать двукратного создания в Magento одного и того же свойства.
	 * Сопоставить же можно по имени: характеристике «Цвет» будет соответствовать справочник «Цвет»:
	 *
		<Свойство>
			<Ид>05e26d72-01e4-11dc-a411-00055d80a2d1</Ид>
			<НомерВерсии>AAAACQAAAAA=</НомерВерсии>
			<ПометкаУдаления>false</ПометкаУдаления>
			<Наименование>Цвет</Наименование>
			<Внешний>false</Внешний>
			<ТипЗначений>Справочник</ТипЗначений>
			<ВариантыЗначений>
	 			(...)
				<Справочник>
					<ИдЗначения>dacc2953-7473-11df-b338-0011955cba6b</ИдЗначения>
					<Значение>Черный</Значение>
				</Справочник>
				<Справочник>
					<ИдЗначения>dacc2952-7473-11df-b338-0011955cba6b</ИдЗначения>
					<Значение>Зеленый</Значение>
				</Справочник>
				(...)
			</ВариантыЗначений>
		</Свойство>
	 * @return ReferenceList|null
	 */
	private function getEntityAttribute() {return dfc($this, function() {
		/** @var ReferenceList|null $r */
		$r = Cl::s()->getAttributes()->findByName($this->getName());
		// Возможно, что «свойство» и «характеристика» получили одинаковое имя по случайности?
		// Сопоставимое «свойство» обязательно должно быть типа «справочник».
		return $r instanceof ReferenceList ? $r : null;
	});}

	/**
	 * @param \Df_Catalog_Model_Resource_Eav_Attribute $attribute
	 * @return \Df_Eav_Model_Entity_Attribute_Option
	 */
	private function getOptionByAttribute(\Df_Catalog_Model_Resource_Eav_Attribute $attribute) {
		/** @var \Mage_Eav_Model_Entity_Attribute_Source_Table $source */
		$source = $attribute->getSource();
		df_assert($source instanceof \Mage_Eav_Model_Entity_Attribute_Source_Table);
		/** @var \Df_Eav_Model_Resource_Entity_Attribute_Option_Collection $options */
		$options = \Df_Eav_Model_Entity_Attribute_Option::c();
		$options->setPositionOrder('asc');
		$options->setAttributeFilter($attribute->getId());
		$options->setStoreFilter($attribute->getStoreId());
		$options->addFieldToFilter('tdv.value', $this->getValue());
		if (!$options->count()) {
			df_error(
				'Не могу найти в базе данных Magento опцию «%s» товарного свойства «%s».'
				, $this->getValue()
				, $attribute->getFrontendLabel()
			);
		}
		df_assert_eq(1, $options->count());
		/** @var \Df_Eav_Model_Entity_Attribute_Option $option */
		$result = $options->fetchItem();
		df_assert($result instanceof \Df_Eav_Model_Entity_Attribute_Option);
		return $result;
	}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->_prop(self::P__OFFER, Offer::class);
	}
	/**
	 * @used-by _construct()
	 * @used-by getOffer()
	 * @used-by \Df\C1\Cml2\Import\Data\Collection\OfferPart\OptionValues::itemParams()
	 */
	const P__OFFER = 'offer';
	/**
	 * @used-by am()
	 * @used-by getValue()
	 * @used-by \Df\C1\Cml2\Import\Data\Entity\OfferPart\OptionValue\EmptyT::getValue()
	 * @var string
	 */
	protected static $VALUE__UNKNOWN = '[неизвестно]';
}