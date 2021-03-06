<?php
namespace Df\Core;
use Df\Core\Helper\Path;
class Lib {
	/**
	 * @used-by load()
	 * @param string $moduleLocalPath
	 */
	final public function __construct($moduleLocalPath) {
		$this->_moduleLocalPath = $moduleLocalPath;
		$this->checkEnvironment();
		/**
		 * PATH_SEPARATOR — это символ «;» для Windows и «:» для Unix,
		 * он разделяет пути к известным интерпретатору PHP папкам со скриптами.
		 * http://stackoverflow.com/questions/9769052/why-is-there-a-path-separator-constant
		 */
		$this->needAddToIncludePath()
			? set_include_path(get_include_path() . PATH_SEPARATOR . $this->getLibDir())
			: $this->includeScripts()
		;
	}

	/**
	 * @used-by Df_Seo_Model_Processor_Image_Exif::process()
	 * @return void
	 */
	public function restoreErrorReporting() {
		if (isset($this->_errorReporting)) {
			error_reporting($this->_errorReporting);
		}
	}

	/**
	 * @used-by Df_Seo_Model_Processor_Image_Exif::process()
	 * @return void
	 */
	public function setCompatibleErrorReporting() {
		$this->_errorReporting = error_reporting();
		/**
		 * Обратите внимание, что ошибочно использовать ^ вместо &~,
		 * потому что ^ — это побитовое XOR,
		 * и если предыдущее значение error_reporting не содержало getIncompatibleErrorLevels(),
		 * то вызов с оператором ^ наоборот добавит в error_reporting getIncompatibleErrorLevels().
		 */
		error_reporting($this->_errorReporting &~ $this->getIncompatibleErrorLevels());
	}

	/**
	 * @used-by __construct()
	 * @see Df_Pel_Lib::checkEnvironment()
	 * @return void
	 * @throws Exception
	 */
	protected function checkEnvironment() {}

	/** @return int */
	protected function getIncompatibleErrorLevels() {return 0;}

	/**
	 * Если метод вернёт true, то папка «lib» будет добавлена в @see set_include_path(),
	 * а require_once для файлов из папки «lib» вызван не будет.
	 * Этот алгоритм используется для библиотеки Pel: @see Df_Pel_Lib
	 *
	 * Если метод вернёт false, то папка «lib» не будет добавлена в @see set_include_path(),
	 * а вместо этого будет вызван require_once для всех файлов из папки «lib».
	 * Этот алгоритм используется для всех внутренних библиотек Российской сборки Magento.
	 *
	 * @used-by __construct()
	 * @return bool
	 */
	protected function needAddToIncludePath() {return false;}

	/**
	 * @used-by __construct()
	 * @used-by includeScripts()
	 * @return string
	 */
	private function getLibDir() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} =
				is_dir($this->getLibDirCompiled())
				? $this->getLibDirCompiled()
				: $this->getLibDirStandard()
			;
		}
		return $this->{__METHOD__};
	}

	/** @return string */
	private function getLibDirCompiled() {
		if (!isset($this->{__METHOD__})) {
			/**
			 * @see df_cc_path() здесь использовать ещё нельзя,
			 * потому что библиотеки Российской сборки ещё не загружены
			 */
			$this->{__METHOD__} =
				!defined('COMPILER_INCLUDE_PATH')
				? ''
				: COMPILER_INCLUDE_PATH . DS . $this->getLibDirLocal()
			;
		}
		return $this->{__METHOD__};
	}

	/**
	 * Возвращает, например, строку «Df/Core/lib»
	 * @return string
	 */
	private function getLibDirLocal() {return "{$this->_moduleLocalPath}/lib";}

	/** @return string */
	private function getLibDirStandard() {
		if (!isset($this->{__METHOD__})) {
			/**
			 * @see df_cc_path() здесь использовать ещё нельзя,
			 * потому что библиотеки Российской сборки ещё не загружены
			 */
			$this->{__METHOD__} = implode(DS, array(BP, 'app', 'code', 'local', $this->getLibDirLocal()));
		}
		return $this->{__METHOD__};
	}

	/**
	 * @return void
	 * @throws Exception
	 */
	private function includeScripts() {
		$this->setCompatibleErrorReporting();
		try {
			/** @var string $libDir */
			$libDir = $this->getLibDir();
			// Нельзя писать df_path()->children(),
			// потому что библиотеки Российской сборки ещё не загружены
			foreach (Path::s()->children($libDir) as $c) {
				/** @var string $c */
				/** @var string $path */
				$path = "{$libDir}/{$c}";
				// 2016-11-22
				// «include returns FALSE on failure and raises a warning.
				// Successful includes, unless overridden by the included file, return 1.»
				// require_once ведёт себя так же.
				is_file($path) ? require_once $path : null;
			}
		}
		finally {$this->restoreErrorReporting();}
	}

	/** @var int */
	private $_errorReporting;

	/**
	 * @used-by __construct()
	 * @used-by getLibDirLocal()
	 * @var string|null
	 */
	private $_moduleLocalPath = null;

	/**
	 * @used-by \Df\Core\Boot::run()
	 * @used-by \Df\Core\Boot::initCore()
	 * @used-by \Df\YandexMarket\Category\Document::_rows()
	 * @param string $key
	 * @return \Df\Core\Lib
	 */
	public static function load($key) {
		/** @var array(string => \Df\Core\Lib) */
		static $cache;
		/** @var \Df\Core\Lib $result */
		if (!isset($cache[$key])) {
			/** @var string[] $keyA */
			$keyA = explode('_', $key);
			/** @var int $count */
			$count = count($keyA);
			/** @var string $moduleLocalPath */
			$moduleLocalPath = 1 === $count ? 'Df' . DS . $key : $keyA[0] . DS . $keyA[1];
			/** @var string $class */
			$class = 2 < $count ? $key : __CLASS__;
			/**
			 * Нам нужно сохранить в кэше не просто флаг загруженности объекта,
			 * а именно сам объект @uses \Df\Core\Lib,
			 * потому что затем у этого объекта могут вызываться методы:
			 * @see setCompatibleErrorReporting()
			 * @see restoreErrorReporting()
			 * @see Df_Seo_Model_Processor_Image_Exif::process()
			 *
			 * Обратите внимание, что выгоднее делать ключом кэша $key, а не $moduleLocalPath,
			 * чтобы не пересчитывать $moduleLocalPath заново при каждом вызове @see load()
			 */
			$cache[$key] = new $class($moduleLocalPath);
		}
		return $cache[$key];
	}
}