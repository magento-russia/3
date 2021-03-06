<?php
namespace Df\C1\Cml2\Export\Processor\Sale;
use Df_Sales_Model_Order as O;
class Order extends \Df\C1\Cml2\Export\Processor\Sale {
	/** @return void */
	public function process() {
		$this->getDocument()->importArray(
			$this->getDocumentData_Order()
			,$wrapInCData = ['Ид', 'Комментарий', 'Наименование', 'Описание', 'Представление']
		);
	}

	/** @return \Df\Xml\X */
	private function e() {return $this->cfg(self::$P__E);}

	/** @return \Df_Sales_Model_Order_Address */
	private function getAddress() {return $this->getCustomer()->getMergedAddressWithShippingPriority();}
	
	/** @return \Df\C1\Cml2\Export\Data\Entity\Customer */
	private function getCustomer() {return dfc($this, function() {return
		\Df\C1\Cml2\Export\Data\Entity\Customer::i($this->getOrder())
	;});}

	/**
	 * @see SimpleXMLElement::addChild создаёт и возвращает не просто @see SimpleXMLElement,
	 * как говорит документация, а объект класса родителя.
	 * Поэтому в нашем случае addChild создаст объект @see \Df\Xml\X.
	 * @return \Df\Xml\X
	 */
	private function getDocument() {return dfc($this, function() {return
		$this->e()->addChild('Документ')
	;});}

	/** @return array(string => mixed) */
	private function getDocumentData_Customer() {return [
		'Ид' => $this->getCustomer()->getId()
		,'Наименование' => $this->getCustomer()->getNameShort()
		,'Роль' => 'Покупатель'
		,'ПолноеНаименование' => $this->getCustomer()->getNameFull()
		,'Фамилия' => $this->getCustomer()->getNameLast()
		,'Имя' => $this->getCustomer()->getNameFirst()
		,'Отчество' => $this->getCustomer()->getNameMiddle()
		,'ДатаРождения' => $this->getCustomer()->getDateOfBirthAsString()
		,'Пол' => $this->getCustomer()->getGenderAsString()
		,'ИНН' => $this->getCustomer()->getInn()
		,'КПП' => ''
		,'АдресРегистрации' => $this->getDocumentData_CustomerAddress()
		,'Адрес' => $this->getDocumentData_CustomerAddress()
		,'Контакты' => $this->getDocumentData_CustomerContacts()
	];}

	/** @return array(string => mixed) */
	private function getDocumentData_CustomerAddress() {return [
		'Представление' => df_cdata(df_nts(
			$this->getAddress()->format(\Mage_Customer_Model_Attribute_Data::OUTPUT_FORMAT_TEXT)
		))
		,'АдресноеПоле' => [
			$this->entry()->type('Почтовый индекс', $this->getAddress()->getPostcode())
			,$this->entry()->type('Улица', df_cdata($this->getAddress()->getStreetAsText()))
			,$this->entry()->type('Страна', $this->getAddress()->getCountryModel()->getName())
			,$this->entry()->type('Регион', $this->getAddress()->getRegion())
			,$this->entry()->type('Район', '')
			,$this->entry()->type('Населенный пункт', $this->getAddress()->getCity())
			,$this->entry()->type('Город', $this->getAddress()->getCity())
			,$this->entry()->type('Улица', '')
			,$this->entry()->type('Дом', '')
			,$this->entry()->type('Корпус', '')
			,$this->entry()->type('Квартира', '')
		]
	];}

	/** @return array(string => mixed) */
	private function getDocumentData_CustomerContacts() {return [
		'Контакт' => [
			$this->entry()->type('ТелефонРабочий', $this->getAddress()->getTelephone())
			,$this->entry()->type('Почта', $this->getCustomer()->getEmail())
		]
	];}

	/** @return array(string => mixed) */
	private function getDocumentData_Discounts() {
		/** @var array(string => mixed) $result */
		$result = [
			$this->entry()->discount('Скидка', abs($this->getOrder()->getDiscountAmount()), true)
		];
		/** @var float $rewardAmount */
		$rewardAmount = df_float($this->getOrder()->getData(
			\Df_Sales_Model_Order::P__REWARD_CURRENCY_AMOUNT
		));
		if (0 < $rewardAmount) {
			$result[]= $this->entry()->discount('Бонусная скидка', $rewardAmount, false);
		}
		/** @var float $customerBalanceAmount */
		$customerBalanceAmount = df_float($this->getOrder()->getData(
			\Df_Sales_Model_Order::P__CUSTOMER_BALANCE_AMOUNT
		));
		if (0 < $customerBalanceAmount) {
			$result[]= $this->entry()->discount('Оплата с личного счёта', $customerBalanceAmount, false);
		}
		return $result;
	}

	/** @return array(string => mixed) */
	private function getDocumentData_Order() {return [
		'Ид' => $this->getOrder()->getId()
		,'Номер' => $this->getOrder()->getIncrementId()
		,'Дата' => df_dts(
			$this->getOrder()->getCreatedAtStoreDate()
			, \Df\C1\Cml2\Export\DocumentMixin::DATE_FORMAT
		)
		,'ХозОперация' => 'Заказ товара'
		,'Роль' => 'Продавец'
		,'Валюта' => df_c1_currency_code_to_1c_format($this->getOrder()->getOrderCurrencyCode())
		,'Курс' => 1
		,'Сумма' => df_f2($this->getOrder()->getGrandTotal())
		,'Контрагенты' => ['Контрагент' => $this->getDocumentData_Customer()]
		/**
		 * Раньше здесь использовался формат Zend_Date::TIME_MEDIUM.
		 * Однако при обмене заказами с конфигурацией «Управление торговлей для Украины»
		 * это приводило к сбою функции ОбработатьДатуВремяCML.
		 */
		,'Время' => df_dts($this->getOrder()->getCreatedAtStoreDate(), 'HH:mm:ss')
		,'Налоги' => [
			'Налог' => $this->entry()->tax(
				'Совокупный налог', $this->getOrder()->getTaxAmount(), true
			)
		]
		,'Скидки' => ['Скидка' => $this->getDocumentData_Discounts()]
		,'Товары' => ['Товар' => $this->getDocumentData_OrderItems()]
		,'ЗначенияРеквизитов' => ['ЗначениеРеквизита' => $this->getDocumentData_OrderProperties()]
		,'Комментарий' => $this->getOrderComments()
	];}

	/** @return array(array(string => mixed)) */
	private function getDocumentData_OrderItems() {
		/** @var array(array(string => mixed)) $result */
		$result = [];
		foreach ($this->getOrder()->getItemsCollection() as $item) {
			/** @var \Mage_Sales_Model_Order_Item $item */
			if (\Mage_Catalog_Model_Product_Type::TYPE_SIMPLE === $item->getProductType()) {
				$result[]= \Df\C1\Cml2\Export\Processor\Sale\Order\Item::i($item)->getDocumentData();
			}
		}
		if (0 < $this->getOrder()->getShippingAmount()) {
			// Используем тот же трюк, что и 1С-Битрикс:
			// указываем стоимость доставки отдельной строкой заказа.
			$result[]= [
				'Ид' => 'ORDER_DELIVERY'
				,'Наименование' => 'Доставка заказа'
				,'БазоваяЕдиница' => $this->entry()->unit()
				,'ЦенаЗаЕдиницу' => df_f2($this->getOrder()->getShippingAmount())
				,'Количество' => 1
				,'Сумма' => df_f2($this->getOrder()->getShippingAmount())
				,'ЗначенияРеквизитов' => [
					'ЗначениеРеквизита' => [
						$this->entry()->name('ВидНоменклатуры', 'Услуга')
						,$this->entry()->name('ТипНоменклатуры', 'Услуга')
					]
				]
			];
		}
		$result = df_clean($result);
		return $result;
	}

	/** @return array(array(string => string)) */
	private function getDocumentData_OrderProperties() {
		/** @var array(array(string => string)) $result */
		$result = [];
		if (false !== $this->getOrder()->getPayment()) {
			$result[]= $this->entry()->name(
				'Метод оплаты', $this->getOrder()->getPayment()->getMethodInstance()->getTitle()
			);
		}
		$result[]= $this->entry()->name('Заказ оплачен', df_bts(0 >= $this->getOrder()->getTotalDue()));
		$result[]= $this->entry()->name('Способ доставки', $this->getOrder()->getShippingDescription());
		$result[]= $this->entry()->name('Доставка разрешена', df_bts($this->getOrder()->canShip()));
		$result[]= $this->entry()->name('Отменен', df_bts($this->getOrder()->isCanceled()));
		$result[]= $this->entry()->name(
			'Финальный статус'
			, df_bts(\Mage_Sales_Model_Order::STATE_COMPLETE === $this->getOrder()->getState())
		);
		$result[]= $this->entry()->name(
			'Статус заказа'
			, implode(' / ', [$this->getOrder()->getState(), $this->getOrder()->getStatus()])
		);
		$result[]= $this->entry()->name('Дата изменения статуса', $this->getOrder()->getUpdatedAt());
		$result[]= $this->entry()->name('Сайт', $this->getOrder()->getStore()->getName());
		return $result;
	}

	/** @return \Df_Sales_Model_Order */
	private function getOrder() {return $this->cfg(self::$P__ORDER);}

	/** @return string */
	private function getOrderComments() {
		/** @var string[] $comments */
		$comments = [];
		foreach ($this->getOrder()->getAllStatusHistory() as $historyItem) {
			/** @var \Mage_Sales_Model_Order_Status_History $historyItem */
			if ($historyItem->getComment()) {
				$comments[]= df_cc_n($historyItem->getCreatedAt(), $historyItem->getComment());
			}
		}
		return implode("\n\n", $comments);
	}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this
			->_prop(self::$P__ORDER, O::class)
			->_prop(self::$P__E, \Df\Xml\X::class)
		;
	}
	/** @var string */
	private static $P__ORDER = 'order';
	/** @var string */
	private static $P__E = 'e';
	/**
	 * @used-by \Df\C1\Cml2\Export\Document\Orders::createElement()
	 * @static
	 * @param O $order
	 * @param \Df\Xml\X $e
	 * @return \Df\C1\Cml2\Export\Processor\Sale\Order
	 */
	public static function i(O $order, \Df\Xml\X $e) {return new self([
		self::$P__ORDER => $order, self::$P__E => $e
	]);}
}