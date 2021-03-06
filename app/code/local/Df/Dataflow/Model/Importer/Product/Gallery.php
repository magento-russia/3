<?php
class Df_Dataflow_Model_Importer_Product_Gallery extends Df_Core_Model {
	/** @return $this */
	public function addAdditionalImagesToProduct() {
		$additionalImages =
			!$this->isAdditionalImageBorrowed()
			? $this->getAdditionalImages()
			: array_slice(
				$this->getAdditionalImages()
				,1
				,-1 + count($this->getAdditionalImages())
			)
		;
		foreach ($additionalImages as $additionalImage) {
			$this->getProduct()->addImageToMediaGallery($additionalImage, null, false, false);
		}
		return $this;
	}

	/** @return string[] */
	public function getAdditionalImages() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} =
				df_clean(array_map(
					/** @uses removeNonExistent() */
					array($this, 'removeNonExistent')
					,array_map(
						/** @uses processRawImage() */
						array($this, 'processRawImage')
						, $this->getAdditionalImagesAsRawArray()
					)
				))
			;
			foreach ($this->{__METHOD__} as &$image) {
				/** @var string $image */
				$image = df_path_n($image);
			}
		}
		return $this->{__METHOD__};
	}

	/** @return string[][] */
	public function getPrimaryImages() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = [];
			foreach ($this->getImageFields() as $primaryImageSize) {
				/** @var string $primaryImageSize */
				$imagePath = $this->getImportedValue($primaryImageSize);
				if ($imagePath && ('no_selection' !== $imagePath)) {
					if (!isset($this->{__METHOD__}[$imagePath])) {
						$this->{__METHOD__}[$imagePath] = [];
					}
					$this->{__METHOD__}[$imagePath][]= $primaryImageSize;
				}
			}
			// If there are empty primary image fields,
			// then fill it with the first additional image
			/** @var string[] $usedPrimaryImageSizes */
			/**
			 * С @see dfa_unique_fast() постоянно возникакает проблема
			 * array_flip(): Can only flip STRING and INTEGER values
			 * http://magento-forum.ru/topic/4695/
			 * Лучше верну-ка старую добрую функцию @see array_unique()
			 */
			$usedPrimaryImageSizes = array_unique(array_values($this->{__METHOD__}));
			/** @var string[] $unusedPrimaryImageSizes */
			$unusedPrimaryImageSizes = array_diff($this->getImageFields(), $usedPrimaryImageSizes);
			if (count($unusedPrimaryImageSizes) && count($this->getAdditionalImages())) {
				/** @var string $replacement */
				$replacement =
					dfa(
						$this->{__METHOD__}
						,$this->getKeyOfMainImage()
						,$this->getAdditionalImageForBorrowing()
					)
				;
				if ($replacement === $this->getAdditionalImageForBorrowing()) {
					$this->setAdditionalImageBorrowed(true);
				}
				$this->{__METHOD__}[$replacement] = $unusedPrimaryImageSizes;
			}
		}
		return $this->{__METHOD__};
	}

	/**
	 * @param string $path
	 * @return string
	 */
	private function addLeadingSlash($path) {
		return
			('/' === mb_substr($path, 0, 1))
			? $path
			: '/' . $path
		;
	}

	/**
	 * @throws Exception
	 * @param string $file_source
	 * @param string $file_target
	 * @return $this
	 */
	private function download($file_source, $file_target) {
		$rh = null;
		$wh = null;
		try {
			/** @var resource|bool $rh */
			$rh = fopen($file_source, 'rb');
			df_assert(false !== $rh, df_sprintf('Failed to download url: %s', $file_source));
			/** @var resource|bool $wh */
			$wh = fopen($file_target, 'wb');
			df_assert(false !== $wh, df_sprintf('Failed to create file: %s', $file_target));
			while (!feof($rh)) {
				$r = fwrite($wh, fread($rh, 1024));
				df_assert(
					false !== $r
					,df_sprintf(
						'Error while downloading url %s to destination %s'
						,$file_source
						,$file_target
					)
				)
				;
			}
		}
		catch (Exception $e) {
			/**
			 * Несмотря на сказанное в документации по PHP, как показывает практика,
			 * $rh в случае сбоя может быть равно не только false, но и null
			 */
			if ($rh) {fclose($rh);}
			if ($wh) {fclose($wh);}
			df_error($e);
		}
		return $this;
	}

	/**
	 * @param string $imagePath
	 * @return null|string
	 */
	private function downloadImage($imagePath) {
		$result = df_cc_path($this->getDestinationBaseDir(), basename($imagePath));
		try {
			$this->download($imagePath, $result);
		}
		catch (Exception $e) {
			df_handle_entry_point_exception($e, false);
			$result = null;
		}
		return $result;
	}

	/** @return array */
	private function getAdditionalImagesAsRawArray() {
		if (!isset($this->{__METHOD__})) {
 			$this->{__METHOD__} = array_filter(df_trim(
				explode($this->getRawImagesDelimiter(), $this->getAdditionalImagesAsString())
			));
		}
		return $this->{__METHOD__};
	}

	/** @return string */
	private function getAdditionalImagesAsString() {
		return $this->getImportedValue('df_additional_images');
	}

	/** @return string|null */
	private function getAdditionalImageForBorrowing() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_n_set(
				!$this->getAdditionalImagesAsRawArray()
				? null
				: $this->addLeadingSlash(dfa($this->getAdditionalImagesAsRawArray(), 0))
			);
		}
		return df_n_get($this->{__METHOD__});
	}

	/** @return string */
	private function getDestinationBaseDir() {return Mage::getBaseDir('media') . DS . 'import';}

	/** @return string[] */
	private function getImageFields() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = array_keys($this->getProduct()->getMediaAttributes());
		}
		return $this->{__METHOD__};
	}

	/** @return array */
	private function getImportedRow() {return $this->_getData(self::P__IMPORTED_ROW);}

	/**
	 * @param string $key
	 * @param string|null $default [optional]
	 * @return string
	 */
	private function getImportedValue($key, $default = null) {
		return dfa($this->getImportedRow(), $key, $default);
	}

	/** @return string */
	private function getKeyOfMainImage() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = dfa(array_values($this->getImageFields()), 0);
		}
		return $this->{__METHOD__};
	}

	/** @return Df_Catalog_Model_Product */
	private function getProduct() {return $this->_getData(self::P__PRODUCT);}

	/** @return string */
	private function getRawImagesDelimiter() {
		$defaultDelimiter = ';';
		return
			df_contains($this->getAdditionalImagesAsString(), $defaultDelimiter)
			? $defaultDelimiter
			: ','
		;
	}

	/** @return bool */
	private function isAdditionalImageBorrowed() {return $this->_additionalImageBorrowed;}
	/** @var bool */
	private $_additionalImageBorrowed = false;

	/**
	 * @param string $string
	 * @return bool
	 */
	private function isUrl($string) {return 'http' === mb_strtolower(mb_substr($string, 0, 4));}

	/**
	 * @param string $imagePath
	 * @return null|string
	 */
	private function makePathAbsolute($imagePath) {
		return !$imagePath ? null : $this->getDestinationBaseDir() . '/' . df_trim($imagePath, '/');
	}

	/**
	 * Этот метод может быть приватным,
	 * несмотря на использование его как callable,
	 * потому что он используется как callable только внутри своего класса:
	 * @used-by getAdditionalImages()
	 * http://php.net/manual/language.types.callable.php#113447
	 * Проверял, что это действительно допустимо, на различных версиях интерпретатора PHP:
	 * http://3v4l.org/OipEQ
	 * @param string $imagePath
	 * @return null|string
	 */
	private function processRawImage($imagePath) {
		return
			$this->isUrl($imagePath)
			? $this->downloadImage($imagePath)
			: $this->makePathAbsolute($imagePath)
		;
	}

	/**
	 * Этот метод может быть приватным,
	 * несмотря на использование его как callable,
	 * потому что он используется как callable только внутри своего класса:
	 * @used-by getAdditionalImages()
	 * http://php.net/manual/language.types.callable.php#113447
	 * Проверял, что это действительно допустимо, на различных версиях интерпретатора PHP:
	 * http://3v4l.org/OipEQ
	 * @param string $imagePath
	 * @return string|null
	 */
	private function removeNonExistent($imagePath) {
		$result = !is_file($imagePath) ? null : $imagePath;
		if (!$result) {
			Mage::log(df_sprintf('Imported image file %s does not exist', $imagePath));
		}
		return $result;
	}

	/**
	 * @param bool $value
	 * @return $this
	 */
	private function setAdditionalImageBorrowed($value) {
		$this->_additionalImageBorrowed = $value;
		return $this;
	}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this
			->_prop(self::P__IMPORTED_ROW, DF_V_ARRAY)
			->_prop(self::P__PRODUCT, Df_Catalog_Model_Product::class)
		;
	}

	const P__IMPORTED_ROW = 'importedRow';
	const P__PRODUCT = 'product';
	/**
	 * @static
	 * @param Df_Catalog_Model_Product $product
	 * @param array(string => mixed) $row
	 * @return Df_Dataflow_Model_Importer_Product_Gallery
	 */
	public static function i(Df_Catalog_Model_Product $product, array $row) {
		return new self(array(self::P__PRODUCT => $product, self::P__IMPORTED_ROW => $row));
	}
}