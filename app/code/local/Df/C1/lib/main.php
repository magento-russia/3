<?php
/** @return Df_C1_Helper_Data */
function df_c1() {return Df_C1_Helper_Data::s();}

/**
 * Добавляет к прикладному типу товаров свойство «внешний идентификатор 1С».
 * Все требуемые для такого добавления операции выполняются только при необходимости
 * (свойство добавляется, только если оно ещё не было добавлено ранее).
 * @param Df_Eav_Model_Entity_Attribute_Set $attributeSet
 * @return void
 */
function df_c1_add_external_id_attribute_to_set(Df_Eav_Model_Entity_Attribute_Set $attributeSet) {
	$attributeSet->addExternalIdAttribute(
		\Df\C1\C::ENTITY_EXTERNAL_ID
		, 'Идентификатор товара в 1С'
		, \Df\C1\C::PRODUCT_ATTRIBUTE_GROUP_NAME
		, 2
	);
}

/** @return \Df\C1\Config\Api */
function df_c1_cfg() {return \Df\C1\Config\Api::s();}

/**
 * Пример внешнего идентификатора: «6cc37c6d-7d15-11df-901f-00e04c595000».
 * @used-by \Df\C1\Cml2\Import\Data\Collection\ProductPart\AttributeValues\Custom::createItem()
 * @used-by \Df\C1\Cml2\Import\Data\Entity\ProductPart\AttributeValue\Custom\Option::getExternalId()
 * @param $string|null
 * @return bool
 */
function df_c1_is_external_id($string) {return
	is_string($string) && 36 === mb_strlen($string) && 5 === count(explode('-', $string))
;}

/**
 * @param mixed[] $args
 * @return void
 */
function df_c1_log(...$args) {df_c1()->log(df_format($args));}

/**
 * @param Df_Catalog_Model_Product $product
 * @return void
 */
function df_c1_reindex_product(Df_Catalog_Model_Product $product) {
	$product
		->reindexPrices()
		->reindexStockStatus()
		->reindexUrlRewrites()
	;
}