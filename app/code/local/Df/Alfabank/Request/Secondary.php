<?php
namespace Df\Alfabank\Request;
/**
 * @method \Df\Alfabank\Method method()
 * @method \Df\Alfabank\Response getResponse()
 * @method \Df\Alfabank\Config\Area\Service configS()
 */
abstract class Secondary extends \Df\Payment\Request\Transaction {
	/**
	 * @used-by getUri()
	 * @return string
	 */
	abstract protected function getServiceName();

	/**
	 * @override
	 * @return \Zend_Uri_Http
	 */
	public function getUri() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = $this->configS()->getUri($this->getServiceName());
			$this->{__METHOD__}->setQuery($this->params());
		}
		return $this->{__METHOD__};
	}

	/**
	 * @override
	 * @see \Df\Payment\Request\Secondary::_params()
	 * @used-by \Df\Payment\Request\Secondary::params()
	 * @return array(string => string|int)
	 */
	protected function _params() {
		/** @var array(string => string|int) $result */
		$result = [
			'userName' => $this->shopId()
			,'password' => $this->password()
			,'orderId' => $this->getPaymentExternalId()
		];
		if ($this->hasAmount()) {
			$result['amount'] = df_round(100 * $this->amount()->getAsFixedFloat());
		}
		return $result;
	}

	/**
	 * @override
	 * @return string
	 */
	protected function getPaymentExternalId() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = $this->payment()->getAdditionalInformation(
				\Df\Alfabank\Method::INFO__PAYMENT_EXTERNAL_ID
			);
			df_result_string_not_empty($this->{__METHOD__});
		}
		return $this->{__METHOD__};
	}

	/**
	 * @override
	 * @return array(string => string)
	 */
	protected function getResponseAsArray() {
		if (!isset($this->{__METHOD__})) {
			try {
				$this->{__METHOD__} = Zend_Json::decode($this->getResponseAsJson());
			}
			catch (Zend_Json_Exception $e) {
				df_notify_exception($e);
				df_error($this->getGenericFailureMessage());
			}
			df_result_array($this->{__METHOD__});
		}
		return $this->{__METHOD__};
	}

	/** @return string */
	private function getResponseAsJson() {
		if (!isset($this->{__METHOD__})) {
			/** @var Zend_Http_Client $httpClient */
			$httpClient = new Zend_Http_Client();
			$httpClient
				->setHeaders([])
				->setUri($this->getUri())
				->setConfig(['timeout' => 10])
			;
			/** @var Zend_Http_Response $response */
			$response = $httpClient->request(Zend_Http_Client::GET);
			/** @var string $responseAsJson */
			$this->{__METHOD__} = $response->getBody();
			df_assert($this->{__METHOD__}, $this->getGenericFailureMessage());
		}
		return $this->{__METHOD__};
	}
}