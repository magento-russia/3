<?php
class Df_Core_Model_Cache_Design_Package extends Df_Core_Model {
	/**
	 * @param string $key
	 * @return string|null
	 */
	public function cacheGet($key) {return dfa($this->_cache, $key);}

	/**
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public function cacheSet($key, $value) {
		$this->_cache[$key] = $value;
		$this->markCachedPropertyAsModified(self::$F__CACHE);
	}

	/**
	 * @override
	 * @see Df_Core_Model::cachedGlobal()
	 * @return string[]
	 */
	protected function cachedGlobal() {return array(self::$F__CACHE);}

	/** @var array(string => string) */
	protected $_cache = [];
	/** @var string */
	private static $F__CACHE = '_cache';

	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}