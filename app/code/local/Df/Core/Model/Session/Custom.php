<?php
abstract class Df_Core_Model_Session_Custom extends Mage_Core_Model_Session_Abstract_Varien {
	/** @return string */
	abstract protected function getSessionIdCustom();

	/**
	 * @override
	 * @return bool
	 * @see Mage_Core_Model_Session_Abstract_Varien::start():
		if (isset($_SESSION) && !$this->getSkipEmptySessionCheck()) {
			return $this;
		}
	 */
	public function getSkipEmptySessionCheck() {return true;}

	/**
	 * @override
	 * @see Mage_Core_Model_Session_Abstract_Varien::setSessionId()
	 * @param string|null $id [optional]
	 * @return \Df\C1\Cml2\Session\ByCookie\C1
	 */
	public function setSessionId($id = null) {
		/**
		 * Обратите внимание, что параметр $id мы намеренно никак не используем
		 * по следующим причинам:
		 * 1) никто не вызовет этот наш метод с параметром $id
		 * 2) если кто-то всё-таки потом когда-нибудь попробует вызвать этот наш метод
		 * с параметром $id, то это всё равно будет неверно,
		 * потому что идентификатор сессии подраумевается брать только из метода
		 * @see Df_Core_Model_Session_Custom::getSessionIdCustom().
		 */
		parent::setSessionId($this->getSessionIdCustom());
		return $this;
	}

	/**
	  * @override
	  * @param string $sessionName [optional]
	  * @return Df_Core_Model_Session_Custom
	  */
	public function start($sessionName = null) {
		self::$_currentSession = $this;
		parent::start($sessionName);
		return $this;
	}

	/**
	 * 2016-11-07
	 * Этот метод появился в родительском классе в Magent CE 1.9.3.0:
	 * https://github.com/OpenMage/magento-mirror/blob/1.9.3.0/app/code/core/Mage/Core/Model/Session/Abstract/Varien.php#L381-L389
	 * https://github.com/OpenMage/magento-mirror/blob/1.9.3.0/app/code/core/Mage/Core/Model/Session/Abstract/Varien.php#L35
	 * Перекрыаем его значением false по той же причине, которая указана в комментарии к методу
	 * @see \Df\C1\Cml2\Session\ByCookie\MagentoAPI::isSessionExpired():
	 * обмен с 1С может занимать долгое время, и нам не нужно, чтобы сессия при этом обрывалась.
	 * @override
	 * @see Mage_Core_Model_Session_Abstract_Varien::useValidateSessionExpire()
	 * @used-by Mage_Core_Model_Session_Abstract_Varien::_validate()
	 * @return bool
	 */
	public function useValidateSessionExpire() {return false;}

	/** @return string */
	protected function getNamespace() {return get_class($this);}

	/** @var Df_Core_Model_Session_Custom|null */
	protected static $_currentSession = null;
}