<?php
namespace Df\Avangard\Action;
/** @method \Df\Avangard\Method method() */
class CustomerReturn extends \Df\Payment\Action\Confirm {
	/**
	 * @override
	 * @return \Zend_Controller_Request_Abstract
	 */
	protected function request() {
		if (!isset($this->{__METHOD__})) {
			/** @var \Zend_Controller_Request_Abstract $result */
			$this->{__METHOD__} = new \Zend_Controller_Request_Http;
			$this->{__METHOD__}->setParams(
				$this->getRequestState()->getResponse()->getData() + parent::request()->getParams()
			);
		}
		return $this->{__METHOD__};
	}

	/**
	 * Использовать getConst нельзя из-за рекурсии.
	 * @override
	 * @return string
	 */
	protected function rkOII() {return \Df\Payment\Method\WithRedirect::REQUEST_PARAM__ORDER_INCREMENT_ID;}

	/**
	 * @override
	 * @return string
	 */
	protected function signatureOwn() {df_should_not_be_here(); return null;}

	/**
	 * @override
	 * @param \Exception $e
	 * @return void
	 */
	protected function processException(\Exception $e) {
		if ($e instanceof \Df\Payment\Exception && isset($this->{__CLASS__ . '::getRequestState'})) {
			$this->logException($e);
		}
		else {
			\Mage::logException($e);
		}
		$this->showExceptionOnCheckoutScreen($e);
		$this->redirectToCheckout();
	}

	/**
	 * @override
	 * @see \Df\Payment\Action\Confirm::_process()
	 * @used-by Df_Core_Model_Action::process()
	 * @return void
	 */
	protected function _process() {
		if (!$this->order()->canInvoice()) {
			/**
			 * Бывают платёжные системы (например, «Единая касса»),
			 * которые, согласно их документации,
			 * могут несколько раз присылать подтверждение оплаты покупателем
			 * одного и того же заказа.
			 *
			 * Так вот, данная проверка гарантирует, что платёжный модуль не будет пытаться
			 * принять повторно оплату за уже оплаченный заказ.
			 *
			 * Обратите внимание, что проверку заказа на оплаченность
			 * надо сделать до вызова метода checkPaymentAmount,
			 * потому что иначе требуемая к оплате сумма будет равна нулю,
			 * и checkPaymentAmount будет сравнивать сумму от платёжной системы с нулём.
			 */
			$this->processOrderCanNotInvoice();
		}
		else {
			$this->getResponseState()->throwOnFailure();
			if ($this->getResponseState()->isPaymentServiceError()) {
				/** @var string $resultCode */
				$resultCode = $this->param('result_code');
				/** @var \Df\Avangard\Response\Registration $responseRegistration */
				$responseRegistration = \Df\Avangard\Response\Registration::i();
				$responseRegistration->loadFromPaymentInfo($this->payment());
				if ($responseRegistration->getPasswordForPaymentResponseSuccess() !== $resultCode) {
					$this->throwException('Заказ не был оплачен.');
				}
			}
			/** @var \Mage_Sales_Model_Order_Invoice $invoice */
			$invoice = $this->order()->prepareInvoice();
			$invoice->register();
			/**
			 * Код «2» означает, что средства с карты покупателя были списаны.
			 * Код «1» означает, что средства с карты покупателя были зарезервированы.
			 * Обратите внимание, что при резервировании средств мы не вызываем $invoice->capture()
			 * (что переводило бы счёт в состояние «оплачен»),
			 * а вместо этого оставляем счёт в открытом состоянии,
			 * что даёт администратору возможность снять зарезервированные покупателем средства
			 * посредством администтративного интерфейса интернет-магазина:
			 * на странице счёта появляется кнопка «Принять оплату» («Capture»).
			 */
			$invoice->capture();
			$this->saveInvoice($invoice);
			$this->order()->setState(
				\Mage_Sales_Model_Order::STATE_PROCESSING
				,\Mage_Sales_Model_Order::STATE_PROCESSING
				,$this->messageSuccess($invoice)
				,true
			);
			$this->order()->save();
			$this->order()->sendNewOrderEmail();
			$this->redirectToSuccess();
			/**
			 * В отличие от метода
			 * @see \Df\Payment\Action\Confirm::process()
			 * здесь необходимость вызова @uses \Df\Payment\Redirected::off()
			 * не вызывает сомнений, потому что @see \Df\Avangard\Action\CustomerReturn:process()
			 * обрабатывает именно сессию покупателя, а не запрос платёжной системы
			 */
			\Df\Payment\Redirected::off();
		}
	}

	/**
	 * @override
	 * @param \Exception $e
	 * @return void
	 */
	protected function processResponseForError(\Exception $e) {$this->redirectToFail();}
	
	/** @return \Df\Avangard\Request\State */
	private function getRequestState() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = \Df\Avangard\Request\State::i($this->payment());
		}
		return $this->{__METHOD__};
	}

	/** @return \Df\Avangard\Response\State */
	private function getResponseState() {return $this->getRequestState()->getResponse();}
}