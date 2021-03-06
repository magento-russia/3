<?php
namespace Df\C1\Cml2\Import\Data\Collection\ProductPart\AttributeValues;
use Df\C1\Cml2\Import\Data\Entity\Product as EntityProduct;
use Df\C1\Cml2\Import\Data\Entity\ProductPart\AttributeValue\Custom as Value;
use Df\C1\Cml2\Import\Data\Entity\ProductPart\AttributeValue\Custom\Option\Manufacturer;
/** @method \Df\C1\Cml2\Import\Data\Entity\AttributeValue[] getItems() */
class Custom extends \Df\C1\Cml2\Import\Data\Collection {
	/**
	 * @override
	 * @see \Df\Xml\Parser\Collection::createItem()
	 * @param \Df\Xml\X $e
	 * @return \Df\C1\Cml2\Import\Data\Entity
	 */
	protected function createItem(\Df\Xml\X $e) {return
		Value::ic($this->itemClassAdvanced($e), $e, $this->getProduct());
	}

	/**
	 * 2015-08-15
	 * Класс элемента зависит от ветки XML.
	 * @override
	 * @see \Df\Xml\Parser\Collection::itemClassAdvanced()
	 * @param \Df\Xml\X $e
	 * @return string
	 */
	protected function itemClassAdvanced(\Df\Xml\X $e) {
		/** @var string|null $valueId */
		$valueId = $e->leaf('ИдЗначения');
		if (!$valueId) {
			/**
			 * В магазине sb-s.com.ua встречается такая конструкция:
			 *
	 			<ЗначенияСвойства>
					<Ид>6cc37c6d-7d15-11df-901f-00e04c595000</Ид>
					<Значение>6cc37c6e-7d15-11df-901f-00e04c595000</Значение>
				</ЗначенияСвойства>
			 *
			 * Похожую (но другую!) конструкцию встретил 11 июля 2013 года в магазине belle.com.ua:
				<ЗначенияСвойств>
					<ЗначенияСвойства>
						<Ид>dd6bfa58-d7e9-11d9-bfbc-00112f3000a2</Ид>
						<Значение>Розница</Значение>
					</ЗначенияСвойства>
				</ЗначенияСвойств>
			 *
			 * Обратите внимание, что в данном случае
			 * внутри тега «Значение» находится непосредственно значение,
			 * а не идентификатор значения, как в примере выше из магазина sb-s.com.ua.
			 *
			 * Причем соответствующее свойство описано в import.xml следующим образом:
				<Классификатор>
					(...)
					<Свойства>
						<СвойствоНоменклатуры>
							<Ид>dd6bfa58-d7e9-11d9-bfbc-00112f3000a2</Ид>
							<Наименование>Канал сбыта</Наименование>
							<Обязательное>false</Обязательное>
							<Множественное>false</Множественное>
							<ИспользованиеСвойства>true</ИспользованиеСвойства>
						</СвойствоНоменклатуры>
					</Свойства>
				</Классификатор>
			 *
			 * Обратите внимание на использование тега «СвойствоНоменклатуры»
			 * вместо стандартного тега «Свойство».
			 * Причём это происходит в типовой конфигурации
			 * «Управление торговлей для Украины» редации 2.3
			 * (редакция платформы 1С:Предприятие — 10.3)
			 */
			/** @var string $value */
			$value = $e->leaf('Значение');
			if (df_c1_is_external_id($value)) {
				$valueId = $value;
			}
		}
		return
			!$valueId
			? \Df\C1\Cml2\Import\Data\Entity\ProductPart\AttributeValue\Custom::class
			: \Df\C1\Cml2\Import\Data\Entity\ProductPart\AttributeValue\Custom\Option::class
		;
	}

	/**
	 * @override
	 * @see \Df\Xml\Parser\Collection::itemPath()
	 * @return string|string[]
	 */
	protected function itemPath() {return 'ЗначенияСвойств/ЗначенияСвойства';}

	/**
	 * 11 февраля 2014 года заметил,
	 * что 1С:Управление торговлей 11.1 при использовании версии 2.05 протокола CommerceML
	 * и версии 8.3 платформы 1С:Предприятие
	 * при обмене данными с интернет-магазином передаёт информацию о производителе
	 * не в виде стандартного атрибута, а иначе:
	 *
		<КоммерческаяИнформация ВерсияСхемы="2.05" ДатаФормирования="2014-02-11T15:32:13">
			(...)
			<Каталог СодержитТолькоИзменения="false">
				(...)
				<Товары>
					<Товар>
						(...)
						<Изготовитель>
							<Ид>9bf2b1bf-8e9a-11e3-bd2c-742f68ccd0fb</Ид>
							<Наименование>Tecumseh</Наименование>
							<ОфициальноеНаименование>Tecumseh</ОфициальноеНаименование>
						</Изготовитель>
						(...)
					</Товар>
				</Товары>
			</Каталог>
		</КоммерческаяИнформация>
	 *
	 * http://magento-forum.ru/topic/4277/
	 * http://dev.1c-bitrix.ru/community/forums/forum26/topic51568
	 * http://forum.infostart.ru/forum26/topic87833/
	 * http://www.hostcms.ru/forums/2/8746/
	 *
	 * @override
	 * @see \Df\Xml\Parser\Collection::postInitItems()
	 * @used-by \Df\Xml\Parser\Collection::getItems()
	 * @param Value[] $items
	 * @return void
	 */
	protected function postInitItems(array $items) {
		/** @var \Df\Xml\X|null $xmlManufacturer */
		$xmlManufacturer = $this->e()->descend('Изготовитель');
		if ($xmlManufacturer) {
			$this->addItem(Manufacturer::i($xmlManufacturer, $this->getProduct()));
		}
	}

	/** @return \Df\C1\Cml2\Import\Data\Entity\Product */
	private function getProduct() {return $this[self::$P__PRODUCT];}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->_prop(self::$P__PRODUCT, EntityProduct::class);
	}
	/** @var string */
	private static $P__PRODUCT = 'product';
	/**
	 * @used-by \Df\C1\Cml2\Import\Data\Entity\Product::getAttributeValuesCustom()
	 * @param \Df\Xml\X $e
	 * @param EntityProduct $product
	 * @return \Df\C1\Cml2\Import\Data\Collection\ProductPart\AttributeValues\Custom
	 */
	public static function i(\Df\Xml\X $e, EntityProduct $product) {return new self([
		self::$P__E => $e, self::$P__PRODUCT => $product
	]);}
}