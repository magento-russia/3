<?php
class Df_Tweaks_Model_Settings_Theme extends Df_Core_Model_Settings {
	/** @return Df_Tweaks_Model_Settings_Theme_Modern */
	public function modern() {return Df_Tweaks_Model_Settings_Theme_Modern::s();}
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}