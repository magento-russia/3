<?php
namespace Df\LiqPay\Action;
class Confirm extends \Df\Payment\Action\Confirm {
	/**
	 * @override
	 * @return void
	 */
	protected function alternativeProcessWithoutInvoicing() {
		parent::alternativeProcessWithoutInvoicing();
		$this->comment($this->getPaymentStateMessage($this->rState()));
	}

	/**
	 * @override
	 * @return \Zend_Controller_Request_Abstract
	 */
	protected function request() {
		if (!isset($this->{__METHOD__})) {
			/** @var \Zend_Controller_Request_Abstract $result */
			$this->{__METHOD__} = new \Zend_Controller_Request_Http();
			$this->{__METHOD__}->setParams(
				$this->getPaymentInfoAsArray() + parent::request()->getParams()
			);
		}
		return $this->{__METHOD__};
	}

	/**
	 * Использовать getConst нельзя из-за рекурсии.
	 * @override
	 * @return string
	 */
	protected function rkOII() {return 'order_id';}

	/**
	 * @override
	 * @return string
	 */
	protected function signatureOwn() {
		/** @var string $result */
		$result =
			base64_encode(
				sha1(
					df_c(
						$this->configS()->getResponsePassword()
						,$this->getResponseXml()
						,$this->configS()->getResponsePassword()
					)
					,1
				)
			)
		;
		/**
		 * base64_encode возвращает false в случае сбоя
		 * (хотя непонятно, по какой причине может произойти сбой)
		 */
		df_result_string($result);
		return $result;
	}

	/**
	 * @override
	 * @return bool
	 */
	protected function needInvoice() {
		return self::PAYMENT_STATE__SUCCESS === $this->rState();
	}

	/** @return array(string => string) */
	private function getPaymentInfoAsArray() {
		return $this->getPaymentInfoAsVarienXml()->asCanonicalArray();
	}

	/** @return \Df\Xml\X */
	private function getPaymentInfoAsVarienXml() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_xml_parse($this->getResponseXml());
		}
		return $this->{__METHOD__};
	}

	/**
	 * @param string $code
	 * @return string
	 */
	private function getPaymentStateMessage($code) {
		df_param_string($code, 0);
		/** @var array(string => string) $states */
		$states = array(
			self::PAYMENT_STATE__DELAYED =>
				'Покупатель решил платить наличными через терминал Приватбанка'
			,self::PAYMENT_STATE__SUCCESS => 'Оплата получена'
			,self::PAYMENT_STATE__FAILURE => 'Покупатель отказался от оплаты'
			,self::PAYMENT_STATE__WAIT_SECURE =>
				'Покупатель оплатил заказ картой, однако система LiqPay ещё проверяет данный платёж.'
		);
		/** @var string $result */
		$result = dfa($states, $code);
		df_result_string($result);
		return $result;
	}

	/** @return string */
	private function getResponseXml() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = base64_decode(df_request('operation_xml'));
			// base64_decode возвращает false в случае сбоя
			df_result_string($this->{__METHOD__});
		}
		return $this->{__METHOD__};
	}

	const PAYMENT_STATE__DELAYED = 'delayed';
	const PAYMENT_STATE__FAILURE = 'failure';
	const PAYMENT_STATE__SUCCESS = 'success';
	const PAYMENT_STATE__WAIT_SECURE = 'wait_secure';
}