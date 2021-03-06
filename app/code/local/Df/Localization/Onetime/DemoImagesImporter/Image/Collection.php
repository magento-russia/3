<?php
class Df_Localization_Onetime_DemoImagesImporter_Image_Collection
	extends Df_Varien_Data_Collection_Singleton {
	/**
	 * @override
	 * @see Df_Varien_Data_Collection::itemClass()
	 * @used-by Df_Varien_Data_Collection::addItem()
	 * @return string
	 */
	protected function itemClass() {return Df_Localization_Onetime_DemoImagesImporter_Image::class;}

	/**
	 * @override
	 * @return void
	 */
	protected function loadInternal() {
		foreach (df_fetch_col('catalog/product_attribute_media_gallery', 'value') as $imageLocalPath) {
			/** @var string $imageLocalPath */
			$this->addItem(Df_Localization_Onetime_DemoImagesImporter_Image::i($imageLocalPath));
		}
	}

	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}