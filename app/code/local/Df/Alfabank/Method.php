<?php
namespace Df\Alfabank;
/** @method \Df\Alfabank\Config\Area\Service configS() */
class Method extends \Df\Payment\Method\WithRedirect {
	/**
	 * @see \Df\Payment\Method::canCapture()
	 * @override
	 * @return bool
	 */
	public function canCapture() {return true;}

	/**
	 * Результат метода говорит системе о том, поддерживает ли способ оплаты
	 * автоматизированный возврат оплаты покупателю
	 * @override
	 * @return bool
	 */
	public function canRefund() {return true;}

	/**
	 * Результат метода говорит системе о том, поддерживает ли способ оплаты
	 * автоматизированный возврат части оплаты покупателю.
	 * Если способ оплаты частичный возврат допускает или же вообще возврата не допускает,
	 * то на странице документа-возврата появляется возможность редактирования
	 * количества возвращаемого товара.
	 * @used-by Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create_Items::canEditQty():
		public function canEditQty() {
		 if ($this->getCreditmemo()->getOrder()->getPayment()->canRefund()) {
			 return $this->getCreditmemo()->getOrder()->getPayment()->canRefundPartialPerInvoice();
		 }
		 return true;
	 }
	 * @override
	 * @return bool
	 */
	public function canRefundPartialPerInvoice() {return true;}

	/**
	 * Результат метода говорит системе о том, поддерживает ли способ оплаты
	 * автоматизированное разблокирование (возврат покупателю)
	 * ранее зарезервированных (но не снятых со счёта покупателя) средств
	 * @override
	 * @param \Varien_Object $payment
	 * @return bool
	 */
	public function canVoid(\Varien_Object $payment) {return true;}

	/**
	 * Обратите внимание, что платёжный шлюз Альфа-Банка (@see \Df\Alfabank\Method)
	 * не нуждается в получении параметров при перенаправлении на него покупателя.
	 * Вместо этого модуль Альфа-Банк передаёт эти параметры предварительным запросом
	 * @see \Df\Alfabank\Method::getRegistrationResponse()
	 * и платёжный шлюз возвращает модулю уникальный веб-адрес
	 * @see \Df\Alfabank\Method::getPaymentPageUrl()
	 * на который модуль перенаправляет покупателя без параметров.
	 * @override
	 * @see \Df\Payment\Method\WithRedirect::getPaymentPageParams()
	 * @used-by \Df\Payment\Block\Redirect::getFormFields()
	 * @return array(string => string|int)
	 */
	public function getPaymentPageParams() {return [];}

	/**
	 * @override
	 * @see \Df\Payment\Method\WithRedirect::getPaymentPageUrl()
	 * @used-by \Df\Payment\Block\Redirect::getTargetURL()
	 * @return string
	 */
	public function getPaymentPageUrl() {return dfa($this->getRegistrationResponse(), 'formUrl');}

	/** @return array(string => string) */
	private function getRegistrationResponse() {
		if (!isset($this->{__METHOD__})) {
			/** @var string $json */
			$json = $this->getRegistrationResponseJson();
			/** @var array(string => string) $result */
			/**
			 * Обратите внимание, что здесь мы ловим только @uses Zend_Json_Exception
			 * и внутри try расположен только вызов @uses Zend_Json::decode()
			 */
			try {
				$result = \Zend_Json::decode($json);
			}
			catch (\Zend_Json_Exception $e) {
				$this->logFailureHighLevel(
					'Платёжный шлюз при регистрации заказа в системе вернул недопустимый ответ: «%s».'
					,$this->getRegistrationResponseJson()
				);
				$this->logFailureLowLevel($e);
				$this->registrationError();
			}
			df_result_array($result);
			/** @var int $errorCode */
			$errorCode = df_int(dfa($result, 'errorCode'));
			if ($errorCode) {
				$this->registrationError(dfa($result, 'errorMessage'));
			}
			$this->iiaSet(self::INFO__PAYMENT_EXTERNAL_ID, dfa($result, 'orderId'));
			$this->ii()->save();
			$this->{__METHOD__} = $result;
		}
		return $this->{__METHOD__};
	}

	/** @return string */
	private function getRegistrationResponseJson() {
		if (!isset($this->{__METHOD__})) {
			/**
			 * 2015-03-15
			 * Обратите внимание, что мы намеренно используем длинный способ с @uses Zend_Http_Client
			 * вместо короткого способа с @see file_get_contents() ради расширенной диагностики.
			 * Всё-таки, приём оплаты банковской карты — один из самых ответственных участков
			 * Российской сборки Magento, и хочется, чтобы здесь всё было надёжно, устойчиво,
			 * и в случае сбоев диагностика была предельно ясной.
			 */
			/** @var \Zend_Http_Client $httpClient */
			$httpClient = new \Zend_Http_Client();
			$httpClient
				/**
				 * Обратите внимание, что вызывать нужно именно родительский метод
				 * @uses \Df\Payment\Method\WithRedirect::getPaymentPageParams(),
				 * а не getPaymentPageParams()
				 */
				->setUri($this->configS()->getRegistrationUri(parent::getPaymentPageParams()))
				->setMethod(\Zend_Http_Client::GET)
			;
			/** @var string $responseAsJson */
			$this->{__METHOD__} = $httpClient->request()->getBody();
			if (!$this->{__METHOD__}) {
				$this->registrationError();
			}
		}
		return $this->{__METHOD__};
	}

	/**
	 * @param string|null $message [optional]
	 * @throws \Df\Core\Exception
	 */
	private function registrationError($message = null) {
		df_error($message ? $message :
			'Платёжный шлюз Альфа-Банка в настоящее время не отвечает.'
			.'<br/>Пожалуйста, выберите другой способ оплаты или оформите заказ по телефону.'
		);
	}

	/**
	 * @used-by getRegistrationResponse()
	 * @used-by \Df\Alfabank\Request\Secondary::getPaymentExternalId()
	 */
	const INFO__PAYMENT_EXTERNAL_ID = 'order_external_id';
}