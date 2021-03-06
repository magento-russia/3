<?php
class Df_Localization_Realtime_Dictionary_ModulePart_Blocks extends \Df\Xml\Parser\Collection {
	/**
	 * @override
	 * @see \Df\Xml\Parser\Collection::itemClass()
	 * @return string
	 */
	protected function itemClass() {return Df_Localization_Realtime_Dictionary_ModulePart_Block::class;}

	/**
	 * @override
	 * @see \Df\Xml\Parser\Collection::itemPath()
	 * @return string|string[]
	 */
	protected function itemPath() {return 'block';}

	/**
	 * @static
	 * @param \Df\Xml\X $e
	 * @return Df_Localization_Realtime_Dictionary_ModulePart_Blocks
	 */
	public static function i(\Df\Xml\X $e) {return new self([self::$P__E => $e]);}
}