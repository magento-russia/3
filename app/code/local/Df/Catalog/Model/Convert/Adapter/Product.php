<?php
class Df_Catalog_Model_Convert_Adapter_Product extends Mage_Catalog_Model_Convert_Adapter_Product {
	/**
	 * Цель перекрытия —
	 * предоставление возможности при импорте через Dataflow
	 * импортировать вместе с товаром
	 * товарные разделы, картинки, настраиваемые покупателем опции.
	 * @override
	 * @param array(string => string) $importData
	 * @return bool
	 */
	public function saveRow(array $importData) {
		return $this->dfSaveRow($importData);
	}

	/**
	 * @param array(string => string) $importData
	 * @return bool
	 */
	private function getStoreId_Df(array $importData) {
		return dfa($importData, 'store', $this->getBatchParams('store'));
	}

	/**
	 * @param array(string => string) $importData
	 * @return bool
	 * @throws Exception
	 */
	public function dfSaveRow(array $importData) {
		/** @var Df_Catalog_Model_Product $product */
		$product = df_product();
		/**
		 * Важно! Иначе могут не импортироваться цена и вес товара:
		 * http://magento-forum.ru/topic/3728/
		 * Так происходит потому, что в методе
		 * Mage_Catalog_Model_Convert_Adapter_Product::getAttribute
		 * проверяется, применимо ли товарное свойство
		 * к типу импортируемого конкретно сейчас товара
		 * @see Mage_Catalog_Model_Convert_Adapter_Product::getAttribute
		 */
		$this->_productModel = Mage::objects()->save($product);
		/** @var Df_Core_Model_Store|bool $store */
		$store = false;
		if (empty($importData['store'])) {
			if (!is_null($this->getBatchParams('store'))) {
				$store = $this->getStoreById($this->getBatchParams('store'));
			} else {
				$message = df_mage()->catalogHelper()->__(
					'Skipping import row, required field "%s" is not defined.','store'
				);
				Mage::throwException($message);
			}
		}
		else {
			$store = $this->getStoreByCode($importData['store']);
		}
		if ($store === false) {
			$message = df_mage()->catalogHelper()->__(
				'Skipping import row, store "%s" field does not exist.',$importData['store']
			);
			Mage::throwException($message);
		}
		if (empty($importData['sku'])) {
			$message = df_mage()->catalogHelper()->__('Skipping import row, required field "%s" is not defined.', 'sku');
			Mage::throwException($message);
		}
		$product->setStoreId($store->getId());
		df_check_sku($importData['sku']);
		$productId = $product->getIdBySku($importData['sku']);
		if ($productId) {
			$product->load($productId);
		}
		else {
			$productTypes = $this->getProductTypes();
			$productAttributeSets = $this->getProductAttributeSets();
			/**
			 * Check product define type
			 */
			if (empty($importData['type']) || !isset($productTypes[strtolower($importData['type'])])) {
				$value = isset($importData['type']) ? $importData['type'] : '';
				$message = df_mage()->catalogHelper()->__(
					'Skip import row, is not valid value "%s" for field "%s"',$value,'type'
				);
				Mage::throwException($message);
			}
			/** @noinspection PhpUndefinedMethodInspection */
			$product->setTypeId($productTypes[strtolower($importData['type'])]);
			/**
			 * Check product define attribute set
			 */
			if (empty($importData['attribute_set']) || !isset($productAttributeSets[$importData['attribute_set']])) {
				$value = isset($importData['attribute_set']) ? $importData['attribute_set'] : '';
				$message = df_mage()->catalogHelper()->__(
					'Skip import row, the value "%s" is invalid for field "%s"',$value,'attribute_set'
				);
				Mage::throwException($message);
			}
			$product->setAttributeSetId($productAttributeSets[$importData['attribute_set']]);
			foreach ($this->_requiredFields as $field) {
				$attribute = $this->getAttribute($field);
				/** @noinspection PhpUndefinedMethodInspection */
				if (!isset($importData[$field]) && $attribute && $attribute->getIsRequired()) {
					$message = df_mage()->catalogHelper()->__(
						'Skipping import row, required field "%s" for new products is not defined.',$field
					);
					Mage::throwException($message);
				}
			}
		}
		$this->setProductTypeInstance($product);
		if (isset($importData['category_ids'])) {
			$product->setCategoryIds($importData['category_ids']);
		}
		if (df_cfgr()->dataflow()->products()->getEnhancedCategorySupport()) {
			// BEGIN PATCH: Import categories in various formats
			Df_Dataflow_Model_Importer_Product_Categories::i($product, $importData, $store)->process();
			// END PATCH: Import categories in various formats
		}
		foreach ($this->_ignoreFields as $field) {
			if (isset($importData[$field])) {
				unset($importData[$field]);
			}
		}
		/** @var int[] $websiteIds */
		if ($store->getId()) {
			$websiteIds = df_nta($product->getWebsiteIds());
			if (!in_array($store->getWebsiteId(), $websiteIds)) {
				$websiteIds[]= $store->getWebsiteId();
			}
			$product->setWebsiteIds($websiteIds);
		}
		if (isset($importData['websites'])) {
			$websiteIds = $product->getWebsiteIds();
			if (!is_array($websiteIds) || !$store->getId()) {
				$websiteIds = [];
			}
			$websiteCodes = df_csv_parse($importData['websites']);
			foreach ($websiteCodes as $websiteCode) {
				/** @var string $websiteCode */
				try {
					$website = df_website(trim($websiteCode));
					if (!in_array($website->getId(), $websiteIds)) {
						$websiteIds[]= $website->getId();
					}
				}
				catch (Exception $e) {}
			}
			$product->setWebsiteIds($websiteIds);
			unset($websiteIds);
		}
		/********************************************************************
		 * Заплатка для локали.
		 * Стандартный программный код приводит к проблеме:
		 * если в импортируемом файле значения опций записаны в фомате одной локали,
		 * а при импорте установлена другая локаль, то значения не будут распознаны.
		 *
		 * Данная заплатка позволяет администратору устанавливать локаль для опций
		 * в профиле Magento Dataflow.
		 *
		 * Пример:
		 *
			<action type="vichy_dataflow/import_products_parser" method="parse">
				<var name="adapter">catalog/convert_adapter_product</var>
				<var name="method">parse</var>
				<var name="locale">en_US</var>
			</action>
		 */
		/** @var string $originalLocaleCode */
		$originalLocaleCode = df_mage()->core()->translateSingleton()->getLocale();
		/** @var Exception $exception */
		$exception = null;
		df_assert_string($originalLocaleCode);
		/** @var string|null $localeCodeFromBatchParams */
		$localeCodeFromBatchParams = dfa($this->getBatchParams(), 'locale');
		if (!is_null($localeCodeFromBatchParams)) {
			df_assert_string($localeCodeFromBatchParams);
			df_mage()->core()->translateSingleton()
				->setLocale($localeCodeFromBatchParams)
				->init('adminhtml', true)
			;
		}
		try {
			foreach ($importData as $field => $value) {
				if (in_array($field, $this->_inventoryFields)) {
					continue;
				}
				/** @noinspection PhpDeprecationInspection */
				if (in_array($field, $this->_imageFields)) {
					continue;
				}
				if (is_null($value)) {
					continue;
				}
				/** @var Mage_Eav_Model_Entity_Attribute $attribute */
				$attribute = $this->getAttribute($field);
				if (!$attribute) {
					continue;
				}
				$isArray = false;
				$setValue = $value;
				if (
						'multiselect'
					===
						$attribute->getDataUsingMethod(
							Df_Eav_Model_Entity_Attribute::P__FRONTEND_INPUT
						)
				) {
					$value = explode(self::MULTI_DELIMITER, $value);
					$isArray = true;
					$setValue = [];
				}
				if ('decimal' === $value && $attribute->getBackendType()) {
					$setValue = $this->getNumber($value);
				}
				if ($attribute->usesSource()) {
					/**
					 * Данный программный код приводит к проблеме:
					 * если в импортируемом файле значения опции записаны в фомате одной локали,
					 * а при импорте установлена другая локаль, то значения не будут распознаны.
					 */
					$options = $attribute->getSource()->getAllOptions(false);
					if ($isArray) {
						foreach ($options as $item) {
							if (in_array($item['label'], $value)) {
								$setValue[]= $item['value'];
							}
						}
					} else {
						$setValue = false;
						foreach ($options as $item) {
							if ($value === $item['label']) {
								$setValue = $item['value'];
							}
						}
					}
				}
				$product->setData($field, $setValue);
			}
		}
		/***************************
		 * Заключительная часть заплатки для локали
		 */
		catch (Exception $e) {
			$exception = $e;
		}
		if ($originalLocaleCode != df_mage()->core()->translateSingleton()->getLocale()) {
			df_mage()->core()->translateSingleton()
				->setLocale($originalLocaleCode)
				->init('adminhtml', true)
			;
		}
		if (!is_null($exception)) {
			df_error($exception);
		}
		/***************************
		 * Конец заплатки для локали
		 */
		/** @noinspection PhpUndefinedMethodInspection */
		if (!$product->getVisibility()) {
			/** @noinspection PhpUndefinedMethodInspection */
			$product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
		}
		$stockData = [];
		$inventoryFields = isset($this->_inventoryFieldsProductTypes[$product->getTypeId()])
			? $this->_inventoryFieldsProductTypes[$product->getTypeId()]
			: [];
		foreach ($inventoryFields as $field) {
			if (isset($importData[$field])) {
				if (in_array($field, $this->_toNumber)) {
					$stockData[$field] = $this->getNumber($importData[$field]);
				}
				else {
					$stockData[$field] = $importData[$field];
				}
			}
		}
		$product->setStockData($stockData);
		$product->setIsMassupdate(true);
		$product->setExcludeUrlRewrite(true);
		$product->save();
		// BEGIN PATCH: import images
		$product->reload();
		/** @var Df_Dataflow_Model_Importer_Product_Gallery $galleryImporter */
		$galleryImporter = Df_Dataflow_Model_Importer_Product_Gallery::i($product, $importData);
		/** @var array[] $imageData */
		$imageData = $galleryImporter->getPrimaryImages();
		// Do something only if there are some new images!
		if (!empty ($imageData)) {
			if (df_cfgr()->dataflow()->products()->getDeletePreviousImages()) {
				//remove previous images
				$product->deleteImages();
			}
			foreach ($imageData as $file => $fields) {
				$imagePath = Mage::getBaseDir('media') . DS . 'import' . trim ($file);
				if (!is_file($imagePath)) {
					df_error("Image file %s does not exist", $imagePath);
				}
				try {
					$product->addImageToMediaGallery($imagePath, array('thumbnail','small_image','image'), false, false);
				}
				catch (Exception $e) {
					df_handle_entry_point_exception($e, false);
				}
			}
			if (df_cfgr()->dataflow()->products()->getGallerySupport()) {
				// BEGIN PATCH 2: Import of additional images
				$galleryImporter->addAdditionalImagesToProduct();
				// END PATCH 2: Import of additional images
			}
			$product->save();
		}
		// END PATCH: import images
		// BEGIN PATCH: Import of custom options
		if (df_cfgr()->dataflow()->products()->getCustomOptionsSupport()) {
			$product->reload();
			Df_Dataflow_Model_Importer_Product_Options::i($product, $importData)->process();
			$product->save();
		}
		// END PATCH: Import of custom options
		return true;
	}
}