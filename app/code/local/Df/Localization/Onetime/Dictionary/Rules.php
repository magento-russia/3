<?php
class Df_Localization_Onetime_Dictionary_Rules extends \Df\Xml\Parser\Collection {
	/**
	 * @override
	 * @see \Df\Xml\Parser\Collection::itemClass()
	 * @return string
	 */
	protected function itemClass() {return Df_Localization_Onetime_Dictionary_Rule::class;}

	/**
	 * @override
	 * @see \Df\Xml\Parser\Collection::itemPath()
	 * @return string|string[]
	 */
	protected function itemPath() {return 'rule';}

	/**
	 * @static
	 * @param \Df\Xml\X $e
	 * @return Df_Localization_Onetime_Dictionary_Rules
	 */
	public static function i(\Df\Xml\X $e) {return new self([self::$P__E => $e]);}
}