<?php
class Df_Seo_Model_Settings_Images extends Df_Core_Model_Settings {
	/** @return boolean */
	public function getAddExifToJpegs() {return $this->getYesNo('add_exif_to_jpegs');}
	/** @return boolean */
	public function getUseDescriptiveFileNames() {return $this->getYesNo('use_descriptive_file_names');}
	/**
	 * @override
	 * @return string
	 */
	protected function getKeyPrefix() {return 'df_seo/images/';}
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}