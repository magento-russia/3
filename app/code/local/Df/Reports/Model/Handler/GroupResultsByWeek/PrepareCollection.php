<?php
/**
 * @method Df_Core_Model_Event_Core_Collection_Abstract_LoadBefore getEvent()
 */
class Df_Reports_Model_Handler_GroupResultsByWeek_PrepareCollection extends Df_Core_Model_Handler {
	/**
	 * Метод-обработчик события
	 * @override
	 * @return void
	 */
	public function handle() {
		if (
			$this->isItReportCollection()
			&& df_cfgr()->reports()->common()->enableGroupByWeek()
			&& df_h()->reports()->groupResultsByWeek()->isSelectedInFilter()
		) {
			if (!$this->getReportCollection()->isTotals()) {
				$this->adjustGroupPart();
			}
			$this->adjustColumns();
		}
	}

	/**
	 * Класс события (для валидации события)
	 * @override
	 * @return string
	 */
	protected function getEventClass() {return Df_Core_Model_Event_Core_Collection_Abstract_LoadBefore::class;}

	/** @return $this */
	private function adjustColumns() {
		/** @var array $partColumns */
		$partColumns= $this->getSelect()->getPart(Zend_Db_Select::COLUMNS);
		df_assert_array($partColumns);
		$this->getSelect()->reset(Zend_Db_Select::COLUMNS);
		foreach ($partColumns as &$column) {
			/** @var array $column */
			df_assert_array($column);
			/** @var string|null $columnName */
			$columnName = dfa($column, 2);
			if (!is_null($columnName)) {
				df_assert_string($columnName);
				if ('period' === $columnName) {
					$column[1] = new Zend_Db_Expr ('WEEK(period)');
				}
			}
		}
		$this->getSelect()->setPart(Zend_Db_Select::COLUMNS, $partColumns);
		return $this;
	}

	/** @return $this */
	private function adjustGroupPart() {
		/** @var array $partGroup */
		$partGroup = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
		df_assert_array($partGroup);
		$this->getSelect()->reset(Zend_Db_Select::GROUP);
		$cleanedPartGroup = [];
		foreach ($partGroup as $partGroupItem) {
			if (is_string($partGroupItem)) {
				if (df_contains($partGroupItem, 'period')) {
					continue;
				}
			}
			$cleanedPartGroup[]= $partGroupItem;
		}
		$cleanedPartGroup[]= 'WEEK(period)';
		foreach ($cleanedPartGroup as $cleanedPartGroupItem) {
			$this->getSelect()->group($cleanedPartGroupItem);
		}
		return $this;
	}

	/** @return Varien_Db_Select */
	private function getSelect() {return $this->getReportCollection()->getSelect();}

	/** @return Mage_Sales_Model_Resource_Report_Collection_Abstract */
	private function getReportCollection() {
		/** @var Mage_Sales_Model_Resource_Report_Collection_Abstract $result */
		$result = $this->c();
		df_assert($this->isItReportCollection());
		return $result;
	}

	/** @return bool */
	private function isItReportCollection() {if (!isset($this->{__METHOD__})) {$this->{__METHOD__} =
		$this->c() instanceof Mage_Sales_Model_Resource_Report_Collection_Abstract;
	}return $this->{__METHOD__};}
}