<?php
namespace Df\C1\Cml2\Session\ByCookie;
class C1 extends \Df_Core_Model_Session_Custom_Primary {
	/**
	 * @used-by Df_C1_Helper_Data::logger()
	 * @return string|null
	 */
	public function getFileName_Log() {return $this->getData(self::$P__FILE_NAME_LOG);}

	/**
	 * @used-by Df_C1_Helper_Data::logger()
	 * @param string $value
	 * @return void
	 */
	public function setFileName_Log($value) {$this->setData(self::$P__FILE_NAME_LOG, $value);}

	/**
	 * @override
	 * @see Df_Core_Model_Session_Custom_Primary::getSessionIdCustom()
	 * @used-by Df_Core_Model_Session_Custom_Primary::setSessionId()
	 * @return string
	 */
	protected function getSessionIdCustom() {return \Df\C1\Cml2\Action\Login::sessionId();}

	/** @var string */
	private static $P__FILE_NAME_LOG = 'file_name_log';

	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}