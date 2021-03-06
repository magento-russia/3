<?php
/**
 * @method getAmountDelta()
 * @method Mage_Customer_Model_Customer|null getCustomer()
 * @method int|null getCustomerId()
 * @method bool getNotifyByEmail()
 * @method Df_CustomerBalance_Model_Resource_Balance getResource()
 * @method int|null getWebsiteId()
 * @method bool hasAmountDelta()
 * @method bool hasHistoryAction()
 * @method bool hasStoreId()
 * @method bool hasWebsiteId()
 * @method $this setAmount(float $value)
 * @method $this setAmountDelta(float $value)
 * @method $this setComment(string $value)
 * @method $this setCustomer(Mage_Customer_Model_Customer $value)
 * @method $this setCustomerId(int $value)
 * @method $this setHistoryAction(int $value)
 * @method $this setOrder(Df_Sales_Model_Order $value)
 * @method $this setStoreId(int $value)
 * @method $this setWebsiteId(int $value)
 */
class Df_CustomerBalance_Model_Balance extends Df_Core_Model {
	/**
	 * Public version of afterLoad
	 * @return $this
	 */
	public function afterLoad() {return $this->_afterLoad();}

	/**
	 * Delete customer orphan balances
	 * @param int $customerId
	 * @return $this
	 */
	public function deleteBalancesByCustomerId($customerId) {
		$this->getResource()->deleteBalancesByCustomerId($customerId);
		return $this;
	}

	/** @return float */
	public function getAmount() {return df_float($this->_getData('amount'));}

	/**
	 * Get customer orphan balances count
	 * @param int $customerId
	 * @return $this
	 */
	public function getOrphanBalancesCount($customerId) {
		return $this->getResource()->getOrphanBalancesCount($customerId);
	}

	/**
	 * @override
	 * @return Df_CustomerBalance_Model_Resource_Balance_Collection
	 */
	public function getResourceCollection() {return self::c();}

	/**
	 * Check whether balance completely covers specified quote
	 * @param Mage_Sales_Model_Quote|Df_Sales_Model_Quote $quote
	 * @param bool $isEstimation [optional]
	 * @return bool
	 */
	public function isFullAmountCovered(Mage_Sales_Model_Quote $quote, $isEstimation = false) {
		return
				($isEstimation || $quote->getUseCustomerBalance())
			&&
				(
						$this->getAmount()
					>=
						(
								(float)$quote->getBaseGrandTotal()
							+
								(float)$quote->getBaseCustomerBalanceAmountUsed()
						)
				)
		;
	}

	/**
	 * Load balance by customer
	 * Website id should either be set or not admin
	 * @throws Mage_Core_Exception
	 * @return $this
	 */
	public function loadByCustomer() {
		$this->_ensureCustomer();
		if ($this->hasWebsiteId()) {
			$websiteId = $this->getWebsiteId();
		}
		else {
			if (df_is_admin()) {
				Mage::throwException(Df_CustomerBalance_Helper_Data::s()->__('Website ID must be set.'));
			}
			$websiteId = df_website_id();
		}
		$this->getResource()->loadByCustomerAndWebsiteIds($this, $this->getCustomerId(), $websiteId);
		return $this;
	}

	/**
	 * Update customers balance currency code per website id
	 * @param int $websiteId
	 * @param string $currencyCode
	 * @return $this
	 */
	public function setCustomersBalanceCurrencyTo($websiteId, $currencyCode) {
		$this->getResource()->setCustomersBalanceCurrencyTo($websiteId, $currencyCode);
		return $this;
	}

	/**
	 * Specify whether email notification should be sent
	 * @param bool $shouldNotify
	 * @param int $storeId
	 * @return $this
	 * @throws Mage_Core_Exception
	 */
	public function setNotifyByEmail($shouldNotify, $storeId = null) {
		$this->setData('notify_by_email', $shouldNotify);
		if ($shouldNotify) {
			if (null === $storeId) {
				Mage::throwException(Df_CustomerBalance_Helper_Data::s()->__('Set Store ID as well.'));
			}
			$this->setStoreId($storeId);
		}
		return $this;
	}

	/**
	 * @override
	 * @return $this
	 */
	protected function _afterSave() {
		parent::_afterSave();
		// save history action
		if (abs($this->getAmountDelta())) {
			/** @var Df_CustomerBalance_Model_Balance_History $history */
			$history = Df_CustomerBalance_Model_Balance_History::i();
			$history->setBalanceModel($this);
			$history->save();
		}
		return $this;
	}

	/**
	 * @override
	 * @return $this
	 */
	protected function _beforeSave() {
		$this->_ensureCustomer();
		if (0 == $this->getWebsiteId()) {
			Mage::throwException(Df_CustomerBalance_Helper_Data::s()->__('Website ID must be set.'));
		}
		// check history action
		if (!$this->getId()) {
			$this->loadByCustomer();
			if (!$this->getId()) {
				$this->setHistoryAction(Df_CustomerBalance_Model_Balance_History::ACTION_CREATED);
			}
		}
		if (!$this->hasHistoryAction()) {
			$this->setHistoryAction(Df_CustomerBalance_Model_Balance_History::ACTION_UPDATED);
		}

		// check balance delta and email notification settings
		$delta = $this->_prepareAmountDelta();
		if (0 == $delta) {
			$this->setNotifyByEmail(false);
		}
		if ($this->getNotifyByEmail() && !$this->hasStoreId()) {
			Mage::throwException(Df_CustomerBalance_Helper_Data::s()->__('In order to send email notification, the Store ID must be set.'));
		}
		return parent::_beforeSave();
	}

	/**
	 * Make sure proper customer information is set. Load customer if required
	 * @throws Mage_Core_Exception
	 */
	protected function _ensureCustomer() {
		if ($this->getCustomer() && $this->getCustomer()->getId()) {
			$this->setCustomerId($this->getCustomer()->getId());
		}
		if (!$this->getCustomerId()) {
			Mage::throwException(Df_CustomerBalance_Helper_Data::s()->__('Customer ID must be specified.'));
		}
		if (!$this->getCustomer()) {
			$this->setCustomer(Df_Customer_Model_Customer::ld($this->getCustomerId()));
		}
		if (!$this->getCustomer()->getId()) {
			Mage::throwException(Df_CustomerBalance_Helper_Data::s()->__('Customer is not set or does not exist.'));
		}
	}

	/**
	 * @override
	 * @return Df_CustomerBalance_Model_Resource_Balance
	 */
	protected function _getResource() {return Df_CustomerBalance_Model_Resource_Balance::s();}

	/**
	 * Validate & adjust amount change
	 * @return float
	 */
	protected function _prepareAmountDelta() {
		$result = 0;
		if ($this->hasAmountDelta()) {
			$result = (float)$this->getAmountDelta();
			if ($this->getId()) {
				if (($result < 0) && (($this->getAmount() + $result) < 0)) {
					$result = -1 * $this->getAmount();
				}
			}
			else if ($result <= 0) {
				$result = 0;
			}
		}
		$this->setAmountDelta($result);
		if (!$this->getId()) {
			$this->setAmount($result);
		}
		else {
			$this->setAmount($this->getAmount() + $result);
		}
		return $result;
	}

	/** @var Df_Customer_Model_Customer */
	protected $_customer;
	/** @var string */
	protected $_eventPrefix = 'customer_balance';
	/** @var string */
	protected $_eventObject = 'balance';

	/** @used-by Df_CustomerBalance_Model_Resource_Balance_Collection::_construct() */

	const P__ID = 'balance_id';

	/** @return Df_CustomerBalance_Model_Resource_Balance_Collection */
	public static function c() {return new Df_CustomerBalance_Model_Resource_Balance_Collection;}
	/**
	 * @static
	 * @param array(string => mixed) $parameters [optional]
	 * @return $this
	 */
	public static function i(array $parameters = []) {return new self($parameters);}
	/**
	 * @static
	 * @param int|string $id
	 * @param string|null $field [optional]
	 * @return $this
	 */
	public static function ld($id, $field = null) {return df_load(self::i(), $id, $field);}
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}