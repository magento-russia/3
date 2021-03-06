<?php
class Df_CustomerBalance_Model_Settings extends Df_Core_Model_Settings {
	/** @return string */
	public function getTransactionalEmailSender() {return $this->v('email_identity');}
	/** @return int */
	public function getTransactionalEmailTemplateId() {return $this->nat('email_template');}
	/** @return boolean */
	public function isEnabled() {return $this->getYesNo('enabled');}
	/** @return boolean */
	public function needShowHistory() {return $this->getYesNo('show_history');}
	/**
	 * @override
	 * @return string
	 */
	protected function getKeyPrefix() {return 'df_customer/balance/';}
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}