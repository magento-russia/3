<?php
namespace Df\C1\Cml2\Import\Processor\Product\Type;
use Df\C1\Cml2\Import\Data\Entity\Offer;
class Configurable extends \Df\C1\Cml2\Import\Processor\Product\Type {
	/**
	 * @override
	 * @return void
	 */
	public function process() {
		if ($this->getEntityOffer()->isTypeConfigurableParent()) {
			// Сначала импортируем настраиваемые варианты в виде простых товаров
			$this->importChildren();
			// Затем создаём настраиваемый товар
			$this->importParent();
		}
	}

	/**
	 * @param Offer $offer
	 * @return array(array(string => string|int|float)))
	 */
	protected function getConfigurableProductData(Offer $offer) {
		/** @var array(mixed => mixed) $result */
		$result = [];
		foreach ($this->getTypeInstance()->getConfigurableAttributesAsArray() as $attribute) {
			/** @var array(string => string|int) $attribute */
			/** @var \Df\C1\Cml2\Import\Data\Entity\OfferPart\OptionValue $optionValue */
			$optionValue = $offer->характеристики()->findByAttributeId($attribute['attribute_id']);
			/** @var int $valueId */
			$valueId = $optionValue->getValueId();
			df_nat($valueId);
			$result[]= [
				'attribute_id' => $attribute['attribute_id']
				,'pricing_value' => $offer->getProduct()->getPrice()
				,'is_percent' => false
				,'value_index' => $valueId
				,'use_default_value' => true
			];
		}
		return $result;
	}

	/** @return array(int => array(array(string => string|int|float))) */
	protected function getConfigurableProductsData() {return dfc($this, function() {return df_map_r(
		function(Offer $o) {return [
			$o->getProduct()->getId(), $this->getConfigurableProductData($o)
		];}, $this->getEntityOffer()->getConfigurableChildren()
	);});}

	/**
	 * Обратите внимание, что 1С может вполне не передавать цену.
	 * Это возможно в следующих ситуациях:
	 * 1) Когда цена на товар отсутствует в 1С
	 * 2) Когда передача цен отключена в настройках узла обмена
	 * (а это возможно, как минимум, в новых версиях модуля 1С-Битрикс (ветка 4)).
	 * 3) В новых версиях  модуля 1С-Битрикс (ветка 4, CommerceML 2.0.8)
	 * 1С передаёт цены не в файле offers.xml (как было в прежних версиях),
	 * а отдельным файлом prices_*.xml, который передаётся после файла offers_*.xml,
	 * в то время как файл offers_*.xml цен не содержит.
	 * @override
	 * @return float|null
	 */
	protected function getPrice() {return dfc($this, function() {
		/** @var float|null $result */
		$result = null;
		//Mage::log(__METHOD__);
		//Mage::log('children count: ' . count($this->getEntityOffer()->getConfigurableChildren()));
		foreach ($this->getEntityOffer()->getConfigurableChildren() as $offer) {
			/** @var Offer $offer */
			/** @var float|null $currentPrice */
			//Mage::log('product name: ' . $offer->getProduct()->getName());
			//Mage::log('product price: ' . $offer->getProduct()->getPrice());
			$currentPrice = $offer->getProduct()->getPrice();
			/**
			 * Раньше тут стояло: !is_null($currentPrice), что неверно.
			 * Настраиваемые варианты с нулевой ценой надо игнорировать
			 * при расчёте стоимости настраиваемого товара
			 * (вариантов с нулевой ценой наверняка просто нет на складе),
			 * потому что иначе цена настраиваемого товара получится равной нулю.
			 * ведь по данному алгоритму ценой настраиваемого товара считается
			 * цена самого дешёвого настраиваемого варианта.
			 */
			if (0 < $currentPrice) {
				/** @var float $currentPriceAsFloat */
				$currentPriceAsFloat = df_float($currentPrice);
				if (is_null($result) || ($result > $currentPriceAsFloat)) {
					$result = $currentPriceAsFloat;
				}
			}
		}
		return $result;
	});}

	/** @return \Df_Catalog_Model_Product */
	protected function getProductMagento() {df_abstract($this); return null;}

	/**
	 * @override
	 * @return string
	 */
	protected function getSku() {return dfc($this, function() {
		/** @var string $result */
		if ($this->getExistingMagentoProduct()) {
			$result = $this->getExistingMagentoProduct()->getSku();
		}
		else {
			$result = $this->getEntityProduct()->getSku();
			if (!$result) {
				df_c1_log(
					'У товара «%s» в 1С отсутствует артикул.', $this->getEntityProduct()->getName()
				);
				$result = $this->getEntityOffer()->getExternalId();
			}
			$result = df_sku_adapt($result);
			if (df_h()->catalog()->product()->isExist($result)) {
				/** @var \Df_Catalog_Model_Product $existingProduct */
				$existingProduct = df_product($result);
				// Вдруг товар с данным артикулом уже присутствует в системе?
				df_c1_log(
					'В магазине уже присутствует товар с артикулом «{артикул}»:'
					. ' он имеет номер {идентификатор уже имеющегося товара},'
					. ' название «{название уже имеющегося товара}»'
					. ' и внешний идентификатор {внешний идентификатор уже имеющегося товара}.'
					, [
						'{артикул}' => $result
						,'{идентификатор уже имеющегося товара}' => $existingProduct->getId()
						,'{внешний идентификатор уже имеющегося товара}' =>
								$existingProduct->getExternalId()
						,'{название уже имеющегося товара}' => $existingProduct->getName()
					]
				);
				df_assert_ne($result, $this->getEntityOffer()->getExternalId());
				$result = df_sku_adapt($this->getEntityOffer()->getExternalId());
			}
		}
		df_result_sku($result);
		return $result;
	});}

	/**
	 * @override
	 * @return string
	 */
	protected function getType() {return \Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE;}

	/** @return \Mage_Catalog_Model_Product_Type_Configurable */
	protected function getTypeInstance() {return dfc($this, function() {
		/** @var \Mage_Catalog_Model_Product_Type_Configurable $result */
		$result = $this->getProductMagento()->getTypeInstance();
		df_assert($result instanceof \Mage_Catalog_Model_Product_Type_Configurable);
		return $result;
	});}

	/** @return void */
	private function importChildren() {
		/** @var int $count */
		$count = count($this->getEntityOffer()->getConfigurableChildren());
		if (!$count) {
			df_c1_log('Простые варианты настраиваемых товаров отсутствуют.');
		}
		else {
			df_c1_log('Найдено простых вариантов настраиваемых товаров: %d.', $count);
			df_c1_log('Импорт простых вариантов настраиваемых товаров начат.');
			foreach ($this->getEntityOffer()->getConfigurableChildren() as $offer) {
				/** @var Offer $offer */
				Configurable\Child::p($offer);
			}
			df_c1_log('Импорт простых вариантов настраиваемых товаров завершён.');
		}
	}

	/** @return void */
	private function importParent() {
		$this->getExistingMagentoProduct() ? $this->importParentUpdate() : $this->importParentNew()
	;}

	/** @return void */
	private function importParentNew() {Configurable\NewT::p_new($this);}

	/** @return void */
	private function importParentUpdate() {Configurable\Update::p_update($this);}

	/**
	 * @override
	 * @return int
	 */
	protected function getVisibility() {return \Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH;}

	/**
	 * @used-by \Df\C1\Cml2\Action\Catalog\Import::importProductsConfigurable()
	 * @param Offer $offer
	 * @return void
	 */
	public static function p(Offer $offer) {self::ic(__CLASS__, $offer)->process();}
}