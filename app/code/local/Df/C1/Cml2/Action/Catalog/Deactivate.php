<?php
namespace Df\C1\Cml2\Action\Catalog;
/**
 * Этот режим имеется в версии 4.0.2.3 модуля 1С-Битрикс для обмена с сайтом:
	Процедура ДобавитьПараметрыПротоколаОбменаВСтруктуру(СтруктураПараметров)
		СтруктураПараметров.Вставить("ПараметрЗапросаHTTP_Инициализация"			, "&mode=init");
		СтруктураПараметров.Вставить("ПараметрЗапросаHTTP_ПередачаФайла"			, "&mode=file&filename=");
		СтруктураПараметров.Вставить("ПараметрЗапросаHTTP_ИмпортФайлаСервером"		, "&mode=import&filename=");
		СтруктураПараметров.Вставить("ПараметрЗапросаHTTP_ПолучитьДанные"			, "&mode=query");
		СтруктураПараметров.Вставить("ПараметрЗапросаHTTP_УспешноеЗавершениеИмпорта", "&mode=success");
		СтруктураПараметров.Вставить("ПараметрЗапросаHTTP_ДеактивацияДанныхПоДате"	, "&mode=deactivate");
		(...)
	КонецПроцедуры
 * https://dev.1c-bitrix.ru/community/blogs/product_features/exchange-module-with-1cbitrix-40.php
 * Что он означает — пока неясно: надо смотреть исходники последних версий 1С-Битрикс.
 * В журнале 1С этот режим прокомментирован так:
 * «Деактивация элементов, не попавшие в полную пакетную выгрузку.»
 */
class Deactivate extends \Df\C1\Cml2\Action\Catalog {
	/**
	 * @override
	 * @see Df_Core_Model_Action::_process()
	 * @used-by Df_Core_Model_Action::process()
	 * @return void
	 */
	protected function _process() {$this->setResponseSuccess();}
}