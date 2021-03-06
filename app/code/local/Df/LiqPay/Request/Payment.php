<?php
namespace Df\LiqPay\Request;
/** @method \Df\LiqPay\Method method() */
class Payment extends \Df\Payment\Request\Payment {
	/**
	 * @override
	 * @see \Df\Payment\Request\Payment::_params()
	 * @used-by \Df\Payment\Request\Payment::params()
	 * @return array(string => string|int)
	 */
	protected function _params() {
		return array(
			'operation_xml' => base64_encode($this->xml())
			, 'signature' => base64_encode(sha1($this->password() . $this->xml() . $this->password(), 1))
		);
	}

	/** @return array(string => string) */
	private function getParamsForXml() {
		/** @var array(string => string) $result */
		$result = array(
			'version' => '1.2'
			,'default_phone' =>
				'+' . \Df\Core\Format\MobilePhoneNumber::i($this->phone())->getOnlyDigits()
			,'amount' => $this->amountS()
			// Раньше LiqPay запрещал кириллицу в описании платежа,
			// но теперь, вроде, разрешает.
			,'description' => $this->description()
			,'currency' => $this->currencyCode()
			,'order_id' => $this->orderIId()
			,'merchant_id' => $this->shopId()
			,'server_url' => $this->urlConfirm()
			// iPay и LiqPay, в отличие от других платёжных систем,
			// не поддерживают разные веб-адреса для успешного и неуспешного сценариев оплаты
			,'result_url'=> $this->urlReturn()
		);
		if ($this->method()->getSubmethod()) {
			$result['pay_way'] = $this->method()->getSubmethod();
		}
		return $result;
	}

	/**
	 * Без _nosid система будет формировать ссылку c ?___SID=U.
	 * На всякий случай избегаем этого.
	 * @return string
	 */
	private function urlReturn() {return
		\Mage::getUrl($this->method()->getCode() . '/customerReturn', array('_nosid' => true))
	;}

	/** @return string */
	private function xml() {
		if (!isset($this->{__METHOD__})) {
			/** @var \Varien_Object $object */
			$object = new \Varien_Object($this->getParamsForXml());
			$this->{__METHOD__} = $object->toXml(
				// все свойства
				$arrAttributes = []
				// корневой тег
				, $rootName = 'request'
				/* не добавлять <?xml version="1.0" encoding="UTF-8"?> */
				, $addOpenTag = false
				// запрещаем добавление CDATA,
				// потому что LiqPay эту синтаксическую конструкцию не понимает
				, $addCdata = false
			);
		}
		return $this->{__METHOD__};
	}
}