<?php
namespace Df\C1\Cml2\Import\Data\Entity\ProductPart\AttributeValue\Custom;
use Df_Eav_Model_Entity_Attribute_Option as EavOption;
class Option extends \Df\C1\Cml2\Import\Data\Entity\ProductPart\AttributeValue\Custom {
	/**
	 * @override
	 * @return string
	 */
	public function getExternalId() {return dfc($this, function() {
		/** @var string|null $result */
		$result = $this->leaf('ИдЗначения');
		if (!$result) {
			/**
			 * В магазине sb-s.com.ua встречается такая конструкция:
			 *
				<ЗначенияСвойства>
					<Ид>6cc37c6d-7d15-11df-901f-00e04c595000</Ид>
					<Значение>6cc37c6e-7d15-11df-901f-00e04c595000</Значение>
				</ЗначенияСвойства>
			 */
			/** @var string|null $value */
			$value = $this->leaf('Значение');
			if (df_c1_is_external_id($value)) {
				$result = $value;
			}
		}
		df_result_string_not_empty($result);
		return $result;
	});}

	/**
	 * 2015-02-06
	 * @used-by getValueForObject()
	 * Обратите внимание на отличие метода @see getValue() от метода @see getValueForDataflow()
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
	 * @see getValue() и @used-by getValueForObject() вернут значение «35»,
	 * а @see getValueForDataflow() вернёт значение «натуральная кожа».
	 * @override
	 * @return string|int
	 */
	public function getValue() {return !$this->getOption() ? '' : $this->getOption()->getId();}

	/**
	 * 2015-02-06
	 * @used-by \Df\C1\Cml2\Import\Processor\Product\Type::getProductDataNewOrUpdateAttributeValues()
	 * Метод @used-by Df_Dataflow_Model_Import_Abstract_Row::getFieldValue()
	 * проверяет принадлежность результата @see getValueForDataflow()
	 * одному из типов: string|int|float|bool|null
	 *
	 * Обратите внимание на отличие метода @see getValueForDataflow()
	 * от метода @see getValue()
	 * и, косвенно, от метода @see getValueForObject(), который использует @see getValue().
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
	 * @see getValue() и @used-by getValueForObject() вернут значение «35»,
	 * а @see getValueForDataflow() вернёт значение «натуральная кожа».
	 *
	 * @override
	 * @return string|int|float|bool|null
	 */
	public function getValueForDataflow() {return
		!$this->getOption() ? '' : $this->getOption()->getData('value')
	;}

	/**
	 * 2015-02-06
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
	 * @return EavOption|null
	 */
	private function getOption() {return dfc($this, function() {
		/** @var EavOption|null $result */
		$result = null;
		df_assert(!is_null($this->getExternalId()));
		/** @var \Df_Eav_Model_Resource_Entity_Attribute_Option_Collection $options */
		$options = EavOption::c();
		$options->setPositionOrder('asc');
		$options->setAttributeFilter($this->am()->getId());
		$options->setStoreFilter($this->am()->getStoreId());
		$options->addFieldToFilter(\Df\C1\C::ENTITY_EXTERNAL_ID, $this->getExternalId());
		if (!$options->count()) {
			// Из 1С:Управление торговлей в интернет-магазин передано справочное значение,
			// отсутствующее в соответствующем справочнике интернет-магазина.
			df_c1_log(
				"Из «1С:Управление торговлей» в интернет-магазин передано"
				. " значение «{value}» свойства {attribute}"
				. " для товара «{productName}» [{productSku}],"
				. " однако это значение не является допустимым"
				. " для данного свойства."
				. "\nТакое могло произойти по причине наличия"
				. " в «1С:Управление торговлей» нескольких одинаковых (дублирующих друг друга)"
				. " значений этого свойства."
				,[
					'{value}' => $this->getExternalId()
					,'{attribute}' => $this->am()->getTitle()
					,'{productName}' => $this->getProduct()->getName()
					,'{productSku}' => $this->getProduct()->getSku()
				]
			);
			/** @var \Df_Eav_Model_Resource_Entity_Attribute_Option_Collection $optionsAll */
			$optionsAll = EavOption::c();
			$optionsAll->setPositionOrder('asc');
			$optionsAll->setAttributeFilter($this->am()->getId());
			$optionsAll->setStoreFilter($this->am()->getStoreId());
			df_c1_log('Допустимые значения свойства %s:', $this->am()->getTitle());
			foreach ($optionsAll as $option) {
				/** @var EavOption $option */
				df_c1_log('«{optionLabel}» («{optionExternalId}»)', [
					'{optionLabel}' => $option->getValue()
					,'{optionExternalId}' => $option->get1CId()
				]);
			}
		}
		else {
			$result = $options->fetchItem();
		}
		if ($result) {
			df_assert($result instanceof EavOption);
		}
		return $result;
	});}
}