<?php
namespace Df\C1\Cml2\Import\Data\Collection\OfferPart;
use Df\C1\Cml2\Import\Data\Entity\Offer;
use Df\C1\Cml2\Import\Data\Entity\OfferPart\OptionValue;
use Df\C1\Cml2\Import\Data\Entity\OfferPart\OptionValue\Anonymous;
use Df\C1\Cml2\Import\Data\Entity\OfferPart\OptionValue\EmptyT;
/**
 * 2016-11-08
 * Сегодня заметил, что версия 6 модуля Битрикс (наверняка и более ранние версии тоже)
 * не передаёт сайту характеристики, если в настройках узла обмена
 * не включена соответствующая опция «Выгружать характеристики предложений».
 * Если эта опция выключена, то данные будут иметь такую структуру:
		<Предложение>
 			(...)
			<ХарактеристикиТовара/>
			(...)
		</Предложение>
 * То есть модуль Битрикс всё равно передаёт сайту тег <ХарактеристикиТовара/>, но в пустом виде.
 */
class OptionValues extends \Df\C1\Cml2\Import\Data\Collection {
	/**
	 * В версиях ранее 4-й модуля 1С-Битрикс
	 * товарное предложение не перечисляет незаполненные характеристики.
	 * Например, если для товарного предложения
	 * в 1С не заполнено значение характеристики «Тип кожи»,
	 * то в версии 4 структура будет выглядеть так:
		<ХарактеристикиТовара>
			<ХарактеристикаТовара>
				<Наименование>Размер</Наименование>
				<Значение>38</Значение>
			</ХарактеристикаТовара>
			<ХарактеристикаТовара>
				<Наименование>Цвет</Наименование>
				<Значение>Бежевый</Значение>
			</ХарактеристикаТовара>
			<ХарактеристикаТовара>
				<Наименование>Тип кожи</Наименование>
				<Значение/>
			</ХарактеристикаТовара>
		</ХарактеристикиТовара>
	 *
	 * А в версии ранее 4-й структура будет выглядеть так:
		<ХарактеристикиТовара>
			<ХарактеристикаТовара>
				<Наименование>Размер</Наименование>
				<Значение>38</Значение>
			</ХарактеристикаТовара>
			<ХарактеристикаТовара>
				<Наименование>Цвет</Наименование>
				<Значение>Бежевый</Значение>
			</ХарактеристикаТовара>
		</ХарактеристикиТовара>
	 *
	 * Как можно заметить, в  версии ранее 4-й в структуре отсутствует блок
		<ХарактеристикаТовара>
			<Наименование>Тип кожи</Наименование>
			<Значение/>
		</ХарактеристикаТовара>
	 *
	 * Так вот, это приводит к тому,
	 * что у простого варианта настраиваемого товара в Magento не будет инициализировано
	 * значение товарного свойства, являющего опцией настраиваемого товара.
	 * В таком случае Magento откажется считать данное товарное предложение
	 * частью настраиваемого товара.
	 *
	 * Для устранения этой проблемы нам надо инициализировать все товарные свойства,
	 * которые являются опциями настраиваемого товара.
	 * Для тех свойств, значения которых в 1С отсутствуют,
	 * мы в Magento используем значение [неизвестно].
	 * @see \Df\C1\Cml2\Import\Data\Entity\OfferPart\OptionValue::$VALUE__UNKNOWN
	 *
	 * Смотрите также комментарий к методу
	 * @see \Df\C1\Cml2\Import\Data\Entity\OfferPart\OptionValue::getValue()
	 * Тот метод содержит решение этой же проблемы для версии 4 модуля 1С-Битрикс.
	 *
	 * НЕЛЬЗЯ автоматически вызывать данный метод из метода @see getItems(),
	 * потому что иначе мы попадём рекурсию.
	 *
	 * @return void
	 */
	public function addAbsentItems() {
		foreach ($this->getAbsentConfigurableMagentoAttributes() as $attribute) {
			/** @var \Df_Catalog_Model_Resource_Eav_Attribute $attribute */
			$this->addItem(EmptyT::i2($this->getOffer(), $attribute));
		}
	}

	/**
	 * @param string $attributeId
	 * @return OptionValue
	 */
	public function findByAttributeId($attributeId) {
		df_param_string_not_empty($attributeId, 0);
		if (!isset($this->{__METHOD__}[$attributeId])) {
			/** @var OptionValue|null $result */
			foreach ($this as $optionValue) {
				/** @var OptionValue $optionValue */
				if ($attributeId === $optionValue->am()->getId()) {
					$result = $optionValue;
					break;
				}
			}
			df_assert($result instanceof OptionValue);
			$this->{__METHOD__}[$attributeId] = $result;
		}
		return $this->{__METHOD__}[$attributeId];
	}

	/**
	 * @override
	 * @see \Df\Xml\Parser\Collection::itemClass()
	 * @return string
	 */
	protected function itemClass() {return OptionValue::class;}

	/**
	 * @override
	 * Позволяет добавлять к создаваемым элементам
	 * дополнительные, единые для всех элементов, параметры
	 * @return array(string => mixed)
	 */
	protected function itemParams() {return [OptionValue::P__OFFER => $this->getOffer()];}

	/**
	 * @override
	 * @see \Df\Xml\Parser\Collection::itemPath()
	 * @return string|string[]
	 */
	protected function itemPath() {return 'ХарактеристикиТовара/ХарактеристикаТовара';}

	/**
	 * @override
	 * @see \Df\Xml\Parser\Collection::postInitItems()
	 * @used-by \Df\Xml\Parser\Collection::getItems()
	 * @param \Df\C1\Cml2\Import\Data\Entity\OfferPart\OptionValue[] $items
	 * @return void
	 */
	protected function postInitItems(array $items) {
		if ($items && $this->getOffer()->isTypeConfigurableChild()) {
			$this->addItem(Anonymous::i($this->getOffer()));
		}
		/**
		 * НЕЛЬЗЯ автоматически вызывать здесь @see addAbsentItems(),
		 * потому что иначе мы попадём рекурсию.
		 */
	}

	/** @return \Df_Catalog_Model_Resource_Eav_Attribute[] */
	private function getAbsentConfigurableMagentoAttributes() {return dfc($this, function() {return
		// очень красивое решение!
		array_diff_key(
			$this->getOffer()->getConfigurableParent()->getConfigurableAttributes()
			, $this->getOffer()->getConfigurableAttributes()
		)
	;});}

	/** @return Offer */
	private function getOffer() {return $this->cfg(self::$P__OFFER);}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->_prop(self::$P__OFFER, Offer::class);
	}
	/** @var string */
	private static $P__OFFER = 'offer';
	/**
	 * @used-by \Df\C1\Cml2\Import\Data\Entity\Offer::характеристики()
	 * @param Offer $offer
	 * @param \Df\Xml\X $e
	 * @return self
	 */
	public static function i(Offer $offer, \Df\Xml\X $e) {return new self([
		self::$P__OFFER => $offer, self::$P__E => $e
	]);}
}