<?php
namespace Df\YandexMoney\Request;
use Df_Sales_Model_Order as O;
/** @method \Df\YandexMoney\Config\Area\Service configS() */
class Payment extends \Df\Payment\Request\Payment {
	/**
	 * 2015-03-09
	 * Переопределяем метод с целью сделать его публичным конкретно для данного класса.
	 * @override
	 * @used-by \Df\YandexMoney\Request\Authorize::getParamsUnique()
	 * @see \Df\Payment\Request\Payment::description()
	 * @return string
	 */
	public function description() {return parent::description();}

	/**
	 * @used-by \Df\YandexMoney\Request\Authorize::getParamsUnique()
	 * @return string
	 */
	public function descriptionForShop() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} =
				!$this->configS()->descriptionForShop()
				? $this->description()
				: strtr(
					$this->configS()->descriptionForShop()
					,$this->descriptionParams()
				)
			;
		}
		return $this->{__METHOD__};
	}

	/**
	 * Метод публичен, потому что его иногда использует сторонний класс:
	 * @see \Df\YandexMoney\Request\Authorize::getRequestParams()
	 * @return string
	 */
	public function getTransactionTag() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} =
				str_replace(
					array_keys($this->descriptionParams())
					,array_values($this->descriptionParams())
					,$this->configS()->getTransactionTag()
				)
			;
		}
		return $this->{__METHOD__};
	}

	/**
	 * @override
	 * @see \Df\Payment\Request\Payment::_params()
	 * @used-by \Df\Payment\Request\Payment::params()
	 * @return array(string => string|int)
	 */
	protected function _params() {
		if (!$this->configS()->isTestMode() && 1 > $this->amount()->getAsFixedFloat()) {
			df_error('В промышленном режиме минимальный платёж посредством Яндекс.Денег — 1 рубль.');
		}
		return array(
			'client_id' => $this->configS()->getAppId()
			,'response_type' => 'code'
			,'redirect_uri' => $this->urlCustomerReturn()
			,'scope' => $this->getScope()
		);
	}

	/**
	 * @override
	 * @return array(string => string)
	 */
	protected function descriptionParams() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = array_merge(parent::descriptionParams(), array(
				'{website.domain}' => $this->getStoreUri()->getHost()
				,'{website.name}' => $this->store()->getWebsite()->getName()
				,'{website.code}' => $this->store()->getWebsite()->getCode()
				,'{store.name}' => $this->store()->getGroup()->getName()
				,'{storeView.name}' => $this->store()->getName()
				,'{storeView.code}' => $this->store()->getCode()
			));
		}
		return $this->{__METHOD__};
	}

	/** @return string */
	private function getScope() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} =
				'money-source("wallet")'
				. " payment.to-account(\"{$this->shopId()}\").limit(,{$this->amountS()})"
			;
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-10-14
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->_prop(self::$P__ORDER, O::class);
	}

	/**
	 * @override
	 * @see \Df\Payment\Request\Payment::order()
	 * @return O
	 */
	protected function order() {return $this[self::$P__ORDER];}

	/** @var string */
	private static $P__ORDER = 'order';

	/**
	 * @static
	 * @param O $order
	 * @return self
	 */
	public static function i(O $order) {return new self(array(self::$P__ORDER => $order));}
}