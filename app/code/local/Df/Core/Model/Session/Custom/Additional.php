<?php
abstract class Df_Core_Model_Session_Custom_Additional extends Df_Core_Model_Session_Custom {
	/**
	 * 2016-11-07
	 * https://3v4l.org/Gm4M8
	 * @param Closure $f
	 * @return mixed
	 */
	public function run(\Closure $f) {
		$this->begin();
		try {return $f();}
		finally {$this->end();}
	}

	/** @return string */
	protected function getName() {return get_class($this);}

	/**
	 * @used-by run()
	 * @return void
	 */
	private function begin() {
		if (self::$_currentSession) {
			$this->_previousSession = self::$_currentSession;
		}
		if ($this->isSessionStarted()) {
			$this->_previousName = session_name();
			session_write_close();
			$_SESSION = [];
		}
		$this->start($this->getName());
		$this->init($this->getNamespace());
	}

	/**
	 * @used-by run()
	 * @return void
	 */
	private function end() {
		session_write_close();
		$_SESSION = [];
		if ($this->_previousName) {
			if ($this->_previousSession) {
				$this->_previousSession->start($this->_previousName);
				$this->_previousSession->init($this->_previousSession->getNamespace());
				$this->_previousSession = null;
			}
			else {
				session_name($this->_previousName);
				session_start();
			}
			$this->_previousName = null;
		}
	}

	/** @return bool */
	private function isSessionStarted() {
		/** http://php.net/manual/function.session-status.php#111945 */
		return
				!df_is_cli()
			&&
				(
						(function_exists('session_status'))
					?
						/**
						 * Функция @see session_status() и константа @see PHP_SESSION_ACTIVE
						 * появились только в PHP 5.4
						 * http://magento-forum.ru/topic/4627/
						 */
						(PHP_SESSION_ACTIVE === session_status())
					:
						('' !== session_id())
				)
		;
	}

	/** @var string|null */
	private $_previousName = null;
	/** @var Df_Core_Model_Session_Custom|null */
	private $_previousSession = null;
}