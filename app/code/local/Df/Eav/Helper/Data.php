<?php
class Df_Eav_Helper_Data extends Mage_Core_Helper_Abstract {
	/** @return bool */
	public function isPacketUpdate() {return 0 < $this->_packetUpdateRefCount;}

	/** @return void */
	public function packetUpdateBegin() {$this->_packetUpdateRefCount++;}

	/** @return void */
	public function packetUpdateEnd() {$this->_packetUpdateRefCount--;}

	/** @var int */
	private $_packetUpdateRefCount = 0;

	/**
	 * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
	 * @return bool
	 */
	public function isAttributeBelongsToProduct(Mage_Eav_Model_Entity_Attribute_Abstract $attribute) {
		return df_eav_id_product() === (int)$attribute->getEntityTypeId();
	}

	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}