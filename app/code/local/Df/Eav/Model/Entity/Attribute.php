<?php
class Df_Eav_Model_Entity_Attribute extends Mage_Eav_Model_Entity_Attribute {

	const P__ATTRIBUTE_MODEL = 'attribute_model';
	const P__FRONTEND_INPUT = 'frontend_input';
	/**
	 * @static
	 * @param array(string => mixed) $parameters [optional]
	 * @return Df_Eav_Model_Entity_Attribute
	 */
	public static function i(array $parameters = []) {return new self($parameters);}
}