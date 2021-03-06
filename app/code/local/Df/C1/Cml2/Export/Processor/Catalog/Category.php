<?php
namespace Df\C1\Cml2\Export\Processor\Catalog;
/** @method \Df\C1\Cml2\Export\Document\Catalog getDocument() */
class Category extends \Df_Catalog_Model_XmlExport_Category {
	/**
	 * Структуру данных получил из анализа программного кода
	 * обработки «Б_ПомощникИмпортаТоваровБитрикс»
	 * (в частности, смотрите метод «ОбработатьЗначениеЭлемента»)
	 * @override
	 * @return array(string => mixed)
	 */
	public function getResult() {
		/** @var array(string => mixed) $result */
		$result = [
			'Ид' => $this->getExternalId()
			,'Наименование' => df_cdata($this->getCategory()->getName())
		];
		if ($this->getChildren()) {
			$result['Группы'] = self::process($this->getChildren(), $this->getDocument());
		}
		return $result;
	}

	/**
	 * Не экспортируем системный (скрытый от администратора Magento)
	 * корневой товарный раздел «Root Category».
	 * @override
	 * @return bool
	 */
	public function isEligible() {return !!$this->getCategory()->getId();}

	/** @return string */
	private function getExternalId() {return dfc($this, function() {
		if (!$this->getCategory()->get1CId()) {
			$this->getCategory()->set1CId(df_t()->guid());
			$this->getCategory()->saveRm($this->getDocument()->store());
		}
		return $this->getCategory()->get1CId();
	});}

	/**
	 * @used-by \Df\C1\Cml2\Export\Document\Catalog::getКлассификатор_Группы()
	 * @param \Df_Catalog_Model_Category[] $categories
	 * @param \Df\C1\Cml2\Export\Document\Catalog $document
	 * @return array(array(string => mixed))
	 */
	public static function process(array $categories, \Df\C1\Cml2\Export\Document\Catalog $document) {
		/** @var array(array(string => mixed)) $result */
		$result = [];
		if ($categories) {
			/** @var array(array(string => mixed)) $groups */
			$groups = [];
			foreach ($categories as $category) {
				/** @var \Df_Catalog_Model_Category $category */
				/** @var \Df\C1\Cml2\Export\Processor\Catalog\Category $processor */
				$processor = self::ic(__CLASS__, $category, $document);
				if ($processor->isEligible()) {
					$groups[]= $processor->getResult();
				}
			}
			$result['Группа'] = $groups;
		}
		return $result;
	}
}