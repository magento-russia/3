<?php
class Df_Checkout_Model_Settings_OrderComments extends Df_Core_Model_Settings {
	/**
	 * @used-by df/checkout/review/orderComments.phtml
	 * @return string
	 */
	public function getPositionRelativeToTheTerms() {return $this->v('position_relative_to_terms');}
	/** @return string */
	public function getTextareaFloat() {return $this->v('textarea_float');}
	/** @return int */
	public function getTextareaWidth() {return $this->nat('textarea_width');}
	/** @return boolean */
	public function specifyTextareaHoriziontalShift() {
		return $this->getYesNo('specify_textarea_horizontal_shift');
	}
	/**
	 * @used-by Df_Checkout_Block_Frontend_Review_OrderComments::getMarginRule()
	 * @return string
	 */
	public function getTextareaHoriziontalShiftDirection() {
		return $this->v('textarea_horizontal_shift_direction');
	}
	/** @return int */
	public function getTextareaHoriziontalShiftLength() {
		return $this->int('textarea_horizontal_shift_length');
	}
	/** @return int */
	public function getTextareaVisibleRows() {return $this->nat('textarea_rows');}
	/** @return boolean */
	public function isEnabled() {return $this->getYesNo('enabled');}
	/** @return boolean */
	public function needShowInCustomerAccount() {return $this->getYesNo('show_in_customer_account');}
	/** @return boolean */
	public function needShowInOrderEmail() {return $this->getYesNo('show_in_order_email');}
	/** @return boolean */
	public function specifyTextareaPosition() {return $this->getYesNo('specify_textarea_position');}
	/** @return boolean */
	public function specifyTextareaWidth() {return $this->getYesNo('specify_textarea_width');}
	/**
	 * @override
	 * @return string
	 */
	protected function getKeyPrefix() {return 'df_checkout/order_comments/';}
	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}