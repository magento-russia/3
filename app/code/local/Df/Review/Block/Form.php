<?php
class Df_Review_Block_Form extends Mage_Review_Block_Form {
	/**
	 * Цель перекрытия —
	 * исправление опечатки родительского метода @see Mage_Review_Block_Form::___construct():
	 * там происходит вызов $this->getAllowWriteReviewFlag вместо $this->getAllowWriteReviewFlag().
	 * @override
	 * @param bool $value
	 * @return $this
	 */
	public function setAllowWriteReviewFlag($value) {
		$this->setData('allow_write_review_flag', $value);
		$this->getAllowWriteReviewFlag = $value;
		return $this;
	}
	/** @var bool */
	public $getAllowWriteReviewFlag = null;
}