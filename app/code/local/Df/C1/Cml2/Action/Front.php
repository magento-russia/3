<?php
namespace Df\C1\Cml2\Action;
use Df\C1\Cml2\Action;
class Front extends Action {
	/**
	 * @override
	 * @see Df_Core_Model_Action::_process()
	 * @used-by Df_Core_Model_Action::process()
	 * @return void
	 */
	protected function _process() {
		if ($this->rmRequest()->isCheckAuth()) {
			$this->delegate(Action\Login::class);
		}
		else {
			$this->checkLoggedIn();
			switch ($this->rmRequest()->getType()) {
				case \Df\C1\Cml2\InputRequest\Generic::TYPE__GET_CATALOG:
					$this->action_catalogExport();
					break;
				case \Df\C1\Cml2\InputRequest\Generic::TYPE__CATALOG:
					$this->action_catalog();
					break;
				case \Df\C1\Cml2\InputRequest\Generic::TYPE__ORDERS:
					$this->action_orders();
					break;
				case \Df\C1\Cml2\InputRequest\Generic::TYPE__REFERENCE:
					$this->action_reference();
					break;
			}
		}
	}

	/**
	 * 2016-11-07
	 * Логирование этого класса отключаем,
	 * потому что класс делегирует обработку другим классам, где и будет логирование.
	 * @override
	 * @see Df_Core_Model_Action::needLogRR()
	 * @used-by Df_Core_Model_Action::process()
	 * @return bool
	 */
	protected function needLogRR() {return false;}

	/** @return void */
	private function action_catalog() {
		switch($this->rmRequest()->getMode()) {
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
			 * http://dev.1c-bitrix.ru/community/blogs/product_features/exchange-module-with-1cbitrix-40.php
			 * Что он означает — пока неясно: надо смотреть исходники последних версий 1С-Битрикс.
			 * В журнале 1С этот режим прокомментирован так:
			 * «Деактивация элементов, не попавшие в полную пакетную выгрузку.»
			 */
			case \Df\C1\Cml2\InputRequest\Generic::MODE__DEACTIVATE:
				$this->delegate(Action\Catalog\Deactivate::class);
				break;
			case \Df\C1\Cml2\InputRequest\Generic::MODE__FILE:
				$this->action_upload();
				break;
			case \Df\C1\Cml2\InputRequest\Generic::MODE__IMPORT:
				$this->delegate(Action\Catalog\Import::class);
				break;
			case \Df\C1\Cml2\InputRequest\Generic::MODE__INIT:
				$this->action_init();
				break;
		}
	}

	/**
	 * @return void
	 * @throws \Exception
	 */
	private function action_catalogExport() {
		switch($this->rmRequest()->getMode()) {
			case \Df\C1\Cml2\InputRequest\Generic::MODE__INIT:
				$this->action_init();
				$this->flag_catalogHasJustBeenExported(false);
				break;
			case \Df\C1\Cml2\InputRequest\Generic::MODE__QUERY:
				/** @var bool $process */
				$process = !$this->flag_catalogHasJustBeenExported();
				try {
					/**
					 * @uses \Df\C1\Cml2\Action\Catalog\Export\Finish
					 * @uses \Df\C1\Cml2\Action\Catalog\Export\Process
					 */
					$this->delegate(
						$process
						? Action\Catalog\Export\Process::class
						: Action\Catalog\Export\Finish::class
					);
					$this->flag_catalogHasJustBeenExported($process);
				}
				catch (\Exception $e) {
					$this->flag_catalogHasJustBeenExported($process);
					throw $e;
				}
				break;
		}
	}

	/**
	 * @uses \Df\C1\Cml2\Action\Init
	 * @return void
	 */
	private function action_init() {$this->delegate(Action\Init::class);}

	/** @return void */
	private function action_orders() {
		switch($this->rmRequest()->getMode()) {
			case \Df\C1\Cml2\InputRequest\Generic::MODE__FILE:
				$this->delegate(Action\Orders\Import::class);
				break;
			case \Df\C1\Cml2\InputRequest\Generic::MODE__INIT:
				$this->action_init();
				break;
			case \Df\C1\Cml2\InputRequest\Generic::MODE__QUERY:
				$this->delegate(Action\Orders\Export::class);
				break;
			case \Df\C1\Cml2\InputRequest\Generic::MODE__SUCCESS:
				$this->setResponseSuccess();
				break;
		}
	}

	/** @return void */
	private function action_reference() {
		switch($this->rmRequest()->getMode()) {
			case \Df\C1\Cml2\InputRequest\Generic::MODE__FILE:
				$this->action_upload();
				break;
			case \Df\C1\Cml2\InputRequest\Generic::MODE__IMPORT:
				$this->delegate(Action\Reference\Import::class);
				break;
			case \Df\C1\Cml2\InputRequest\Generic::MODE__INIT:
				$this->action_init();
				break;
		}
	}

	/**
	 * @uses \Df\C1\Cml2\Action\GenericImport\Upload
	 * @return void
	 */
	private function action_upload() {$this->delegate(Action\GenericImport\Upload::class);}

	/** @return void */
	private function checkLoggedIn() {
		/** @var string|null $sessionId */
		$sessionId = Action\Login::sessionId();
		if (!df_check_string_not_empty($sessionId)) {
			df_error(
				'1С должна была указать в запросе идентификатор сессии, однако не указала.'
				. "\nОбработка запроса невозможна."
				. "\nОбратитесь к программисту."
			);
		}
		$this->sessionMagentoAPI()->setSessionId($sessionId);
		if (!$this->sessionMagentoAPI()->isLoggedIn($sessionId)) {
			df_error(
				'Доступ к данной операции запрещён,'
				. ' потому что система не смогла распознать администратора (неверная сессия)'
			);
		}
	}

	/**
	 * @param bool|null $value [optional]
	 * @return bool|null
	 */
	private function flag_catalogHasJustBeenExported($value = null) {
		$this->session()->begin();
		/** @var bool|null $result */
		$result = $this->session()->flag_catalogHasJustBeenExported($value);
		$this->session()->end();
		return $result;
	}
}