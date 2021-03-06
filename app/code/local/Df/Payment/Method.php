<?php
namespace Df\Payment;
use Mage_Payment_Model_Info as I;
use Mage_Sales_Model_Quote_Payment as QP;
use Mage_Sales_Model_Order_Payment as OP;
/** @method int getStore() */
abstract class Method
	extends \Mage_Payment_Model_Method_Abstract
	implements \Df\Checkout\Module\Main {
	/**
	 * @override
	 */
	public function __construct() {
		parent::__construct();
		$this->_code = self::getCodeByRmId($this->getRmId());
	}

	/**
	 * @override
	 * @param array|\Varien_Object $data
	 * @return $this
	 */
	public function assignData($data) {
		parent::assignData($data);
		if (!is_array($data)) {
			df_assert($data instanceof \Varien_Object);
			$data = $data->getData();
		}
		foreach ($this->getCustomInformationKeys() as $customInformationKey) {
			/** @var string $customInformationKey */
			df_assert_string($customInformationKey);
			/** @var string|null $value */
			$value = dfa($data, $customInformationKey);
			if (!is_null($value)) {
				$this->iiaSet($customInformationKey, $value);
			}
		}
		return $this;
	}

	/**
	 * Я так понимаю, возвращение этим методом значения true означает,
	 * что платёжный шлюз поддерживает возможность предварительной блокировки средств покупателя
	 * (с возможностью их последующего списания со счёта покупателя магазином).
	 *
	 * Причём роль этого метода — очень маленькая,
	 * он вызывается только методом @see Mage_Payment_Model_Method_Abstract::authorize(),
	 * который возбуждает исключительную ситуацию, если canAuthorize вернёт false.
	 *
	 * Обратите внимание, что многие стандартные модули оплаты Magento CE
	 * переопределяют метод @see Mage_Payment_Model_Method_Abstract::authorize(),
	 * не вызывая родительский, поэтому у них флаг canAuthorize не играет никакой роли вообще.
	 *
	 * Метод @see Mage_Sales_Model_Order_Payment::place()
	 * вызывает один из методов
	 * @see Mage_Payment_Model_Method_Abstract::authorize()
	 * @see Mage_Payment_Model_Method_Abstract::capture()
	 * @see Mage_Payment_Model_Method_Abstract::order()
	 * в зависимости от указанного в настройках платёжного модуля значения параметра payment_action
	 * Для получения значения этой настройки вызывается метод
	 * @see Mage_Payment_Model_Method_Abstract::getConfigPaymentAction().
	 *
	 * Обратите внимание, что \Df\Payment\Method::getConfigPaymentAction()
	 * всегда возвращает true, тем самым метод @see Mage_Sales_Model_Order_Payment::place()
	 * не вызывает authorize/capture/order.
	 *
	 * Обратите внимание также, что кроме @see Mage_Sales_Model_Order_Payment::place()
	 * метод authorize никто не вызывает,
	 * поэтому для модулей Российской сборки Magento данный метод вообще не будет использоваться.
	 * @override
	 * @return bool
	 */
	public function canAuthorize() {return false;}

	/**
	 * Важно для витрины вернуть true, чтобы
	 * @see \Df\Payment\Action\Confirm::process() и другие аналогичные методы
	 * (например, @see \Df\Alfabank\Action\CustomerReturn::process())
	 * могли вызвать @see Mage_Sales_Model_Order_Invoice::capture().
	 *
	 * Для административной части возвращайте true только в том случае,
	 * если метод оплаты реально поддерживает операцию capture
	 * (т.е. имеет класс Df_XXX_Request_Capture).
	 * Реализация этого класса позволит проводить двуступенчатую оплату:
	 * резервирование средств непосредственно в процессе оформления заказа
	 * и снятие средств посредством нажатия кнопки «Принять оплату» («Capture»)
	 * на административной странице счёта.
	 *
	 * Обратите внимание, что двуступенчатая оплата
	 * имеет смысл не только для дочернего данному класса @see \Df\Payment\Method\WithRedirect,
	 * но и для других прямых детей класса @see \Df\Payment\Method.
	 * @todo Например, правильным будет сделать оплату двуступенчатой для модуля «Квитанция Сбербанка»,
	 * потому что непосредственно по завершению заказа
	 * неправильно переводить счёт в состояние «Оплачен»
	 * (ведь он не оплачен! покупатель получил просто ссылку на квитанцию и далеко неочевидно,
	 * что он оплатит эту квитанцию).
	 * Вместо этого правильно будет оставлять счёт в открытом состоянии
	 * и переводить его в оплаченное состояние только после оплаты.
	 * @override
	 * @return bool
	 */
	public function canCapture() {return !df_is_admin();}

	/**
	 * @override
	 * @return bool
	 */
	public function canOrder() {return false;}

	/**
	 * Результат метода говорит системе о том, поддерживает ли способ оплаты
	 * автоматизированный возврат оплаты покупателю.
	 * @override
	 * @return bool
	 */
	public function canRefund() {return false;}

	/**
	 * Результат метода говорит системе о том, поддерживает ли способ оплаты
	 * автоматизированный возврат части оплаты покупателю.
	 * Если способ оплаты частичный возврат допускает или же вообще возврата не допускает,
	 * то на странице документа-возврата появляется возможность редактирования
	 * количества возвращаемого товара.
	 * @see Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create_Items::canEditQty():
		public function canEditQty() {
		 if ($this->getCreditmemo()->getOrder()->getPayment()->canRefund()) {
			 return $this->getCreditmemo()->getOrder()->getPayment()->canRefundPartialPerInvoice();
		 }
		 return true;
	 }
	 * @override
	 * @return bool
	 */
	public function canRefundPartialPerInvoice() {return false;}

	/**
	 * Результат метода говорит системе о том, поддерживает ли способ оплаты
	 * функциональность одобрения / отклонения платежей
	 * (в частности, такая функция есть в PayPal).
	 * @override
	 * @param \Mage_Payment_Model_Info $payment
	 * @return bool
	 */
	public function canReviewPayment(\Mage_Payment_Model_Info $payment) {return false;}

	/**
	 * Результат метода говорит системе о том, поддерживает ли способ оплаты
	 * автоматизированное разблокирование (возврат покупателю)
	 * ранее зарезервированных (но не снятых со счёта покупателя) средств
	 * @override
	 * @param \Varien_Object $payment
	 * @return bool
	 */
	public function canVoid(\Varien_Object $payment) {return false;}

	/**
	 * Этот метод вызывается только при двуступенчатой оплате,
	 * когда непосредственно в процессе оформления заказа
	 * средства с карты покупателя не были списаны, а были лишь зарезервированы,
	 * и когда затем администратор на административной странице счёта
	 * нажимает кнопку «Принять оплату» («Capture»).
	 * @see Mage_Adminhtml_Block_Sales_Order_Invoice_View::__construct():
		if ($this->_isAllowedAction('capture') && $this->getInvoice()->canCapture()) {
			 $this->_addButton('capture', array(
				 'label'     => Mage::helper('sales')->__('Capture'),
				 'class'     => 'save',
				 'onclick'   => 'setLocation(\''.$this->getCaptureUrl().'\')'
				 )
			 );
		 }
	 * @see Mage_Adminhtml_Sales_Order_InvoiceController::captureAction()
		$invoice->capture();
	 * @see Mage_Sales_Model_Order_Payment::capture()
		if (!$invoice->getIsPaid() && !$this->getIsTransactionPending()) {
			$this->getMethodInstance()->setStore($order->getStoreId())->capture($this, $amountToCapture);
		}
	 *
	 * Обратите внимание, на реальные типы аргументов:
	 * аргумент $payment — это всегда объект класса Mage_Sales_Model_Order_Payment.
	 * аргумент $amount — это вовсе не с float, как описано в базовом классе, а строка:
	 * @see Mage_Sales_Model_Order_Payment::capture():
			$amountToCapture = $this->_formatAmount($invoice->getBaseGrandTotal());
	 * @see Mage_Sales_Model_Order_Payment::_formatAmount():
		protected function _formatAmount($amount, $asFloat = false) {
		  $amount = Mage::app()->getStore()->roundPrice($amount);
		  return !$asFloat ? (string)$amount : $amount;
		}
	 * Т.к. метод @see Mage_Sales_Model_Order_Payment::refund() вызывает метод
	 * @see Mage_Sales_Model_Order_Payment::_formatAmount() без второго аргумента,
	 * то результатом вызова _formatAmount() будет именно строка.
	 *
	 * Обратите внимание, что размерностью $amount является не валюта заказа,
	 * а учётная валюта магазина:
	 * @see Mage_Sales_Model_Order_Payment::capture():
			$amountToCapture = $this->_formatAmount($invoice->getBaseGrandTotal());
	 *
	 * @override
	 * @param \Varien_Object|OP $payment
	 * @param string $amount
	 * @return $this
	 */
	public function capture(\Varien_Object $payment, $amount) {
		/** @var string $amount */
		df_assert($payment instanceof OP);
		/**
		 * @see Mage_Payment_Model_Method_Abstract::capture()
		 * контролирует допустимость вызова метода capture():
		 * если способ оплаты не поддерживает возврат средств покупателю
		 * (@see \Df\Payment\Method::canCapture()),
		 * то Mage_Payment_Model_Method_Abstract::capture() возбудит исключительную ситуацию.
		 */
		parent::capture($payment, $amount);
		/**
		 * Важно!
		 * 2015-03-09
		 * Сюда мы попадаем при вызове $invoice->capture();
		 * Такой вызов может происходить не только при нажатии кнопки «capture» администратором,
		 * но и при работе класса @see \Df\Payment\Action\Confirm и его потомков.
		 * Так вот, во втором случае мы не должны выполнять транзакцию
		 * (хотя бы потому что большинство наших способов оплаты вообще не умеют выполнять транзации,
		 * да и в сценарии с @see \Df\Payment\Action\Confirm транзация не задумывалась).
		 * потому что
		 */
		if (df_is_admin()) {
			$this->doTransaction(__FUNCTION__, $payment, df_float($amount));
		}
		return $this;
	}

	/**
	 * @see \Df\Checkout\Module\Main::config()
	 * @override
	 * @return \Df\Checkout\Module\Config\Facade
	 */
	public function config() {return \Df\Checkout\Module\Config\Facade::s($this);}

	/**
	 * @used-by \Df\Pd4\Block\Document\Rows::configA()
	 * @return \Df\Payment\Config\Area\Admin
	 */
	public function configA() {return $this->config()->admin();}

	/**
	 * @used-by getTitle()
	 * @used-by \Df\Payment\Block\Form::getDescription()
	 * @used-by \Df\Payment\Action\Confirm::showExceptionOnCheckoutScreen()
	 * @return \Df\Payment\Config\Area\Frontend
	 */
	public function configF() {return $this->config()->frontend();}

	/**
	 * @used-by \Df\Kkb\Response::configS()
	 * @used-by \Df\Payment\Block\Form::isTestMode()
	 * @return \Df\Payment\Config\Area\Service
	 */
	public function configS() {return $this->config()->service();}

	/**
	 * @used-by \Df\IPay\Block\Form::getPaymentOptions()
	 * @used-by \Df\Payment\Request\Payment::chopParam()
	 * @return \Df\Payment\Config\Manager\ConstT
	 */
	public function constManager() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = \Df\Payment\Config\Manager\ConstT::s($this);
		}
		return $this->{__METHOD__};
	}

	/**
	 * @override
	 * @see \Df\Checkout\Module\Main::getCheckoutModuleType()
	 * @used-by \Df\Checkout\Module\Bridge::convention()
	 * @used-by \Df\Checkout\Module\Config\Manager::s()
	 * @used-by \Df\Checkout\Module\Config\Area_No::s()
	 * @return string
	 */
	public function getCheckoutModuleType() {return \Df\Checkout\Module\Bridge::_type(__CLASS__);}

	/**
	 * Получаем заданное ранее администратором
	 * значение конкретной настройки платёжного способа
	 * @override
	 * @see Mage_Payment_Model_Method_Abstract::getConfigData()
	 * @param string $field
	 * @param \Df_Core_Model_StoreM|int|string|bool|null $storeId [optional]
	 * @return mixed
	 */
	public function getConfigData($field, $storeId = null) {return $this->config()->getVar($field);}

	/**
	 * @override
	 * Возвращением true мы обходим стандартную обработку authorize/capture/order
	 * метода @see Mage_Sales_Model_Order_Payment::place()
	 * @return string|bool
	 */
	public function getConfigPaymentAction() {return true;}
	
	/**
	 * @override
	 * @see \Df\Checkout\Module\Main::getConfigTemplates()
	 * @used-by \Df\Checkout\Module\Config\Manager::getTemplates()
	 * @return array(string => string)
	 */
	public function getConfigTemplates() {
		if (!isset($this->{__METHOD__})) {$this->{__METHOD__} = array(
			'{название платёжного шлюза в дательном падеже}' => $this->getNameInCaseDative()
			,'{название платёжного шлюза в именительном падеже}' => $this->getNameInNominativeCase()
			,'{название платёжного шлюза в родительном падеже}' => $this->getNameInCaseGenitive()
			,'{название платёжного шлюза в творительном падеже}' => $this->getNameInCaseInstrumental()
		);}
		return $this->{__METHOD__};
	}

	/**
	 * @param string $key
	 * @param bool $canBeTest [optional]
	 * @param string $default [optional]
	 * @return string
	 */
	public function const_($key, $canBeTest = true, $default = '') {
		return $this->constManager()->const_($key, $canBeTest, $default);
	}

	/**
	 * Этот метод вызывается только одним методом:
	 * @used-by Mage_Payment_Helper_Data::getMethodFormBlock()
	 * @override
	 * @return string
	 */
	public function getFormBlockType() {return df_con($this, 'Block\Form', Block\Form::class);}

	/**
	 * Этот метод вызывается только одним методом:
	 * @used-by Mage_Payment_Helper_Data::getInfoBlock()
	 * @override
	 * @return string
	 */
	public function getInfoBlockType() {return df_con($this, 'Block\Info', Block\Info::class);}

	/**
	 * @override
	 * @see \Df\Checkout\Module\Main::getRmId()
	 * @used-by isAvailable()
	 * @used-by \Df\Checkout\Module\Config\Manager::adaptKey()
	 * @return string
	 */
	public function getRmId() {
		if (!isset($this->{__METHOD__})) {
			/**
			 * 2016-10-18
			 * Разделитель должен быть именно пустой строкой:
			 * «LiqPay» => «liqpay»
			 */
			$this->{__METHOD__} = df_module_id($this, '');
		}
		return $this->{__METHOD__};
	}

	/**
	 * Используется для того, чтобы предложить покупателю на странице оформления заказа
	 * меню из вариантов оплаты, предоставляемых одним и тем же платёжным шлюзом.
	 * Пока эта возможность используется только модулем LiqPay:
	 * @used-by \Df\LiqPay\Request\Payment::getParamsForXml()
	 * @todo Надо бы задействовать эту возможность и для других платёжных модулей,
	 * особенно для тех, которые работают с платёжными агрегаторами
	 * (потому что уж они то заведомо предоставляют несколько вариантов оплаты).
	 * @return string|null
	 */
	public function getSubmethod() {return $this->iia(self::INFO_KEY__SUBMETHOD);}

	/** @return string */
	public function getTemplateSuccess() {return '';}

	/**
	 * @override
	 * @see \Df\Checkout\Module\Main::getTitle()
	 * @override
	 * @return string
	 */
	public function getTitle() {return $this->configF()->getTitle();}

	/**
	 * Насколько я понял, isGateway должно возвращать true,
	 * если процесс оплаты должен происходить непосредственно на странице оформления заказа,
	 * без перенаправления на страницу платёжной системы.
	 * В Российской сборке Magento так пока работает только метод @see Df_Chronopay_Model_Gate,
	 * однако он изготовлен давно и по устаревшей технологии,
	 * и поэтому не является наследником класса @see \Df\Payment\Method
	 * @override
	 * @return bool
	 */
	public function isGateway() {return false;}

	/**
	 * Работает ли модуль в тестовом режиме?
	 * Обратите внимание, что если в настройках отсутствует ключ «test»,
	 * то модуль будет всегда находиться в рабочем режиме.
	 * @return bool
	 */
	public function isTestMode() {return $this->configS()->isTestMode();}

	/**
	 * @param string|Exception $message
	 * @return $this
	 */
	public function logFailureHighLevel($message) {
		if (is_string($message)) {
			/** @var mixed[] $arguments */
			$arguments = func_get_args();
			$message = df_format($arguments);
		}
		$this->log($message, $filename = $this->getFailureLogFileNameHighLevel());
		return $this;
	}

	/**
	 * @param string|Exception $message
	 * @return $this
	 */
	public function logFailureLowLevel($message) {
		if (is_string($message)) {
			/** @var mixed[] $arguments */
			$arguments = func_get_args();
			$message = df_format($arguments);
		}
		$this->log($message, $filename = $this->getFailureLogFileNameLowLevel());
		return $this;
	}

	/**
	 * Этот метод вызывается только из метода @see Mage_Sales_Model_Order_Payment::refund().
	 * Обратите внимание, на реальные типы аргументов:
	 * аргумент $payment — это всегда объект класса Mage_Sales_Model_Order_Payment.
	 * аргумент $amount — это вовсе не с float, как описано в базовом классе, а строка:
	 * @see Mage_Sales_Model_Order_Payment::refund():
			$baseAmountToRefund = $this->_formatAmount($creditmemo->getBaseGrandTotal());
	 * @see Mage_Sales_Model_Order_Payment::_formatAmount():
		protected function _formatAmount($amount, $asFloat = false) {
		  $amount = Mage::app()->getStore()->roundPrice($amount);
		  return !$asFloat ? (string)$amount : $amount;
		}
	 * Т.к. метод @see Mage_Sales_Model_Order_Payment::refund() вызывает метод
	 * @see Mage_Sales_Model_Order_Payment::_formatAmount() без второго аргумента,
	 * то результатом вызова _formatAmount() будет именно строка.
	 *
	 * Обратите внимание, что размерностью $amount является не валюта заказа,
	 * а учётная валюта магазина:
	 * @see Mage_Sales_Model_Order_Payment::capture():
			$amountToCapture = $this->_formatAmount($invoice->getBaseGrandTotal());
	 *
	 * @override
	 * @param \Varien_Object|OP $payment
	 * @param string $amount
	 * @return $this
	 */
	public function refund(\Varien_Object $payment, $amount) {
		/** @var string $amount */
		df_assert($payment instanceof OP);
		/**
		 * @see Mage_Payment_Model_Method_Abstract::refund()
		 * контролирует допустимость вызова метода refund():
		 * если способ оплаты не поддерживает возврат средств покупателю
		 * (@see \Df\Payment\Method::canRefund()),
		 * то Mage_Payment_Model_Method_Abstract::refund() возбудит исключительную ситуацию.
		 */
		parent::refund($payment, $amount);
		$this->doTransaction(__FUNCTION__, $payment, df_float($amount));
		return $this;
	}

	/**
	 * Этот метод вызывается только из метода @see Mage_Sales_Model_Order_Payment::_void():
	 	$this->getMethodInstance()->setStore($order->getStoreId())->$gatewayCallback($this);
	 * @override
	 * @param \Mage_Sales_Model_Order_Payment|\Varien_Object $payment
	 * @return $this
	 */
	public function void(\Varien_Object $payment) {
		parent::void($payment);
		$this->doTransaction(__FUNCTION__, $payment);
		return $this;
	}

	/** @return string[] */
	protected function getCustomInformationKeys() {return array(self::INFO_KEY__SUBMETHOD);}

	/**
	 * 2016-03-06
	 * @param string|null $key [optional]
	 * @return I|OP|QP|mixed
	 */
	protected function ii($key = null) {
		/** @var I|OP|QP $result */
		$result = $this->getInfoInstance();
		return is_null($key) ? $result : $result[$key];
	}

	/**
	 * 2016-03-06
	 * @param string[] ...$keys
	 * @return mixed|array(string => mixed)
	 */
	protected function iia(...$keys) {return dfp_iia($this->ii(), $keys);}

	/**
	 * 2016-07-10
	 * @param array(string => mixed) $values
	 * @return void
	 */
	protected function iiaAdd(array $values) {dfp_add_info($this->ii(), $values);}

	/**
	 * 2016-03-06
	 * @param string|array(string => mixed) $k [optional]
	 * @param mixed|null $v [optional]
	 * @return void
	 */
	protected function iiaSet($k, $v = null) {$this->ii()->setAdditionalInformation($k, $v);}

	/**
	 * 2016-08-14
	 * @param string|array(string => mixed) $k [optional]
	 * @param mixed|null $v [optional]
	 * @return void
	 */
	protected function iiaUnset($k, $v = null) {$this->ii()->unsAdditionalInformation($k, $v);}

	/**
	 * @param string $type
	 * @param OP $payment
	 * @param float $amount [optional]
	 * @throws Exception
	 */
	private function doTransaction($type, OP $payment, $amount = 0.0) {
		try {
			\Df\Payment\Request\Transaction::doTransaction($type, $payment, $amount);
		}
		catch (Exception $exception) {
			$this->logFailureLowLevel($exception);
			if ($exception instanceof \Df\Core\Exception) {
				/** @var \Df\Core\Exception $exception */
				$this->logFailureHighLevel(df_ets($exception));
			}
			df_exception_to_session($exception);
			/**
			 * Перевозбуждаем исключительную ситуацию,
			 * потому что в случае неуспеха нам нужно прервать
			 * выполнение родительского метода
			 * @see Mage_Sales_Model_Order_Payment::capture()
			 * @see Mage_Sales_Model_Order_Payment::refund()
			 */
			df_error(
				'При транзации «%s» объектом класса «%s» произошёл описанный выше сбой.'
				,$type, get_class($this)
			);
		}
	}

	/** @return string */
	private function getFailureLogFileNameHighLevel() {
		return sprintf('rm.%s.failure.highLevel.log', $this->getRmId());
	}

	/** @return string */
	private function getFailureLogFileNameLowLevel() {
		return sprintf('rm.%s.failure.lowLevel.log', $this->getRmId());
	}

	/** @return string */
	private function getNameInCaseDative() {
		return $this->const_('names/dative', $canBeTest = false, $default = $this->getTitle());
	}

	/** @return string */
	private function getNameInCaseGenitive() {
		return $this->const_('names/genitive', $canBeTest = false, $default = $this->getTitle());
	}

	/** @return string */
	private function getNameInCaseInstrumental() {
		return $this->const_('names/instrumental', $canBeTest = false, $default = $this->getTitle());
	}

	/** @return string */
	private function getNameInNominativeCase() {
		return $this->const_('names/nominative', $canBeTest = false, $default = $this->getTitle());
	}

	/**
	 * @param string|Exception $message
	 * @param string $filename
	 * @return $this
	 */
	private function log($message, $filename) {
		if ($message instanceof Exception) {
			\Df\Qa\Message\Failure\Exception::i(array(
				\Df\Qa\Message\Failure\Exception::P__EXCEPTION => $message
			))->log();
		}
		else if (is_string($message)) {
			\Df\Qa\Message\Notification::i(array(
				\Df\Qa\Message\Notification::P__NOTIFICATION => $message
			))->log();
		}
		else {
			df_error();
		}
		return $this;
	}
	/**
	 * @used-by \Df\Payment\Config\ManagerBase::_construct()
	 * @used-by \Df\Payment\Request::_construct()
	 */

	/**
	 * @used-by getCustomInformationKeys()
	 * @used-by getSubmethod()
	 * @used-by df/liqpay/form.phtml
	 */
	const INFO_KEY__SUBMETHOD = 'df_payment__submethod';
	/**
	 * @static
	 * @used-by __construct()
	 * @used-by \Df\Payment\Config\Source::getPaymentMethodCode()
	 * @param string $rmId
	 * @return string
	 */
	public static function getCodeByRmId($rmId) {return 'df-' . $rmId;}
}