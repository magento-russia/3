<?php
class Df_Chronopay_Model_Gate_Response extends \Df\Xml\Parser\Entity {
	/** @return $this */
	public function check() {
		if (0 != $this->getCode()) {
			df_error($this->getDiagnosticMessage());
		}
		return $this;
	}

	/** @return int */
	public function getCode() {return $this->descendI('code');}

	/** @return string */
	public function getDiagnosticMessage() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_cc_n(
				'Error in capturing the payment via ChronoPay.'
				,'ChronoPay error code: ' . $this->getCode()
				, 'ChronoPay extended error code: ' . $this->getExtendedCode()
				, 'ChronoPay error message: ' . $this->getMessage()
				, 'ChronoPay extended error message: ' . $this->getExtendedMessage()
				, 'Transaction ID: ' . $this->getTransactionId()
			);
		}
		return $this->{__METHOD__};
	}

	/** @return string */
	public function getExtendedCode() {return $this->descendS('Extended/code');}

	/** @return string */
	public function getExtendedMessage() {return $this->descendS('Extended/message');}

	/** @return string */
	public function getMessage() {return $this->descendS('message');}

	/** @return int */
	public function getTransactionId() {return $this->descendI('Transaction');}


	/**
	 * @static
	 * @param string $xml
	 * @return Df_Chronopay_Model_Gate_Response
	 */
	public static function i($xml) {return new self(array(self::$P__E => $xml));}
}