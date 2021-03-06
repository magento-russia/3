<?php
/**
 * 2015-08-09
 * Находит в БД или создаёт при отсутствии заданную налоговую ставку.
 * Вынесли реализацию в метод @uses Df_Tax_M::productClassId() ради долгосрочного кэширования.
 * @param float $rate
 * @return int|null
 */
function df_product_tax_class_id($rate) {return Df_Tax_M::s()->productClassId($rate);}

/**
 * 2016-10-19
 * @return Mage_Tax_Model_Config
 */
function df_tax_c() {return Mage::getSingleton('tax/config');}