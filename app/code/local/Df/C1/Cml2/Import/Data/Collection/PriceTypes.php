<?php
namespace Df\C1\Cml2\Import\Data\Collection;
use Df\C1\Cml2\Import\Data\Document;
use Df\C1\Cml2\Import\Data\Entity\PriceType;
use Df\C1\Cml2\State\Import as I;
use Df\Xml\X;
class PriceTypes extends \Df\C1\Cml2\Import\Data\Collection {
	/**
	 * @override
	 * @see \Df\Xml\Parser\Entity::e()
	 * @return X
	 */
	public function e() {return dfc($this, function() {
		/** @var X $result */
		$result = null;
		/** @var I $i */
		$i = I::s();
		if ($i->getFileOffers()->getXml()->descend('ПакетПредложений/ТипыЦен')) {
			$result = $i->getFileOffers()->getXml();
		}
		else if ($i->getFileCatalogStructure()->getXml()->descend('Классификатор/ТипыЦен')) {
			$result = $i->getFileCatalogStructure()->getXml();
		}
		df_assert($result instanceof X);
		return $result;
	});}

	/** @return PriceType */
	public function getMain() {return dfc($this, function() {
		if (!$this->hasItems()) {
			$this->throwError_noPriceTypes();
		}
		$result = $this->findByName(df_c1_cfg()->product()->prices()->getMain());
		if (!$result) {
			df_error(
				  'Модуль «1С:Управление торговлей» для Magento'
				. ' не нашёл в полученных из «1С:Управление торговлей» данных'
				. ' цены типового соглашения (типа) «{название типового соглашения}».'
				. "\nИменно это типовое соглашение (тип цен) указано администратором как основное"
				. ' в настройках модуля «1С:Управление торговлей» для Magento:'
				. "\n(«Система» -> «Настройки» -> «1С:Управление торговлей»"
				. ' -> «Российская сборка» -> «1С:Управление торговлей» -> «Цены»'
				. ' -> «Название основной цены или типового соглашения»).'
				. "\nВам сейчас нужно убедиться, что типовое соглашение (тип цен) с данным именем"
				. ' действительно присутствует в «1С:Управление торговлей»'
				. ' и указано в настройках задействованного для обмена данными с интернет-магазином'
				. ' узла обмена в «1С:Управление торговлей».'
				. "\nИнструкция по настройке типового соглашения «1С:Управление торговлей»"
				. ' для обмена данными с интернет-магазином: http://magento-forum.ru/topic/3100/'
				,['{название типового соглашения}' => df_c1_cfg()->product()->prices()->getMain()]
			);
		}
		return $result;
	});}

	/**
	 * @override
	 * @see \Df\Xml\Parser\Collection::itemClass()
	 * @return string
	 */
	protected function itemClass() {return PriceType::class;}

	/**
	 * @override
	 * @see \Df\Xml\Parser\Collection::itemPath()
	 * @return string|string[]
	 */
	protected function itemPath() {
		/** @var string $type */
		$type = $this->getDocument()->isOffers() ? 'ПакетПредложений' : 'Классификатор';
		return "/КоммерческаяИнформация/{$type}/ТипыЦен/ТипЦены";
	}

	/** @return Document */
	private function getDocument() {return dfc($this, function() {
		/** @var Document $result */
		$result = null;
		/** @var I $i */
		$i = I::s();
		if ($i->getFileOffers()->getXml()->descend('ПакетПредложений/ТипыЦен')) {
			$result = $i->getFileOffers()->getXmlDocument();
		}
		// В новых версиях модуля 1С-Битрикс (ветка 4, CommerceML 2.0.8)
		// типы цен неожиданно переместились в файл каталога.
		else if ($i->getFileCatalogStructure()->getXml()->descend('Классификатор/ТипыЦен')) {
			$result = $i->getFileCatalogStructure()->getXmlDocument();
		}
		if (!$result) {
			$this->throwError_noPriceTypes();
		}
		df_assert($result instanceof Document);
		return $result;
	});}

	/** @return void */
	private function throwError_noPriceTypes() {
		df_error(
			'«1С:Управление торговлей» не передала в интернет-магазин цены на товары.'
			. "\nВероятно, используемый для обмена данными с интернет-магазином"
			. ' узел обмена данными «1С:Управление торговлей» настроен не в полной мере.'
			. "\nЕщё раз внимательно прочитайте и выполните инструкции по настройке обмена данными"
			. ' между «1С:Управление торговлей» и Российской сборкой Magento:'
			. ' http://magento-forum.ru/forum/265/'
		);
	}

	/**
	 * @used-by \Df\C1\Cml2\State::getPriceTypes()
	 * @return self
	 */
	public static function s() {static $r; return $r ?: $r = new self;}
}