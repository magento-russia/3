<?php
/**
 * @method $this setBaseName(string $value)
 * @method $this unsBaseName()
 */
class Df_Logging_Model_Archive extends Df_Core_Model {
	/**
	 * Attempt to create a new file using specified base name
	 * Or generate a base name from current date/time
	 * @param string $baseName
	 * @return bool
	 */
	public function createNew($baseName = '') {
		if (!$baseName) {
			$baseName = df_dts(Zend_Date::now(), 'YMMdH') . '.csv';
		}
		if (!$this->_validateBaseName($baseName)) {
			return false;
		}
		$file = new Varien_Io_File();
		$filename = $this->generateFilename($baseName);
		$file->setAllowCreateFolders(true);
		$file->createDestinationDir(dirname($filename));
		unset($file);
		if (!touch($filename)) {
			return false;
		}
		$this->loadByBaseName($baseName);
		return true;
	}

	/**
	 * Generate a full system filename from base name
	 * @param string $baseName
	 * @return string
	 */
	public function generateFilename($baseName) {
		return
			df_cc_path(
				$this->getBasePath()
				,mb_substr($baseName, 0, 4)
				,mb_substr($baseName, 4, 2)
				,$baseName
			)
		;
	}

	/** @return string */
	public function getBasePath() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} =
				df_cc_path(Mage::getBaseDir('var'), 'log', 'df', 'admin', 'actions')
			;
		}
		return $this->{__METHOD__};
	}

	/** @return string */
	public function getContents() {
		if ($this->_file) {
			return file_get_contents($this->_file);
		}
		return '';
	}

	/** @return string */
	public function getFilename() {
		return $this->_file;
	}

	/** @return string */
	public function getMimeType() {
		return 'text/csv';
	}

	/**
	 * Search the file in storage by base name and set it
	 * @param string $baseName
	 * @return $this
	 */
	public function loadByBaseName($baseName) {
		$this->_file = '';
		$this->unsBaseName();
		if (!$this->_validateBaseName($baseName)) {
			return $this;
		}
		$filename = $this->generateFilename($baseName);
		if (!file_exists($filename)) {
			return $this;
		}
		$this->setBaseName($baseName);
		$this->_file = $filename;
		return $this;
	}

	/**
	 * @param string $baseName
	 * @return bool
	 */
	protected function _validateBaseName($baseName) {
		return 1 === preg_match('/^[0-9]{10}\.csv$/', $baseName);
	}


	/** @var string */
	protected $_file = '';
	/**
	 * @static
	 * @param array(string => mixed) $parameters [optional]
	 * @return $this
	 */
	public static function i(array $parameters = []) {return new self($parameters);}
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}