<?php
namespace Df\Kkb\Request;
/** @method \Df\Kkb\Config\Area\Service configS() */
class Payment extends \Df\Payment\Request\Payment {
	/**
	 * 2015-03-09
	 * Переопределяем метод с целью сделать его публичным конкретно для данного класса.
	 * @override
	 * @see \Df\Payment\Request::amount()
	 * @used-by \Df\Kkb\RequestDocument\Signed::amount()
	 * @see \Df\Kkb\Request\Secondary::amount()
	 * @return \Df_Core_Model_Money
	 */
	public function amount() {return parent::amount();}

	/**
	 * 2015-03-09
	 * Переопределяем метод с целью сделать его публичным конкретно для данного класса.
	 * @override
	 * @see \Df\Payment\Request\Payment::orderIId()
	 * @used-by \Df\Kkb\RequestDocument\Signed::orderIId()
	 * @see \Df\Kkb\Request\Secondary::orderIId()
	 * @return string
	 */
	public function orderIId() {return parent::orderIId();}

	/**
	 * @override
	 * @see \Df\Payment\Request\Payment::_params()
	 * @used-by \Df\Payment\Request\Payment::params()
	 * @return array(string => string|int)
	 */
	protected function _params() {return [
		// из документации:
		// «В данном поле передается XML документ закодированный в Base64
		// (генерится автоматически, предоставляемой компонентой)»
		//
		// 2016-05-26
		// https://testpay.kkb.kz/doc/htm/fields_description.html
		'Signed_Order_B64' => base64_encode($this->getDocumentRegistration()->getXml())
		// из документации: «e-mail покупателя»
		//
		// 2016-05-26
		// «e-mail покупателя. Адрес должен быть реальным, иначе платеж может быть оклонен!»
		// https://testpay.kkb.kz/doc/htm/fields_description.html
		,'email' =>  $this->getCustomerEmail()
		// из документации:
		// «Линк для возврата покупателя в магазин (на сайт) после успешного проведения оплаты»
		,'BackLink' => df_url_checkout_success()
		// из документации:
		// «Линк для возврата покупателя в магазин (на сайт)
		// после оплаты в случае неудачной авторизации»
		,'FailureBackLink' => df_url_checkout_fail()
		// из документации:
		// «Линк для отправки результата авторизации в магазин.
		// Результат авторизации представлен в виде расширенного XML документа»
		,'PostLink' => $this->urlConfirm()
		// из документации:
		// «Линк для отправки неудачного результата авторизации
		// либо информации об ошибке в магазин.»
		,'FailurePostLink' => $this->urlConfirm()
		// из документации:
		// «В данном поле передается информация о языке интерфейса сервера авторизации»
		,'Language' => 'rus'
		// из документации:
		// «В данном поле передается информация о товарах или услугах,
		// за которые производится оплата. XML документ закодированный в Base64»
		,'appendix' => base64_encode($this->getDocumentOrderItems()->getXml())
	];}

	/**
	 * Перекрываем родительский метод с целью обеспечить непустоту результатата.
	 * @override
	 * @return string
	 */
	protected function getCustomerEmail() {return parent::email() ?: 'admin@magento-forum.ru';}
	
	/** @return \Df\Kkb\RequestDocument\OrderItems */
	private function getDocumentOrderItems() {return dfc($this, function() {return
		\Df\Kkb\RequestDocument\OrderItems::i($this)
	;});}
	
	/** @return \Df\Kkb\RequestDocument\Registration */
	private function getDocumentRegistration() {return dfc($this, function() {return
		\Df\Kkb\RequestDocument\Registration::i($this)
	;});}
}