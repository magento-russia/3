<?php
/** @return bool */
function df_checkout_ergonomic() {
	return df_cfgr()->checkout()->_interface()->needShowAllStepsAtOnce();
}

/** @return bool */
function df_customer_logged_in() {return df_session_customer()->isLoggedIn();}

/**
 * @param Df_Core_Destructable $object
 * @return void
 */
function df_destructable_singleton(Df_Core_Destructable $object) {
	Df_Core_GlobalSingletonDestructor::s()->register($object);
}

/* @return Mage_Core_Model_Design_Package */
function df_design_package() {return Mage::getSingleton('core/design_package');}

/** @return Df_Core_Model_Units_Length */
function df_length() {return Df_Core_Model_Units_Length::s();}

/** @return Df_Localization_Settings_Area */
function df_loc() {static $r; return $r ?: $r = Df_Localization_Settings::s()->current();}

/**
 * @param float|int|string $amount
 * @return Df_Core_Model_Money
 */
function df_money($amount) {return Df_Core_Model_Money::i($amount); }

/**
 * @used-by df_quote()
 * @return Mage_Checkout_Model_Session|Df_Checkout_Model_Session
 */
function df_session_checkout() {return Mage::getSingleton('checkout/session');}

/** @return Mage_Core_Model_Session|Df_Core_Model_Session */
function df_session_core() {return Mage::getSingleton('core/session');}

/** @return Mage_Tax_Helper_Data */
function df_tax_h() {static $r; return $r ?: $r = Mage::helper('tax');}

/** @return Df_Core_Model_Units_Weight */
function df_weight() {return Df_Core_Model_Units_Weight::s();}


