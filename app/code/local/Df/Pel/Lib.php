<?php
namespace Df\Pel;
class Lib extends \Df\Core\Lib {
	/**
	 * @used-by \Df\Core\Lib::__construct()
	 * @see \Df\Core\Lib::checkEnvironment()
	 * @override
	 * @return void
	 * @throws \Exception
	 */
	protected function checkEnvironment() {
		if (ini_get('mbstring.func_overload')) {
			df_error(
				'Для корректной работы с EXIF опция «mbstring.func_overload» интерпретатора PHP'
				. ' должна иметь значение «0».'
			);
		}
	}

	/**
	 * @used-by \Df\Core\Lib::setCompatibleErrorReporting()
	 * @used-by \Df\Core\Lib::setCompatibleErrorReporting()
	 * @see \Df\Core\Lib::getIncompatibleErrorLevels()
	 * @override
	 * @return int
	 */
	protected function getIncompatibleErrorLevels() {
		if (!defined('E_DEPRECATED')) {
			define('E_DEPRECATED', 8192);
		}
		return E_STRICT | E_NOTICE | E_WARNING | E_DEPRECATED;
	}

	/**
	 * Если метод вернёт true, то папка «lib» будет добавлена в @see set_include_path(),
	 * а require_once для файлов из папки «lib» вызван не будет.
	 * Этот алгоритм используется для библиотеки Pel: @see Df_Pel_Lib
	 *
	 * Если метод вернёт false, то папка «lib» не будет добавлена в @see set_include_path(),
	 * а вместо этого будет вызван require_once для всех файлов из папки «lib».
	 * Этот алгоритм используется для всех внутренних библиотек Российской сборки Magento.
	 *
	 * @see \Df\Core\Lib::needAddToIncludePath()
	 * @used-by \Df\Core\Lib::__construct()
	 * @return bool
	 */
	protected function needAddToIncludePath() {return true;}

	/**
	 * @used-by Df_Seo_Model_Processor_Image_Exif::process()
	 * @return self
	 */
	public static function s() {return self::load(__CLASS__);}
}