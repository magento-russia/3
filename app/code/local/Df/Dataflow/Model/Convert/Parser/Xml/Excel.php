<?php
class Df_Dataflow_Model_Convert_Parser_Xml_Excel extends Mage_Dataflow_Model_Convert_Parser_Xml_Excel {
	/**
	 * Цель перекрытия —
	 * поддержка тегов HTML при экспорте и импорте файлов Excel.
	 * @override
	 * @param array $fields
	 * @return string
	 */
	protected function _getXmlString(array $fields = []) {
		/** @var bool $patchNeeded */
		static $patchNeeded;
		if (is_null($patchNeeded)) {
			$patchNeeded = df_cfgr()->dataflow()->common()->getSupportHtmlTagsInExcel();
		}
		return
			$patchNeeded
			? $this->_getXmlStringDf($fields)
			: parent::_getXmlString($fields)
		;
	}

	/**
	 * @param array $fields
	 * @return string
	 */
	private function _getXmlStringDf(array $fields = []) {
		$xmlData = [];
		$xmlData[]= '<Row>';
		foreach ($fields as $value) {
			$dataType = "String";
			if (is_numeric($value)) {
				$dataType = "Number";
			}
			$value = df_sprintf("<![CDATA[%s]]>", $value);
			$xmlData[]= '<Cell><Data ss:Type="'.$dataType.'">'.$value.'</Data></Cell>';
		}
		$xmlData[]= '</Row>';
		return join('', $xmlData);
	}
}