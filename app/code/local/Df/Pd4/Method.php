<?php
namespace Df\Pd4;
class Method extends \Df\Payment\Method {
	/**
	 * @override
	 * @return bool
	 */
	public function canOrder() {return true;}

	/**
	 * 2016-03-26
	 * @override
	 * @return bool
	 */
	public function canRefund() {return true;}

	/**
	 * 2016-03-26
	 * @override
	 * http://magento-forum.ru/topic/5394/
	 * @param \Varien_Object $payment
	 * @param string $amount
	 * @return $this
	 */
	public function capture(\Varien_Object $payment, $amount) {return $this;}

	/**
	 * 2016-03-26
	 * @override
	 * http://magento-forum.ru/topic/5184/
	 * http://magento-forum.ru/topic/5394/
	 * @param \Varien_Object $payment
	 * @param string $amount
	 * @return $this
	 */
	public function refund(\Varien_Object $payment, $amount) {return $this;}
}