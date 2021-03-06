<?php
abstract class Df_Dataflow_Model_Registry_MultiCollection
	extends Df_Core_Model implements IteratorAggregate {
	/**
	 * @param Df_Core_Model_StoreM $store
	 * @return Df_Dataflow_Model_Registry_Collection
	 */
	abstract protected function getCollectionForStore(Df_Core_Model_StoreM $store);

	/**
	 * @override
	 * @return Traversable
	 */
	public function getIterator() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = new ArrayIterator($this->getEntities());
		}
		return $this->{__METHOD__};
	}

	/**
	 * @return void
	 * @throws \Df\Core\Exception\Batch|Exception
	 */
	public function save() {
		/** @var \Df\Core\Exception\Batch $batchException */
		$batchException = new \Df\Core\Exception\Batch();
		foreach ($this->getCollections() as $collection) {
			/** @var Df_Dataflow_Model_Registry_Collection $collection */
			try {
				$collection->save();
			}
			catch (\Df\Core\Exception\Batch $partialBatch) {
				$batchException->addBatch($partialBatch);
			}
		}
		$batchException->throwIfNeeed();
	}

	/** @return array(int => Df_Dataflow_Model_Registry_Collection) */
	private function getCollections() {
		if (!isset($this->{__METHOD__})) {
			/** @var array(int => Df_Dataflow_Model_Registry_Collection) $result */
			$result = [];
			foreach (Mage::app()->getStores($withDefault = true) as $store) {
				/** @var Df_Core_Model_StoreM $store */
				$result[$store->getId()] = $this->getCollectionForStore($store);
			}
			$this->{__METHOD__} = $result;
		}
		return $this->{__METHOD__};
	}

	/** @return Mage_Core_Model_Abstract[] */
	private function getEntities() {
		if (!isset($this->{__METHOD__})) {
			/** @uses iterator_to_[] */
			$this->{__METHOD__} = df_merge_single(array_map('iterator_to_array', $this->getCollections()));
		}
		return $this->{__METHOD__};
	}
}
