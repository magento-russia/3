<?php
namespace Df\C1\Cml2\Session\ByCookie;
/**
 * 2016-11-09
 * Рамки этой сессии — последовательность обращений экземпляра учётной системы
 * от одной авторизации ("mode": "checkauth") до следующей.
 * В рамках одной сессии экземпляр учётной системы выполняет несколько обращений к интернет-магазину,
 * например:
 * 1) df.c1.cml2.action.login
 * 2) df.c1.cml2.action.init
 * 3) df.c1.cml2.action.generic.import.upload
 * 4) df.c1.cml2.action.catalog.import
 * В этом примере в рамках единой сессии экземпляр учётной системы выполнил 4 запроса.
 * На протяжении сессии мы ведём единый журнал.
 *
 * На протяжении нескольких сессий C1 проводится единая общая сессия @see \Df\C1\Cml2\Session\Composite
 * @see \Df\C1\Cml2\Session\ByCookie\C1
 */
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